<?php
include 'config.php';
$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM stock WHERE StockID=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: dashboard_analyst.php#stocks");
exit;
