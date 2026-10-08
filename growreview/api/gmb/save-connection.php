<?php
require_once __DIR__ . '/../bootstrap.php';require_method('POST');$u=require_auth(['super_admin']);require_csrf();$d=request_json();
$bid=(int)($d['business_id']??0);$account=preg_replace('/[^0-9]/','',(string)($d['account_id']??''));$location=trim((string)($d['location_id']??''));$category=trim((string)($d['category_id']??''));
if(!$bid||!$account||$location==='')json_response(['ok'=>false,'error'=>'Business, Google account ID and location ID are required'],422);
$st=db()->prepare('INSERT INTO gmb_connections(business_id,account_id,location_id,category_id,is_enabled) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE account_id=VALUES(account_id),location_id=VALUES(location_id),category_id=VALUES(category_id),is_enabled=1');$st->execute([$bid,$account,$location,$category?:null]);audit((int)$u['id'],$bid,'gmb_connection_saved');json_response(['ok'=>true]);
