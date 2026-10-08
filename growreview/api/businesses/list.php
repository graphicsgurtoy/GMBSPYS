<?php
require_once __DIR__ . '/../bootstrap.php';
require_auth(['super_admin']);
$pdo=db();
$hasPlans=(bool)$pdo->query("SHOW TABLES LIKE 'business_subscriptions'")->fetchColumn();
$planJoin=$hasPlans?"LEFT JOIN business_subscriptions bs ON bs.business_id=b.id LEFT JOIN plans p ON p.id=bs.plan_id":"";
$planSelect=$hasPlans?"COALESCE(p.name,'Basic') plan_name,COALESCE(p.code,'basic') plan_code":"'Basic' plan_name,'basic' plan_code";
$sql="SELECT b.id,b.name,b.slug,b.category,b.city,b.logo_path,b.banner_path,b.status,b.created_at,{$planSelect},
 COALESCE(st.cnt,0) active_staff,COALESCE(rv.cnt,0) reviews,COALESCE(fs.cnt,0) forms,COALESCE(ev.cnt,0) scans
 FROM businesses b {$planJoin}
 LEFT JOIN (SELECT business_id,COUNT(*) cnt FROM staff WHERE is_active=1 GROUP BY business_id) st ON st.business_id=b.id
 LEFT JOIN (SELECT business_id,COUNT(*) cnt FROM review_drafts GROUP BY business_id) rv ON rv.business_id=b.id
 LEFT JOIN (SELECT business_id,COUNT(*) cnt FROM form_submissions GROUP BY business_id) fs ON fs.business_id=b.id
 LEFT JOIN (SELECT business_id,COUNT(*) cnt FROM events WHERE event_type IN ('page_view','qr_scan') GROUP BY business_id) ev ON ev.business_id=b.id
 ORDER BY b.created_at DESC";
json_response(['ok'=>true,'businesses'=>$pdo->query($sql)->fetchAll()]);
