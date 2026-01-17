<?php
/*
 * Chart Data Processing
 * Aggregates selling data for charts
 */

// Aggregate revenue by date
$revenueByDate = array();
$productCount = array();

for ($i = 0; $i < $TotalReg; $i++) {
    $getname = explode("-|-", $getData[$i]['name']);

    // Skip if prefix filter doesn't match
    if ($prefix != "" && substr($getname[2], 0, strlen($prefix)) != $prefix) {
        continue;
    }

    $date = $getname[0];
    $price = floatval($getname[3]);
    $profile = isset($getname[7]) ? $getname[7] : 'Unknown';

    // Aggregate revenue by date
    if (!isset($revenueByDate[$date])) {
        $revenueByDate[$date] = 0;
    }
    $revenueByDate[$date] += $price;

    // Count products
    if (!isset($productCount[$profile])) {
        $productCount[$profile] = 0;
    }
    $productCount[$profile]++;
}

// Sort by date
ksort($revenueByDate);

// Sort products by count (descending)
arsort($productCount);

// Prepare data for JSON
$revenueDates = array_keys($revenueByDate);
$revenueAmounts = array_values($revenueByDate);

// Get top 10 products
$topProducts = array_slice($productCount, 0, 10, true);
$productNames = array_keys($topProducts);
$productCounts = array_values($topProducts);

// Calculate total for percentages
$totalProducts = array_sum($productCount);
?>