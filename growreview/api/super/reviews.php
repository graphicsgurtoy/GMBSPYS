<?php
require_once __DIR__ . '/../bootstrap.php';
require_auth(['super_admin']);
$limit=max(1,min(200,(int)($_GET['limit']??100)));
$sql="SELECT r.id,r.generated_review,r.customer_feedback,r.keyword_used,r.word_count,r.language,r.created_at,b.name business_name,b.slug,s.name staff_name FROM review_drafts r JOIN businesses b ON b.id=r.business_id LEFT JOIN staff s ON s.id=r.staff_id ORDER BY r.created_at DESC LIMIT {$limit}";
json_response(['ok'=>true,'reviews'=>db()->query($sql)->fetchAll()]);
