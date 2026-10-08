<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST'); $u=require_auth(['super_admin','owner','admin','manager']); require_csrf(); $d=request_json();
$bid=business_scope($u,(int)($d['business_id']??0)?:null); $name=trim((string)($d['name']??'')); if($name==='')json_response(['ok'=>false,'error'=>'Staff name required'],422);
$code=staff_code($name); $st=db()->prepare('INSERT INTO staff(business_id,name,email,phone,designation,staff_code) VALUES(?,?,?,?,?,?)');
$st->execute([$bid,$name,trim((string)($d['email']??''))?:null,trim((string)($d['phone']??''))?:null,trim((string)($d['designation']??''))?:null,$code]);
audit((int)$u['id'],$bid,'staff_created',['staff_code'=>$code]); json_response(['ok'=>true,'staff'=>['id'=>(int)db()->lastInsertId(),'name'=>$name,'staff_code'=>$code]]);
