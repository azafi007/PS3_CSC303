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
            $sql = "SELECT * FROM audit_report WHERE ReportID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc());
        } else {
            $sql = "SELECT * FROM audit_report ORDER BY ReportID DESC";
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
        
        $staffID = $data['staffID'];
        $date = $data['date'];
        $summary = $data['summary'];
        
        $sql = "INSERT INTO audit_report (StaffID, Date, FindingsSummary) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $staffID, $date, $summary);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Report created successfully", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["error" => "Error creating report: " . $stmt->error]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $staffID = $data['staffID'];
        $date = $data['date'];
        $summary = $data['summary'];
        
        $sql = "UPDATE audit_report SET StaffID = ?, Date = ?, FindingsSummary = ? WHERE ReportID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $staffID, $date, $summary, $id);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Report updated successfully"]);
        } else {
            echo json_encode(["error" => "Error updating report: " . $stmt->error]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $sql = "DELETE FROM audit_report WHERE ReportID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(["message" => "Report deleted successfully"]);
        } else {
            echo json_encode(["error" => "Error deleting report: " . $stmt->error]);
        }
        break;
}

$conn->close();
?>
