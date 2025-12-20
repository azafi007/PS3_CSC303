<?php
// fraud_rules.php

function evaluate_fraud_rules(mysqli $conn, $txId, $stockId, $participantId, $amount) {
    $riskScore = 0;
    $reasonParts = [];

    // RULE 1: unusually large amount for this stock (last 50 tx)
    $sql1 = "
      SELECT AVG(amount) AS avg_amt, STD(amount) AS std_amt
      FROM stock_transaction
      WHERE StockID = $stockId
      ORDER BY timeStamp DESC
      LIMIT 50
    ";
    $res1 = mysqli_query($conn, $sql1);
    if ($res1 && $row1 = mysqli_fetch_assoc($res1)) {
        $avg = (float)$row1['avg_amt'];
        $std = (float)$row1['std_amt'];

        if ($avg > 0 && $std > 0 && $amount > $avg + 3 * $std) {
            $riskScore = max($riskScore, 90);
            $reasonParts[] = "Amount $amount is unusually high vs avg " . round($avg,2);
        }
    }

    // RULE 2: price jump vs last close
    $sql2 = "
      SELECT s.currentPrice,
             ph.closingPrice
      FROM stock s
      LEFT JOIN price_history ph ON s.StockID = ph.StockID
      WHERE s.StockID = $stockId
      ORDER BY ph.recordingTime DESC
      LIMIT 1
    ";
    $res2 = mysqli_query($conn, $sql2);
    if ($res2 && $row2 = mysqli_fetch_assoc($res2)) {
        $curr  = (float)$row2['currentPrice'];
        $close = (float)$row2['closingPrice'];
        if ($curr > 0 && $close > 0) {
            $change = abs($curr - $close) / $close * 100;
            if ($change >= 15) {
                $riskScore = max($riskScore, 75);
                $reasonParts[] = "Price changed " . round($change,1) . "% since last close";
            }
        }
    }

    // RULE 3: burst activity per participant (last 10 minutes)
    $sql3 = "
      SELECT COUNT(*) AS c
      FROM stock_transaction
      WHERE ParticipantID = $participantId
        AND timeStamp >= (NOW() - INTERVAL 10 MINUTE)
    ";
    $res3 = mysqli_query($conn, $sql3);
    if ($res3 && $row3 = mysqli_fetch_assoc($res3)) {
        $countRecent = (int)$row3['c'];
        if ($countRecent > 10) {
            $riskScore = max($riskScore, 60);
            $reasonParts[] = "Participant has $countRecent trades in last 10 minutes";
        }
    }

if ($riskScore > 0) {
    $reason = implode("; ", $reasonParts);
    $reasonEsc = mysqli_real_escape_string($conn, $reason);

    // Adjust column names if your fraud_alert table is different
    $insertAlert = "
      INSERT INTO fraud_alert (TransactionID, riskScore, detectionDate, Details)
      VALUES ($txId, $riskScore, NOW(), '$reasonEsc')
    ";
    mysqli_query($conn, $insertAlert);
}
}
