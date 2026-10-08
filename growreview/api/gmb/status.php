<?php
require_once __DIR__ . '/../bootstrap.php'; require_auth(['super_admin']);
$oauth=(bool)(envv('GOOGLE_CLIENT_ID','')&&envv('GOOGLE_CLIENT_SECRET','')&&envv('GOOGLE_REFRESH_TOKEN',''));$cron=(bool)envv('CRON_SECRET','');
json_response(['ok'=>true,'oauth_configured'=>$oauth,'auto_publish_ready'=>$oauth,'cron_ready'=>$cron,'notes'=>$oauth?'Google OAuth is configured. Publishing tools can run for connected eligible profiles.':'AI tools work now. Live Google actions require approved Business Profile API access plus OAuth credentials.']);
