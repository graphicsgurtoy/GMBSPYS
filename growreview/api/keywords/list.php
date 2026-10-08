<?php
require_once __DIR__ . '/../bootstrap.php';
$u=require_auth(['super_admin','owner','admin','manager','staff']); $bid=business_scope($u,isset($_GET['business_id'])?(int)$_GET['business_id']:null);
$st=db()->prepare('SELECT * FROM keywords WHERE business_id=? ORDER BY is_active DESC,priority DESC,keyword'); $st->execute([$bid]); json_response(['ok'=>true,'keywords'=>$st->fetchAll()]);
