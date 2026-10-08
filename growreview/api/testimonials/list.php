<?php
require_once __DIR__ . '/../bootstrap.php';require_auth(['super_admin']);json_response(['ok'=>true,'testimonials'=>db()->query("SELECT t.*,b.name linked_business FROM testimonials t LEFT JOIN businesses b ON b.id=t.business_id ORDER BY t.sort_order,t.created_at DESC")->fetchAll()]);
