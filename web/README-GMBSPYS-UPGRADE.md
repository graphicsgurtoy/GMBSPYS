# GMBSPYS website upgrade

## Included
- Google-inspired public landing page with feature/how-it-works sections and FAQ.
- SEO metadata, SoftwareApplication and FAQPage JSON-LD, robots and sitemap routes.
- Live dashboard count queries against Supabase.
- Businesses page retained; Leads and Searches pages now query Supabase; Settings offers password recovery.
- Getting started, lead quality, and responsible-use guides.
- Admin route is explicitly a UI foundation only, not production-secured.

## Run locally
1. Copy `.env.example` to `.env.local`.
2. Set `NEXT_PUBLIC_SUPABASE_URL` and `NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY`.
3. From this folder run `npm install`, then `npm run dev`.
4. Open http://localhost:3000.
5. Run `npm run typecheck` and `npm run build` before deploying.

## Production blockers
- Verify Supabase RLS policies for businesses, leads, and searches. The provided initial migration creates tables but does not define RLS policies.
- Add real authentication and server-side admin role checks before using `/admin` for any sensitive operation. The route is deliberately marked as unprotected foundation.
- The extension must write to the same schema and use correct user IDs; review duplicate inserts and auth handling.
- Never expose a Supabase service-role key in browser code or commit it to git.
- Set NEXT_PUBLIC_SITE_URL to the real production domain before deployment. Update sitemap/robots URLs if domain changes.
- SEO schema helps describe content but does not guarantee rankings or FAQ rich results.
