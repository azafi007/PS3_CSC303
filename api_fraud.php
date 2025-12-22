<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = "SELECT * FROM fraud_alert WHERE AlertID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc());
        } else {
            $sql = "SELECT * FROM fraud_alert ORDER BY AlertID DESC";
            $result = $conn->query($sql);
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
             // Fallback for form data if not JSON
             $data = $_POST;
        }
        
        $riskScore = $data['riskScore'];
        $transactionId = $data['transactionId'];
        $detectionDate = $data['detectionDate'];
        // Status is not in schema but used in UI. We might need to handle it or ignore it.
        // Assuming schema: AlertID, riskScore, detectionDate, TransactionID
        
        $sql = "INSERT INTO fraud_alert (riskScore, detectionDate, TransactionID) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $riskScore, $detectionDate, $transactionId);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Alert created successfully", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["error" => "Error creating alert: " . $stmt->error]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $riskScore = $data['riskScore'];
        $detectionDate = $data['detectionDate'];
        // TransactionID usually shouldn't change for an existing alert, but let's allow it or keep it simple.
        
        $sql = "UPDATE fraud_alert SET riskScore = ?, detectionDate = ? WHERE AlertID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $riskScore, $detectionDate, $id);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Alert updated successfully"]);
        } else {
            echo json_encode(["error" => "Error updating alert: " . $stmt->error]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $sql = "DELETE FROM fraud_alert WHERE AlertID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Alert deleted successfully"]);
        } else {
            echo json_encode(["error" => "Error deleting alert: " . $stmt->error]);
        }
        break;
}

$conn->close();
?>
