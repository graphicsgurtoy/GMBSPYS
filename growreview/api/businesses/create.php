<?php
require_once __DIR__ . '/../bootstrap.php';
require_method('POST');
$u=require_auth(['super_admin']); require_csrf();
$name=trim((string)($_POST['name']??''));
$slug=clean_slug((string)($_POST['slug']??$name));
$ownerName=trim((string)($_POST['owner_name']??''));
$ownerEmail=strtolower(trim((string)($_POST['owner_email']??'')));
$ownerPassword=(string)($_POST['owner_password']??'');
if($name===''||$slug===''||$ownerName===''||!filter_var($ownerEmail,FILTER_VALIDATE_EMAIL)||strlen($ownerPassword)<10) json_response(['ok'=>false,'error'=>'Business, slug, owner name, valid owner email and 10+ character password are required'],422);
$exists=db()->prepare('SELECT id FROM businesses WHERE slug=?'); $exists->execute([$slug]); if($exists->fetch()) json_response(['ok'=>false,'error'=>'Slug already exists'],409);
$logo=save_image($_FILES['logo']??null,$slug,'logo');
$banner=save_image($_FILES['banner']??null,$slug,'banner');
$pdo=db(); $pdo->beginTransaction();
try{
  $st=$pdo->prepare('INSERT INTO businesses(name,slug,category,city,description,logo_path,banner_path,phone,whatsapp,email,address,maps_url,google_review_url,google_profile_url,website_url,primary_color,secondary_color,accent_color) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
  $st->execute([$name,$slug,trim((string)($_POST['category']??''))?:null,trim((string)($_POST['city']??''))?:null,trim((string)($_POST['description']??''))?:null,$logo,$banner,trim((string)($_POST['phone']??''))?:null,trim((string)($_POST['whatsapp']??''))?:null,trim((string)($_POST['email']??''))?:null,trim((string)($_POST['address']??''))?:null,trim((string)($_POST['maps_url']??''))?:null,trim((string)($_POST['google_review_url']??''))?:null,trim((string)($_POST['google_profile_url']??''))?:null,trim((string)($_POST['website_url']??''))?:null,$_POST['primary_color']??'#4285F4',$_POST['secondary_color']??'#111827',$_POST['accent_color']??'#34A853']);
  $bid=(int)$pdo->lastInsertId();
  $tags=array_values(array_filter(array_map('trim',explode(',',(string)($_POST['review_tags']??'Service,Staff,Quality,Collection,Experience,Value')))));
  $pdo->prepare('INSERT INTO business_settings(business_id,review_tags_json) VALUES(?,?)')->execute([$bid,json_encode($tags,JSON_UNESCAPED_UNICODE)]);
  $pdo->prepare("INSERT INTO users(business_id,name,email,password_hash,role) VALUES(?,?,?,?,'owner')")->execute([$bid,$ownerName,$ownerEmail,password_hash($ownerPassword,PASSWORD_DEFAULT)]);
  $planCode=trim((string)($_POST['plan_code']??'basic')) ?: 'basic';
  $pq=$pdo->prepare('SELECT id FROM plans WHERE code=? AND is_active=1 LIMIT 1');$pq->execute([$planCode]);$planId=(int)($pq->fetchColumn()?:0);
  if($planId){$pdo->prepare('INSERT INTO business_subscriptions(business_id,plan_id,status) VALUES(?,?,\'active\') ON DUPLICATE KEY UPDATE plan_id=VALUES(plan_id),status=\'active\'')->execute([$bid,$planId]);}
  $socials=json_decode((string)($_POST['socials_json']??'{}'),true) ?: [];
  $sort=0;
  foreach($socials as $platform=>$url){ $url=trim((string)$url); if($url==='')continue; $pdo->prepare('INSERT INTO social_links(business_id,platform,url,is_enabled,sort_order) VALUES(?,?,?,1,?)')->execute([$bid,$platform,$url,$sort++]); }
  $initialKeywords=array_values(array_filter(array_map('trim',preg_split('/\r\n|\r|\n|,/',(string)($_POST['keywords']??'')))));
  foreach($initialKeywords as $kw){ $pdo->prepare('INSERT IGNORE INTO keywords(business_id,keyword,category,priority,is_active) VALUES(?,?,?,50,1)')->execute([$bid,$kw,'Initial']); }
  $pdo->commit(); audit((int)$u['id'],$bid,'business_created',['slug'=>$slug]);
  $url=rtrim((string)envv('APP_URL',''),'\/').'/r/'.$slug;
  json_response(['ok'=>true,'business'=>['id'=>$bid,'name'=>$name,'slug'=>$slug,'public_url'=>$url],'owner_email'=>$ownerEmail]);
}catch(Throwable $e){$pdo->rollBack();throw $e;}
