-- Unified CRM + scraper foundation.
-- Additive only: existing businesses, leads and searches tables remain untouched.
-- RLS is enabled with no client policies initially (deny by default).
-- Add reviewed tenant-membership policies before production use.

create table if not exists public.workspace_profiles (
  user_id uuid primary key references auth.users(id) on delete cascade,
  display_name text,
  created_at timestamptz not null default now()
);

create table if not exists public.crm_workspaces (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  created_by uuid not null references auth.users(id) on delete restrict,
  created_at timestamptz not null default now()
);

create table if not exists public.crm_workspace_members (
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  user_id uuid not null references auth.users(id) on delete cascade,
  role text not null default 'staff'
    check (role in ('owner','admin','manager','staff')),
  created_at timestamptz not null default now(),
  primary key (workspace_id, user_id)
);

create table if not exists public.crm_businesses (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  name text not null,
  phone text,
  email text,
  website text,
  address text,
  city text,
  category text,
  source text not null default 'crm',
  status text not null default 'active',
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.scraper_searches (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  query text not null,
  location text,
  status text not null default 'queued'
    check (status in ('queued','running','completed','failed','cancelled')),
  results_count integer not null default 0 check (results_count >= 0),
  error_message text,
  created_at timestamptz not null default now(),
  completed_at timestamptz
);

create table if not exists public.scraper_leads (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  search_id uuid references public.scraper_searches(id) on delete set null,
  business_id uuid references public.crm_businesses(id) on delete set null,
  business_name text not null,
  phone text,
  email text,
  website text,
  address text,
  city text,
  category text,
  google_maps_url text,
  rating numeric(2,1),
  reviews_count integer,
  lead_status text not null default 'new'
    check (lead_status in ('new','contacted','qualified','converted','lost')),
  notes text,
  created_at timestamptz not null default now()
);

create table if not exists public.crm_staff (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  user_id uuid references auth.users(id) on delete set null,
  full_name text not null,
  email text,
  phone text,
  role text not null default 'staff',
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.crm_customers (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  business_id uuid references public.crm_businesses(id) on delete set null,
  full_name text not null,
  phone text,
  email text,
  notes text,
  created_at timestamptz not null default now()
);

create table if not exists public.crm_activities (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  business_id uuid references public.crm_businesses(id) on delete set null,
  lead_id uuid references public.scraper_leads(id) on delete set null,
  actor_id uuid references auth.users(id) on delete set null,
  activity_type text not null,
  description text,
  metadata jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);

create table if not exists public.crm_gbp_connections (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  business_id uuid references public.crm_businesses(id) on delete cascade,
  google_account_id text,
  google_location_id text,
  connection_status text not null default 'disconnected',
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.crm_content_jobs (
  id uuid primary key default gen_random_uuid(),
  workspace_id uuid not null references public.crm_workspaces(id) on delete cascade,
  business_id uuid references public.crm_businesses(id) on delete cascade,
  job_type text not null,
  status text not null default 'draft'
    check (status in ('draft','queued','processing','published','failed','cancelled')),
  payload jsonb not null default '{}'::jsonb,
  scheduled_at timestamptz,
  created_at timestamptz not null default now()
);

create table if not exists public.crm_audit_logs (
  id bigint generated always as identity primary key,
  workspace_id uuid references public.crm_workspaces(id) on delete set null,
  actor_id uuid references auth.users(id) on delete set null,
  action text not null,
  entity_type text,
  entity_id text,
  details jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);

create index if not exists idx_crm_businesses_workspace
  on public.crm_businesses(workspace_id);
create index if not exists idx_scraper_searches_workspace_created
  on public.scraper_searches(workspace_id, created_at desc);
create index if not exists idx_scraper_leads_workspace_status
  on public.scraper_leads(workspace_id, lead_status);
create index if not exists idx_scraper_leads_search
  on public.scraper_leads(search_id);
create index if not exists idx_crm_activities_workspace_created
  on public.crm_activities(workspace_id, created_at desc);

alter table public.workspace_profiles enable row level security;
alter table public.crm_workspaces enable row level security;
alter table public.crm_workspace_members enable row level security;
alter table public.crm_businesses enable row level security;
alter table public.scraper_searches enable row level security;
alter table public.scraper_leads enable row level security;
alter table public.crm_staff enable row level security;
alter table public.crm_customers enable row level security;
alter table public.crm_activities enable row level security;
alter table public.crm_gbp_connections enable row level security;
alter table public.crm_content_jobs enable row level security;
alter table public.crm_audit_logs enable row level security;
