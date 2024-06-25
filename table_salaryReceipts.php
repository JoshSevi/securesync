<?php
// table_salaryReceipts.php

// Include the database connection file
include_once('db_connection.php');

try {
    // Query to select all salary receipts
    $sql = "SELECT * FROM salary_receipts";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $salaryReceipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are any salary receipts
    if (count($salaryReceipts) > 0) {
        echo '<table class="table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Receipt ID</th>';
        echo '<th>Employee ID</th>';
        echo '<th>Employee Name</th>';
        echo '<th>Days Of Work</th>';
        echo '<th>Total Work Hours</th>';
        echo '<th>Date Range</th>';
        echo '<th>Total Salary</th>';
        echo '<th>Print Date</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($salaryReceipts as $receipt) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($receipt['Receipt_ID']) . '</td>';
            echo '<td>' . htmlspecialchars($receipt['Employee_ID']) . '</td>';
            echo '<td>' . htmlspecialchars($receipt['Employee_Name']) . '</td>';
            echo '<td>' . htmlspecialchars($receipt['DaysOfWork']) . '</td>';
            echo '<td>' . htmlspecialchars($receipt['TotalWorkHours']) . '</td>';
            echo '<td>' . htmlspecialchars($receipt['DateRange']) . '</td>';
            echo '<td>' . htmlspecialchars($receipt['TotalSalary']) . '</td>';
            echo '<td>' . htmlspecialchars($receipt['PrintDate']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'No salary receipts found.';
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
