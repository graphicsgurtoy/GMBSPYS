<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST');
$data = request_json();
$count = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='super_admin'")->fetchColumn();
if ($count > 0) json_response(['ok'=>false,'error'=>'Super admin already exists'],409);
$name = trim((string)($data['name'] ?? ''));
$email = strtolower(trim((string)($data['email'] ?? '')));
$password = (string)($data['password'] ?? '');
if ($name === '' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
    json_response(['ok'=>false,'error'=>'Name, valid email and password of at least 10 characters are required'],422);
}
$st = db()->prepare("INSERT INTO users(business_id,name,email,password_hash,role) VALUES(NULL,?,?,?,'super_admin')");
$st->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
json_response(['ok'=>true,'message'=>'Super admin created. You can now sign in.']);
