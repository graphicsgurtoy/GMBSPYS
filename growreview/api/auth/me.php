<?php
require_once __DIR__ . '/../bootstrap.php';
$u=current_user();
if(!$u) json_response(['ok'=>false,'authenticated'=>false],401);
json_response(['ok'=>true,'authenticated'=>true,'user'=>$u,'csrf'=>csrf_token()]);
