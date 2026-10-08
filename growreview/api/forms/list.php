<?php
require_once __DIR__ . '/../bootstrap.php';$u=require_auth(['super_admin','owner','admin','manager','staff']);$bid=business_scope($u,isset($_GET['business_id'])?(int)$_GET['business_id']:null);
$st=db()->prepare('SELECT f.id,f.form_type,f.created_at,c.name,c.phone,c.email,c.city,c.marketing_consent,s.name staff_name FROM form_submissions f LEFT JOIN customers c ON c.id=f.customer_id LEFT JOIN staff s ON s.id=f.staff_id WHERE f.business_id=? ORDER BY f.created_at DESC LIMIT 300');$st->execute([$bid]);json_response(['ok'=>true,'forms'=>$st->fetchAll()]);
