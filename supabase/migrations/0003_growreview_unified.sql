-- GrowReview + GMBSPYS unified Supabase foundation
-- Apply in Supabase SQL Editor after reviewing existing schema.
-- Uses Supabase Auth users; never put service_role keys in browser code.

create table if not exists public.user_profiles (
  user_id uuid primary key references auth.users(id) on delete cascade,
  full_name text,
  role text not null default 'user'
    check (role in ('user','staff','admin','super_admin')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.workspaces (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  created_by uuid not null references auth.users(id) on delete restrict,
  created_at timestamptz not null default now()
);

create table if not exists public.workspace_members (
  workspace_id uuid not null references public.workspaces(id) on delete cascade,
  user_id uuid not null references auth.users(id) on delete cascade,
  role text not null default 'member'
    check (role in ('owner','admin','manager','member','viewer')),
  created_at timestamptz not null default now(),
  primary key (workspace_id,user_id)
);

create table if not exists public.growreview_staff (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.workspaces(id) on delete cascade,
  auth_user_id uuid references auth.users(id) on delete set null,
  full_name text not null,
  email text,
  phone text,
  role text not null default 'staff',
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.growreview_reviews (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.workspaces(id) on delete cascade,
  business_id uuid,
  platform text not null default 'google',
  reviewer_name text,
  rating numeric(2,1) check (rating between 0 and 5),
  review_text text,
  review_date timestamptz,
  reply_text text,
  replied_at timestamptz,
  external_review_id text,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  unique (workspace_id,platform,external_review_id)
);

create table if not exists public.review_requests (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.workspaces(id) on delete cascade,
  business_id uuid,
  customer_name text,
  customer_email text,
  customer_phone text,
  channel text not null default 'manual',
  status text not null default 'pending'
    check (status in ('pending','sent','opened','completed','failed','cancelled')),
  request_token text unique,
  sent_at timestamptz,
  completed_at timestamptz,
  created_at timestamptz not null default now()
);

create table if not exists public.gmb_locations (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.workspaces(id) on delete cascade,
  business_id uuid,
  google_account_email text,
  google_location_id text,
  location_name text,
  location_data jsonb not null default '{}'::jsonb,
  connection_status text not null default 'disconnected',
  last_synced_at timestamptz,
  created_at timestamptz not null default now(),
  unique (workspace_id,google_location_id)
);

create table if not exists public.scraping_jobs (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.workspaces(id) on delete cascade,
  created_by uuid references auth.users(id) on delete set null,
  query text not null,
  location text,
  status text not null default 'queued'
    check (status in ('queued','running','completed','failed','cancelled')),
  results_count integer not null default 0,
  error_message text,
  started_at timestamptz,
  completed_at timestamptz,
  created_at timestamptz not null default now()
);

create table if not exists public.workspace_audit_logs (
  id bigint generated always as identity primary key,
  workspace_id uuid references public.workspaces(id) on delete cascade,
  actor_id uuid references auth.users(id) on delete set null,
  action text not null,
  entity_type text,
  entity_id text,
  details jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);

create index if not exists idx_workspace_members_user
  on public.workspace_members(user_id);
create index if not exists idx_growreview_reviews_workspace
  on public.growreview_reviews(workspace_id,created_at desc);
create index if not exists idx_review_requests_workspace
  on public.review_requests(workspace_id,created_at desc);
create index if not exists idx_scraping_jobs_workspace
  on public.scraping_jobs(workspace_id,created_at desc);
create index if not exists idx_gmb_locations_workspace
  on public.gmb_locations(workspace_id);

-- Membership helper: a user can only access workspaces they belong to.
create or replace function public.is_workspace_member(target_workspace uuid)
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (
    select 1 from public.workspace_members wm
    where wm.workspace_id = target_workspace
      and wm.user_id = (select auth.uid())
  );
$$;

revoke all on function public.is_workspace_member(uuid) from public;
grant execute on function public.is_workspace_member(uuid) to authenticated;

alter table public.user_profiles enable row level security;
alter table public.workspaces enable row level security;
alter table public.workspace_members enable row level security;
alter table public.growreview_staff enable row level security;
alter table public.growreview_reviews enable row level security;
alter table public.review_requests enable row level security;
alter table public.gmb_locations enable row level security;
alter table public.scraping_jobs enable row level security;
alter table public.workspace_audit_logs enable row level security;

drop policy if exists "profile read own" on public.user_profiles;
create policy "profile read own" on public.user_profiles
for select to authenticated using (user_id = (select auth.uid()));

drop policy if exists "profile update own" on public.user_profiles;
create policy "profile update own" on public.user_profiles
for update to authenticated using (user_id = (select auth.uid()))
with check (user_id = (select auth.uid()));

drop policy if exists "workspace members read workspaces" on public.workspaces;
create policy "workspace members read workspaces" on public.workspaces
for select to authenticated using (public.is_workspace_member(id));

drop policy if exists "workspace owner create" on public.workspaces;
create policy "workspace owner create" on public.workspaces
for insert to authenticated with check (created_by = (select auth.uid()));

drop policy if exists "members read memberships" on public.workspace_members;
create policy "members read memberships" on public.workspace_members
for select to authenticated using (public.is_workspace_member(workspace_id));

drop policy if exists "members read staff" on public.growreview_staff;
create policy "members read staff" on public.growreview_staff
for select to authenticated using (public.is_workspace_member(workspace_id));

drop policy if exists "admins manage staff" on public.growreview_staff;
create policy "admins manage staff" on public.growreview_staff
for all to authenticated using (
  exists (
    select 1 from public.workspace_members wm
    where wm.workspace_id = growreview_staff.workspace_id
      and wm.user_id = (select auth.uid())
      and wm.role in ('owner','admin')
  )
) with check (
  exists (
    select 1 from public.workspace_members wm
    where wm.workspace_id = growreview_staff.workspace_id
      and wm.user_id = (select auth.uid())
      and wm.role in ('owner','admin')
  )
);

drop policy if exists "members manage reviews" on public.growreview_reviews;
create policy "members manage reviews" on public.growreview_reviews
for all to authenticated using (public.is_workspace_member(workspace_id))
with check (public.is_workspace_member(workspace_id));

drop policy if exists "members manage review requests" on public.review_requests;
create policy "members manage review requests" on public.review_requests
for all to authenticated using (public.is_workspace_member(workspace_id))
with check (public.is_workspace_member(workspace_id));

drop policy if exists "members manage gmb locations" on public.gmb_locations;
create policy "members manage gmb locations" on public.gmb_locations
for all to authenticated using (public.is_workspace_member(workspace_id))
with check (public.is_workspace_member(workspace_id));

drop policy if exists "members manage scraping jobs" on public.scraping_jobs;
create policy "members manage scraping jobs" on public.scraping_jobs
for all to authenticated using (public.is_workspace_member(workspace_id))
with check (public.is_workspace_member(workspace_id));

drop policy if exists "members read audit logs" on public.workspace_audit_logs;
create policy "members read audit logs" on public.workspace_audit_logs
for select to authenticated using (public.is_workspace_member(workspace_id));

grant select, insert, update on public.user_profiles to authenticated;
grant select, insert on public.workspaces to authenticated;
grant select on public.workspace_members to authenticated;
grant select, insert, update, delete on public.growreview_staff to authenticated;
grant select, insert, update, delete on public.growreview_reviews to authenticated;
grant select, insert, update, delete on public.review_requests to authenticated;
grant select, insert, update, delete on public.gmb_locations to authenticated;
grant select, insert, update, delete on public.scraping_jobs to authenticated;
grant select on public.workspace_audit_logs to authenticated;

-- Create a user profile automatically after Supabase Auth signup.
create or replace function public.handle_new_auth_user()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
begin
  insert into public.user_profiles(user_id,full_name)
  values (new.id, coalesce(new.raw_user_meta_data->>'full_name',''))
  on conflict (user_id) do nothing;
  return new;
end;
$$;

drop trigger if exists on_auth_user_created_profile on auth.users;
create trigger on_auth_user_created_profile
after insert on auth.users
for each row execute procedure public.handle_new_auth_user();
