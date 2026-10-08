<?php
require_once __DIR__ . '/../bootstrap.php';$u=require_auth(['owner','admin','manager','staff']);$bid=(int)$u['business_id'];$st=db()->prepare('SELECT slug,name FROM businesses WHERE id=?');$st->execute([$bid]);$b=$st->fetch();if(!$b)json_response(['ok'=>false,'error'=>'Business not found'],404);json_response(['ok'=>true,'slug'=>$b['slug'],'name'=>$b['name']]);
