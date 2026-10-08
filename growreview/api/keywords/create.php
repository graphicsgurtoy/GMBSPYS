<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST'); $u=require_auth(['super_admin','owner','admin','manager']); require_csrf(); $d=request_json(); $bid=business_scope($u,(int)($d['business_id']??0)?:null);
$kw=trim((string)($d['keyword']??'')); if($kw==='')json_response(['ok'=>false,'error'=>'Keyword required'],422); $priority=max(1,min(100,(int)($d['priority']??50)));
try{db()->prepare('INSERT INTO keywords(business_id,keyword,category,priority,is_active) VALUES(?,?,?,?,1)')->execute([$bid,$kw,trim((string)($d['category']??''))?:null,$priority]);}catch(PDOException $e){if($e->getCode()==='23000')json_response(['ok'=>false,'error'=>'Keyword already exists'],409);throw $e;}
json_response(['ok'=>true]);
