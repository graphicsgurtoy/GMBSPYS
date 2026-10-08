# GrowReview V5 GMB Growth Workspaces

All GMB modules now have a local workspace even when Google OAuth is not connected.

## Local Rank AI
Generate an audit with AI and save it in `gmb_audits`.

## AI Google Posts
Generate post content. Save a draft to `gmb_content_jobs`, schedule it, or publish live when Google OAuth is configured.

## Photo Manager
Generate photo ideas. Upload JPG/PNG/WEBP files to Hostinger or save a public image URL in `gmb_assets`. Live Google publishing still requires Google OAuth.

## Services Manager
Save business services in `gmb_services_workspace`. When Google API access is connected, use Sync to Google for eligible profiles.

## Product Studio
Save product/catalog content in `gmb_products_workspace`. It is a GrowReview workspace for reusable catalog content.

## Review Reply AI
Generate replies and save drafts in `gmb_content_jobs`. A Google Review ID + OAuth connection is required to publish a reply live.

## Scheduler
Scheduled posts remain in `gmb_content_jobs`. Configure Hostinger Cron to call:

`/api/cron/publish-scheduled.php?key=YOUR_CRON_SECRET`

Do not claim or expect ranking guarantees. The Local Rank AI is an operational optimisation plan, not a ranking promise.
