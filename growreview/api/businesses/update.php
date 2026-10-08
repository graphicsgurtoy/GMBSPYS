<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST'); $u=require_auth(['super_admin','owner','admin']); require_csrf();
$requested=(int)($_POST['business_id']??0); $bid=business_scope($u,$requested?:null);
$st=db()->prepare('SELECT * FROM businesses WHERE id=?'); $st->execute([$bid]); $old=$st->fetch(); if(!$old) json_response(['ok'=>false,'error'=>'Business not found'],404);
$slug=$old['slug']; $logo=save_image($_FILES['logo']??null,$slug,'logo') ?: $old['logo_path']; $banner=save_image($_FILES['banner']??null,$slug,'banner') ?: $old['banner_path'];
$fields=['name','category','city','description','phone','whatsapp','email','address','maps_url','google_review_url','google_profile_url','website_url','primary_color','secondary_color','accent_color'];
$vals=[]; foreach($fields as $f){$vals[$f]=array_key_exists($f,$_POST)?trim((string)$_POST[$f]):$old[$f];}
$sql='UPDATE businesses SET '.implode(',',array_map(fn($f)=>"$f=?",$fields)).',logo_path=?,banner_path=? WHERE id=?';
$args=array_values($vals); $args[]=$logo; $args[]=$banner; $args[]=$bid; db()->prepare($sql)->execute($args);
if(isset($_POST['review_tags'])){ $tags=array_values(array_filter(array_map('trim',explode(',',(string)$_POST['review_tags'])))); db()->prepare('UPDATE business_settings SET review_tags_json=? WHERE business_id=?')->execute([json_encode($tags,JSON_UNESCAPED_UNICODE),$bid]); }
if(isset($_POST['require_staff_selection'])||isset($_POST['customer_form_enabled'])){ db()->prepare('UPDATE business_settings SET require_staff_selection=?,customer_form_enabled=? WHERE business_id=?')->execute([(int)($_POST['require_staff_selection']??1),(int)($_POST['customer_form_enabled']??1),$bid]); }
if(isset($_POST['socials_json'])){ $socials=json_decode((string)$_POST['socials_json'],true)?:[]; foreach($socials as $platform=>$url){$url=trim((string)$url); if($url===''){db()->prepare('DELETE FROM social_links WHERE business_id=? AND platform=?')->execute([$bid,$platform]);} else {db()->prepare('INSERT INTO social_links(business_id,platform,url,is_enabled) VALUES(?,?,?,1) ON DUPLICATE KEY UPDATE url=VALUES(url),is_enabled=1')->execute([$bid,$platform,$url]);}} }
audit((int)$u['id'],$bid,'business_updated'); json_response(['ok'=>true]);
