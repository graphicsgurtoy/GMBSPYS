<?php
require_once __DIR__ . '/../bootstrap.php';
$slug=clean_slug((string)($_GET['slug']??''));
if($slug==='') json_response(['ok'=>false,'error'=>'Missing business slug'],422);
$st=db()->prepare("SELECT * FROM businesses WHERE slug=? AND status='active' LIMIT 1"); $st->execute([$slug]); $b=$st->fetch();
if(!$b) json_response(['ok'=>false,'error'=>'Business not found'],404);
$staff=db()->prepare('SELECT id,name,designation,staff_code FROM staff WHERE business_id=? AND is_active=1 ORDER BY name'); $staff->execute([$b['id']]);
$social=db()->prepare('SELECT platform,url FROM social_links WHERE business_id=? AND is_enabled=1 ORDER BY sort_order,id'); $social->execute([$b['id']]);
$settings=db()->prepare('SELECT * FROM business_settings WHERE business_id=?'); $settings->execute([$b['id']]); $settings=$settings->fetch()?:[];
$settings['review_tags']=json_decode((string)($settings['review_tags_json']??'[]'),true)?:[]; unset($settings['review_tags_json']);
json_response(['ok'=>true,'business'=>$b,'staff'=>$staff->fetchAll(),'socials'=>$social->fetchAll(),'settings'=>$settings]);
