GMBSPYS 2.1.0

This build preserves the original GMB Scraper extraction engine and adds:
- Supabase login in the popup
- Google Maps search launcher
- cloud sync of extracted businesses to Supabase
- dashboard shortcut

Before loading the extension, replace __GMBSPYS_PUBLISHABLE_KEY__ in auth/supabase-config.js with your Supabase publishable key.

Usage:
1. Load this folder as an unpacked extension.
2. Login in GMBSPYS popup.
3. Open/search Google Maps.
4. Click Start Auto Extract in the top bar.
5. Extracted businesses are stored locally and synced to Supabase.
