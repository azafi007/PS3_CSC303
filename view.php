<?php
include 'db.php';

// Fetch Alerts
$sql = "SELECT * FROM fraud_alert ORDER BY AlertID DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fraud Alerts - View</title>
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-panel: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --accent-blue: #3b82f6;
            --accent-red: #ef4444;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Segoe UI', sans-serif;
            padding: 2rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-panel);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th { background: rgba(255,255,255,0.05); color: var(--text-secondary); }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
            cursor: pointer;
            border: none;
        }
        .btn-new { background: var(--accent-blue); color: white; margin-bottom: 1rem; }
        .btn-edit { background: transparent; border: 1px solid var(--border-color); color: var(--text-primary); }
        .btn-delete { background: var(--accent-red); color: white; margin-left: 0.5rem; }
        
        .high-risk { background-color: rgba(239, 68, 68, 0.15); }
    </style>
</head>
<body>

    <h2 style="margin-bottom: 1rem;">Fraud Alerts list</h2>
    <a href="form.php" class="btn btn-new">+ New Alert</a>
    <a href="auditor_dashboard.html" class="btn btn-edit" style="margin-left: 1rem; border: none;">&larr; Dashboard</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Risk Score</th>
                <th>Transaction ID</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo ($row['riskScore'] > 80) ? 'high-risk' : ''; ?>">
                        <td><?php echo $row['AlertID']; ?></td>
                        <td><?php echo $row['riskScore']; ?></td>
                        <td><?php echo $row['TransactionID']; ?></td>
                        <td><?php echo $row['detectionDate']; ?></td>
                        <td>
                            <a href="form.php?id=<?php echo $row['AlertID']; ?>" class="btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?php echo $row['AlertID']; ?>" class="btn btn-delete" onclick="return confirm('Delete this alert?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No alerts found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
