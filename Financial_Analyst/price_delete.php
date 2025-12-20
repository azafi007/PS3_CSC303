<?php
include 'config.php';

$stockId = $_GET['stock'] ?? 0;
$time    = $_GET['time']  ?? '';

$stmt = $conn->prepare(
    "DELETE FROM price_history WHERE StockID=? AND recordingTime=?"
);
$stmt->bind_param("is", $stockId, $time);
$stmt->execute();

header("Location: dashboard_analyst.php#history");
exit;
