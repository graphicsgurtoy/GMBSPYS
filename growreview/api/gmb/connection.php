<?php
require_once __DIR__ . '/../bootstrap.php';
$u=require_auth(['super_admin']);
$bid=(int)($_GET['business_id']??0); if(!$bid) json_response(['ok'=>false,'error'=>'business_id is required'],422);
$st=db()->prepare('SELECT * FROM gmb_connections WHERE business_id=?');$st->execute([$bid]);
json_response(['ok'=>true,'connection'=>$st->fetch()?:null]);
