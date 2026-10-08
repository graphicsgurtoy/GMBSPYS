<?php
require_once __DIR__ . '/../bootstrap.php';$u=require_auth(['super_admin','owner','admin','manager','staff']);$bid=business_scope($u,isset($_GET['business_id'])?(int)$_GET['business_id']:null);
$sql="SELECT
(SELECT COUNT(*) FROM events WHERE business_id=? AND event_type IN ('page_view','qr_scan')) scans,
(SELECT COUNT(*) FROM events WHERE business_id=? AND event_type='google_review_open') google_opens,
(SELECT COUNT(*) FROM review_drafts WHERE business_id=?) reviews,
(SELECT COUNT(*) FROM form_submissions WHERE business_id=?) forms,
(SELECT COUNT(*) FROM staff WHERE business_id=? AND is_active=1) active_staff,
(SELECT COUNT(*) FROM customers WHERE business_id=?) customers";
$st=db()->prepare($sql);$st->execute([$bid,$bid,$bid,$bid,$bid,$bid]);json_response(['ok'=>true,'metrics'=>$st->fetch()]);
