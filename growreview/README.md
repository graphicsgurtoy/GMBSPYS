# GrowReview CRM V5 — Performance + Website + GMB Workspaces

This release is designed for Hostinger shared hosting using HTML/CSS/Vanilla JavaScript + PHP 8 + MySQL.

## What changed in V5

- Rebuilt responsive marketing homepage with visual-heavy layout, animated Google-color graphics, client showcase, testimonials, blogs and 3 pricing plans.
- Cache-busted CSS/JS and Hostinger/CDN-safe HTML cache rules.
- Faster Super Admin overview using one optimized dashboard API instead of multiple initial requests.
- Added dedicated Clients, Reviews and Blogs sections in Super Admin.
- Added public homepage data endpoint so clients/testimonials/blogs can populate dynamically.
- Added GMB workspaces that function even before Google OAuth is connected:
  - Local Rank audits: generate + save locally.
  - Google Posts: generate + save draft + schedule + optional live publish.
  - Photo Manager: generate plan + save image workspace + optional live publish.
  - Services: save local service library + optional Google sync.
  - Products: save local product/catalog workspace.
  - Review Reply AI: save draft + optional Google publish.
- Existing review/staff/customer tables are not dropped.

## IMPORTANT: correct deployment order

### If you already have the current GrowReview database

1. Back up the database in phpMyAdmin.
2. Import `database/upgrade-v5.sql` into the existing GrowReview database.
3. Only after the migration succeeds, upload/overwrite V5 website files in `public_html`.
4. Keep your existing `public_html/.env` file. Do NOT replace it with `.env.example`.
5. In Hostinger, clear website/CDN cache once after upload.
6. Test `/api/health.php`, `/super-admin`, and one `/r/business-slug` page.

This order prevents the new Super Admin from requesting V5 tables before they exist.

### If this is a fresh installation

1. Create MySQL database + MySQL user in Hostinger.
2. Import `database/schema.sql`.
3. Upload project contents into `public_html`.
4. Rename `.env.example` to `.env` and enter DB/Gemini credentials.
5. Open `/api/health.php`.
6. Open `/setup` and create first Super Admin.

## Required `.env`

```env
APP_ENV=production
APP_URL=https://your-domain.com
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database
DB_USER=your_database_user
DB_PASS=your_database_password
GEMINI_API_KEY=your_gemini_key
GEMINI_MODEL=gemini-2.5-flash
SESSION_NAME=growreview_session

# Optional live Google Business Profile publishing
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REFRESH_TOKEN=

# Scheduled Google posts
CRON_SECRET=replace_with_a_long_random_secret
```

## Key URLs

- `/` — marketing website
- `/setup` — first Super Admin setup
- `/super-admin` — GrowReview master CRM
- `/app` — client CRM
- `/r/business-slug` — public QR/customer page
- `/blog/blog-slug` — published GrowReview blog
- `/api/health.php` — backend health check

## Hostinger cache

V5 uses `?v=5.0.0` on critical CSS/JS and adds no-cache headers for HTML/PHP. After first deployment, still clear Hostinger CDN/cache once to remove older assets.

## Google Business Profile publishing

The local GMB workspaces run without OAuth. Live posts/photos/services/review replies require an approved Google Business Profile API setup and OAuth credentials.

## Database files

- `database/schema.sql` — fresh install, includes V5 tables.
- `database/upgrade-v5.sql` — safe additive migration for an existing GrowReview database.
- Older `upgrade-v4.sql` is kept only for history; V5 users should run `upgrade-v5.sql` after V4.
