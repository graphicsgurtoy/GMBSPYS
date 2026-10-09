<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_auth(['super_admin']);
$pdo = db();

function tbl(PDO $pdo, string $name): bool {
    $st = $pdo->prepare('SHOW TABLES LIKE ?');
    $st->execute([$name]);
    return (bool)$st->fetchColumn();
}

$hasPlans = tbl($pdo, 'business_subscriptions') && tbl($pdo, 'plans');
$hasBlogs = tbl($pdo, 'blog_posts');
$hasJobs = tbl($pdo, 'gmb_content_jobs');

$blogMetric = $hasBlogs
    ? "(SELECT COUNT(*) FROM blog_posts WHERE status = 'published')"
    : '0';
$jobMetric = $hasJobs
    ? "(SELECT COUNT(*) FROM gmb_content_jobs WHERE status IN ('draft', 'scheduled'))"
    : '0';

$metricsSql = "
SELECT
 (SELECT COUNT(*) FROM businesses) AS businesses,
 (SELECT COUNT(*) FROM businesses WHERE status='active') AS active_businesses,
 (SELECT COUNT(*) FROM staff WHERE is_active=1) AS staff,
 (SELECT COUNT(*) FROM review_drafts) AS reviews,
 (SELECT COUNT(*) FROM form_submissions) AS forms,
 (SELECT COUNT(*) FROM events WHERE event_type IN ('page_view','qr_scan')) AS scans,
 (SELECT COUNT(*) FROM events WHERE event_type='google_review_open') AS google_opens,
 {$blogMetric} AS blogs,
 {$jobMetric} AS queued_jobs
";
$metrics = $pdo->query($metricsSql)->fetch();

$planJoin = $hasPlans
    ? 'LEFT JOIN business_subscriptions bs ON bs.business_id=b.id LEFT JOIN plans p ON p.id=bs.plan_id'
    : '';
$planSelect = $hasPlans ? "COALESCE(p.name,'Basic') AS plan_name" : "'Basic' AS plan_name";

$clientsSql = "
SELECT b.id,b.name,b.slug,b.category,b.city,b.status,b.logo_path,
 {$planSelect},
 COALESCE(st.cnt,0) AS active_staff,
 COALESCE(rv.cnt,0) AS reviews,
 COALESCE(fs.cnt,0) AS forms,
 COALESCE(ev.cnt,0) AS scans
FROM businesses b
{$planJoin}
LEFT JOIN (SELECT business_id,COUNT(*) AS cnt FROM staff WHERE is_active=1 GROUP BY business_id) st ON st.business_id=b.id
LEFT JOIN (SELECT business_id,COUNT(*) AS cnt FROM review_drafts GROUP BY business_id) rv ON rv.business_id=b.id
LEFT JOIN (SELECT business_id,COUNT(*) AS cnt FROM form_submissions GROUP BY business_id) fs ON fs.business_id=b.id
LEFT JOIN (SELECT business_id,COUNT(*) AS cnt FROM events WHERE event_type IN ('page_view','qr_scan') GROUP BY business_id) ev ON ev.business_id=b.id
ORDER BY b.created_at DESC LIMIT 8
";
$clients = $pdo->query($clientsSql)->fetchAll();

$reviewsSql = "
SELECT r.id,r.generated_review,r.word_count,r.language,r.created_at,
 b.name AS business_name,s.name AS staff_name
FROM review_drafts r
JOIN businesses b ON b.id=r.business_id
LEFT JOIN staff s ON s.id=r.staff_id
ORDER BY r.created_at DESC LIMIT 5
";
$reviews = $pdo->query($reviewsSql)->fetchAll();

$jobs = [];
if ($hasJobs) {
    $jobsSql = "
    SELECT j.id,j.content_type,j.status,j.scheduled_at,j.created_at,
     b.name AS business_name
    FROM gmb_content_jobs j
    JOIN businesses b ON b.id=j.business_id
    ORDER BY j.created_at DESC LIMIT 5
    ";
    $jobs = $pdo->query($jobsSql)->fetchAll();
}

json_response([
 'ok'=>true,
 'metrics'=>$metrics,
 'clients'=>$clients,
 'reviews'=>$reviews,
 'jobs'=>$jobs,
 'v5_ready'=>$hasBlogs && tbl($pdo,'gmb_services_workspace')
]);
