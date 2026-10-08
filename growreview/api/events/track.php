<?php
require_once __DIR__ . '/../bootstrap.php'; require_method('POST'); $d=request_json(); $bid=(int)($d['business_id']??0); $sid=(int)($d['staff_id']??0)?:null; $type=trim((string)($d['event_type']??''));
$allowed=['page_view','qr_scan','google_review_open','review_generated','review_copied','instagram_open','facebook_open','youtube_open','pinterest_open','website_open','whatsapp_open','call_click','directions_open','google_profile_open','form_submit'];
if(!$bid||!in_array($type,$allowed,true))json_response(['ok'=>false,'error'=>'Invalid event'],422);
$st=db()->prepare("SELECT id FROM businesses WHERE id=? AND status='active'");$st->execute([$bid]);if(!$st->fetch())json_response(['ok'=>false,'error'=>'Business not found'],404);
if($sid){$s=db()->prepare('SELECT id FROM staff WHERE id=? AND business_id=? AND is_active=1');$s->execute([$sid,$bid]);if(!$s->fetch())$sid=null;}
$meta=$d['metadata']??null; db()->prepare('INSERT INTO events(business_id,staff_id,event_type,metadata_json) VALUES(?,?,?,?)')->execute([$bid,$sid,$type,$meta?json_encode($meta,JSON_UNESCAPED_UNICODE):null]); json_response(['ok'=>true]);
