<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST'); $u=require_auth(['super_admin','owner','admin','manager']); require_csrf(); $d=request_json();
$id=(int)($d['staff_id']??0); $st=db()->prepare('SELECT business_id,is_active FROM staff WHERE id=?'); $st->execute([$id]); $s=$st->fetch(); if(!$s)json_response(['ok'=>false,'error'=>'Staff not found'],404); business_scope($u,(int)$s['business_id']);
$new=(int)!((int)$s['is_active']); db()->prepare('UPDATE staff SET is_active=? WHERE id=?')->execute([$new,$id]); json_response(['ok'=>true,'is_active'=>$new]);
