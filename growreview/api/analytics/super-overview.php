<?php
require_once __DIR__ . '/../bootstrap.php';require_auth(['super_admin']);
$row=db()->query("SELECT (SELECT COUNT(*) FROM businesses) businesses,(SELECT COUNT(*) FROM businesses WHERE status='active') active_businesses,(SELECT COUNT(*) FROM staff WHERE is_active=1) staff,(SELECT COUNT(*) FROM review_drafts) reviews,(SELECT COUNT(*) FROM form_submissions) forms,(SELECT COUNT(*) FROM events WHERE event_type IN ('page_view','qr_scan')) scans,(SELECT COUNT(*) FROM events WHERE event_type='google_review_open') google_opens")->fetch();json_response(['ok'=>true,'metrics'=>$row]);
