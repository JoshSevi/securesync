<?php
// table_deletedEmployees.php

// Include the database connection file
include_once('db_connection.php');

try {
    // Query to select all deleted employees
    $sql = "SELECT * FROM deleted_employees";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $deletedEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are any deleted employees
    if (count($deletedEmployees) > 0) {
        echo '<table class="table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Employee ID</th>';
        echo '<th>Employee Name</th>';
        echo '<th>Age</th>';
        echo '<th>Job Type</th>';
        echo '<th>Contact</th>';
        echo '<th>Date</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($deletedEmployees as $employee) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($employee['Employee_ID']) . '</td>';
            echo '<td>' . htmlspecialchars($employee['Employee_Name']) . '</td>';
            echo '<td>' . htmlspecialchars($employee['Age']) . '</td>';
            echo '<td>' . htmlspecialchars($employee['Job_Type']) . '</td>';
            echo '<td>' . htmlspecialchars($employee['Contact']) . '</td>';
            echo '<td>' . htmlspecialchars($employee['Date']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'No deleted employees found.';
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
