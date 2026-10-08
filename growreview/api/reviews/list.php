<?php
require_once __DIR__ . '/../bootstrap.php'; $u=require_auth(['super_admin','owner','admin','manager','staff']); $bid=business_scope($u,isset($_GET['business_id'])?(int)$_GET['business_id']:null);
$st=db()->prepare('SELECT r.*,s.name staff_name FROM review_drafts r LEFT JOIN staff s ON s.id=r.staff_id WHERE r.business_id=? ORDER BY r.created_at DESC LIMIT 300');$st->execute([$bid]);json_response(['ok'=>true,'reviews'=>$st->fetchAll()]);
