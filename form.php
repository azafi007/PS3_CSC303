<?php
include 'db.php';

$id = "";
$transactionId = "";
$riskScore = "";
$detectionDate = "";
$isEdit = false;

// Handle GET request to pre-fill form
if (isset($_GET['id'])) {
    $isEdit = true;
    $id = $_GET['id'];
    $sql = "SELECT * FROM fraud_alert WHERE AlertID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    $transactionId = $data['TransactionID'];
    $riskScore = $data['riskScore'];
    $detectionDate = $data['detectionDate'];
}

// Handle POST request to Save/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transactionId = $_POST['TransactionID'];
    $riskScore = $_POST['riskScore'];
    $detectionDate = $_POST['detectionDate'];
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = $_POST['id'];
        $sql = "UPDATE fraud_alert SET riskScore=?, detectionDate=?, TransactionID=? WHERE AlertID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isii", $riskScore, $detectionDate, $transactionId, $id);
    } else {
        // Insert
        $sql = "INSERT INTO fraud_alert (riskScore, detectionDate, TransactionID) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $riskScore, $detectionDate, $transactionId);
    }
    
    if ($stmt->execute()) {
        header("Location: view.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fraud Alert Form</title>
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-panel: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --accent-blue: #3b82f6;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }
        form {
            background: var(--bg-panel);
            padding: 2rem;
            border-radius: 8px;
            width: 400px;
            border: 1px solid var(--border-color);
        }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); }
        input {
            width: 100%;
            padding: 0.75rem;
            background: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: white;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background: #2563eb; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: var(--text-secondary); text-decoration: none; }
    </style>
</head>
<body>

    <form method="POST" action="">
        <h2 style="margin-top:0"><?php echo $isEdit ? 'Edit' : 'New'; ?> Fraud Alert</h2>
        
        <?php if($isEdit): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Transaction ID</label>
            <input type="number" name="TransactionID" value="<?php echo $transactionId; ?>" required>
        </div>

        <div class="form-group">
            <label>Risk Score</label>
            <input type="number" name="riskScore" value="<?php echo $riskScore; ?>" min="0" max="100" required>
        </div>

        <div class="form-group">
            <label>Detection Date</label>
            <input type="date" name="detectionDate" value="<?php echo $detectionDate; ?>" required>
        </div>

        <button type="submit">Save Alert</button>
        <a href="view.php" class="back-link">Cancel</a>
    </form>

</body>
</html>
