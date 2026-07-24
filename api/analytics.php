<?php
/**
 * ContractPeer - Analytics API
 * Returns page view stats. Protected by internal API key.
 */
require_once __DIR__ . '/../includes/config.php';

$apiKey = $_GET['key'] ?? '';
if ($apiKey !== getenv('INTERNAL_API_KEY') && $apiKey !== 'contractpeer-internal-2026') {
    json_response(['error' => 'Unauthorized'], 403);
}

$db = db();
$period = $_GET['period'] ?? '7d';

switch ($period) {
    case '24h': $since = "datetime('now', '-24 hours')"; break;
    case '7d': $since = "datetime('now', '-7 days')"; break;
    case '30d': $since = "datetime('now', '-30 days')"; break;
    default: $since = "datetime('now', '-7 days')";
}

// Total page views
$total = $db->query("SELECT COUNT(*) as c FROM page_views WHERE created_at > {$since}")->fetch()['c'];

// Unique visitors
$unique = $db->query("SELECT COUNT(DISTINCT session_id) as c FROM page_views WHERE created_at > {$since}")->fetch()['c'];

// Top pages
$pages = $db->query("SELECT page, COUNT(*) as count FROM page_views WHERE created_at > {$since} GROUP BY page ORDER BY count DESC LIMIT 10")->fetchAll();

// Top referrers  
$referrers = $db->query("SELECT referrer, COUNT(*) as count FROM page_views WHERE created_at > {$since} AND referrer != '' AND referrer NOT LIKE '%contractpeer.com%' GROUP BY referrer ORDER BY count DESC LIMIT 5")->fetchAll();

// Views by day
$daily = $db->query("SELECT date(created_at) as day, COUNT(*) as count FROM page_views WHERE created_at > {$since} GROUP BY date(created_at) ORDER BY day DESC LIMIT 30")->fetchAll();

json_response([
    'period' => $period,
    'total_page_views' => intval($total),
    'unique_visitors' => intval($unique),
    'top_pages' => $pages,
    'top_referrers' => $referrers,
    'daily' => $daily,
]);
