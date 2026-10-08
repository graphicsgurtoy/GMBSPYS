<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST'); $u=require_auth(['super_admin']); require_csrf(); $d=request_json();
$id=(int)($d['business_id']??0); $status=($d['status']??'active')==='paused'?'paused':'active';
if(!$id) json_response(['ok'=>false,'error'=>'business_id required'],422);
db()->prepare('UPDATE businesses SET status=? WHERE id=?')->execute([$status,$id]); audit((int)$u['id'],$id,'business_status_changed',['status'=>$status]);
json_response(['ok'=>true]);
