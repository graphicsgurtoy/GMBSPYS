<?php
require_once __DIR__ . '/../bootstrap.php'; require_method('POST'); $d=request_json(); $bid=(int)($d['business_id']??0);$sid=(int)($d['staff_id']??0)?:null;$name=trim((string)($d['name']??''));$phone=trim((string)($d['phone']??''));
if(!$bid||$name===''||strlen(preg_replace('/\D/','',$phone))<8)json_response(['ok'=>false,'error'=>'Name and valid phone are required'],422);
$st=db()->prepare("SELECT b.id,s.customer_form_enabled FROM businesses b JOIN business_settings s ON s.business_id=b.id WHERE b.id=? AND b.status='active'");$st->execute([$bid]);$b=$st->fetch();if(!$b||!(int)$b['customer_form_enabled'])json_response(['ok'=>false,'error'=>'Customer form is unavailable'],403);
if($sid){$ss=db()->prepare('SELECT id FROM staff WHERE id=? AND business_id=? AND is_active=1');$ss->execute([$sid,$bid]);if(!$ss->fetch())$sid=null;}
$pdo=db();$pdo->beginTransaction();
try{
  $email=trim((string)($d['email']??''))?:null;$city=trim((string)($d['city']??''))?:null;$notes=trim((string)($d['notes']??''))?:null;$consent=!empty($d['marketing_consent'])?1:0;
  $find=$pdo->prepare('SELECT id FROM customers WHERE business_id=? AND phone=?');$find->execute([$bid,$phone]);$cid=(int)($find->fetchColumn()?:0);
  if($cid){$pdo->prepare('UPDATE customers SET staff_id=COALESCE(?,staff_id),name=?,email=?,city=?,notes=?,marketing_consent=?,last_seen_at=NOW() WHERE id=?')->execute([$sid,$name,$email,$city,$notes,$consent,$cid]);}
  else{$pdo->prepare('INSERT INTO customers(business_id,staff_id,name,phone,email,city,notes,marketing_consent) VALUES(?,?,?,?,?,?,?,?)')->execute([$bid,$sid,$name,$phone,$email,$city,$notes,$consent]);$cid=(int)$pdo->lastInsertId();}
  $payload=['name'=>$name,'phone'=>$phone,'email'=>$email,'city'=>$city,'notes'=>$notes,'marketing_consent'=>$consent];
  $pdo->prepare('INSERT INTO form_submissions(business_id,staff_id,customer_id,form_type,payload_json) VALUES(?,?,?,?,?)')->execute([$bid,$sid,$cid,'customer_registration',json_encode($payload,JSON_UNESCAPED_UNICODE)]);
  $pdo->prepare("INSERT INTO events(business_id,staff_id,event_type,metadata_json) VALUES(?,?, 'form_submit', ?)")->execute([$bid,$sid,json_encode(['customer_id'=>$cid],JSON_UNESCAPED_UNICODE)]);
  $pdo->commit();json_response(['ok'=>true,'customer_id'=>$cid]);
}catch(Throwable $e){$pdo->rollBack();throw $e;}
