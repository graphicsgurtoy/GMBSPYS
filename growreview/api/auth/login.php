<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST');
$data=request_json();
$email=strtolower(trim((string)($data['email']??'')));
$password=(string)($data['password']??'');
$st=db()->prepare('SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1');
$st->execute([$email]);
$u=$st->fetch();
if(!$u || !password_verify($password,$u['password_hash'])) json_response(['ok'=>false,'error'=>'Invalid email or password'],401);
session_regenerate_id(true);
$_SESSION['user_id']=$u['id'];
$_SESSION['csrf']=bin2hex(random_bytes(24));
db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$u['id']]);
json_response(['ok'=>true,'user'=>['id'=>$u['id'],'business_id'=>$u['business_id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']],'csrf'=>csrf_token()]);
