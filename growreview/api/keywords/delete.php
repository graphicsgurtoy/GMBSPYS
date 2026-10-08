<?php
require_once __DIR__ . '/../bootstrap.php'; require_method('POST'); $u=require_auth(['super_admin','owner','admin']); require_csrf(); $d=request_json(); $id=(int)($d['keyword_id']??0);
$st=db()->prepare('SELECT business_id FROM keywords WHERE id=?');$st->execute([$id]);$k=$st->fetch();if(!$k)json_response(['ok'=>false,'error'=>'Keyword not found'],404);business_scope($u,(int)$k['business_id']);db()->prepare('DELETE FROM keywords WHERE id=?')->execute([$id]);json_response(['ok'=>true]);
