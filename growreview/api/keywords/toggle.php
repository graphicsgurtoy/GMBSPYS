<?php
require_once __DIR__ . '/../bootstrap.php'; require_method('POST'); $u=require_auth(['super_admin','owner','admin','manager']); require_csrf(); $d=request_json(); $id=(int)($d['keyword_id']??0);
$st=db()->prepare('SELECT business_id,is_active FROM keywords WHERE id=?');$st->execute([$id]);$k=$st->fetch();if(!$k)json_response(['ok'=>false,'error'=>'Keyword not found'],404);business_scope($u,(int)$k['business_id']);$new=(int)!((int)$k['is_active']);db()->prepare('UPDATE keywords SET is_active=? WHERE id=?')->execute([$new,$id]);json_response(['ok'=>true,'is_active'=>$new]);
