<?php
include 'config.php';

$stockId = $_GET['stock'] ?? 0;
$time    = $_GET['time']  ?? '';

$stmt = $conn->prepare(
    "DELETE FROM prediction WHERE StockID=? AND predictionDateTime=?"
);
$stmt->bind_param("is", $stockId, $time);
$stmt->execute();

header("Location: dashboard_analyst.php#pred");
exit;
