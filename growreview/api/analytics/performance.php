<?php
require_once __DIR__ . '/../bootstrap.php';
$u = require_auth(['super_admin','owner','admin','manager','staff']);
$bid = business_scope($u, isset($_GET['business_id']) ? (int)$_GET['business_id'] : null);
$period = $_GET['period'] ?? '7d';

function date_condition(string $alias, string $period): string {
    return match ($period) {
        'today' => "DATE({$alias}.created_at)=CURDATE()",
        '30d'   => "{$alias}.created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY)",
        'month' => "{$alias}.created_at>=DATE_FORMAT(NOW(),'%Y-%m-01')",
        'all'   => '1=1',
        default => "{$alias}.created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)",
    };
}

$reviewCond = date_condition('r', $period);
$formCond = date_condition('f', $period);
$googleCond = date_condition('e', $period);
$scanCond = date_condition('e2', $period);

$sql = "SELECT s.id,s.name,s.staff_code,s.designation,
(SELECT COUNT(*) FROM review_drafts r WHERE r.staff_id=s.id AND {$reviewCond}) reviews,
(SELECT COUNT(*) FROM form_submissions f WHERE f.staff_id=s.id AND {$formCond}) forms,
(SELECT COUNT(*) FROM events e WHERE e.staff_id=s.id AND e.event_type='google_review_open' AND {$googleCond}) google_opens,
(SELECT COUNT(*) FROM events e2 WHERE e2.staff_id=s.id AND e2.event_type IN ('page_view','qr_scan') AND {$scanCond}) scans
FROM staff s WHERE s.business_id=? ORDER BY reviews DESC,google_opens DESC,forms DESC,s.name";

$st = db()->prepare($sql);
$st->execute([$bid]);
json_response(['ok'=>true,'period'=>$period,'performance'=>$st->fetchAll()]);
