<?php
include_once('db_connection.php');
?>

<table>
        <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>No. Days of work</th>
            <th>Total Work Hours</th>
            <th>Date Range</th>
            <th>Total Salary</th>
        </tr>
        <tr class="separator"><td colspan="3"></td></tr> <!-- Adjust colspan based on the number of columns -->
        <?php
        try {
            $sql = "SELECT e.Employee_ID, e.Employee_Name, 
                    COUNT(DISTINCT strftime('%Y-%m-%d', a.Date)) AS DaysOfWork,
                    MIN(a.Date) AS FirstDate,
                    MAX(a.Date) AS LastDate,
                    SUM(CASE WHEN strftime('%s', a.Time_Out) < strftime('%s', a.Time_In)
                             THEN strftime('%s', a.Time_Out) + 86400 - strftime('%s', a.Time_In)
                             ELSE strftime('%s', a.Time_Out) - strftime('%s', a.Time_In)
                        END) AS TotalSeconds
                    FROM employees e
                    LEFT JOIN attendance a ON e.Employee_ID = a.Employee_ID
                    GROUP BY e.Employee_ID, e.Employee_Name 
                    HAVING DaysOfWork > 0
                    ORDER BY e.Employee_ID ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $noOfWorkDays = $row['DaysOfWork'];
                
                // Calculate total work hours
                $totalWorkHoursInSeconds = $row['TotalSeconds'];
                $totalWorkHours = gmdate("H:i
                ", $totalWorkHoursInSeconds);
                            // Ensure non-negative total work hours for salary calculation
            $totalWorkHoursInSeconds = max($totalWorkHoursInSeconds, 0);

            // Calculate total salary
            $payoutPerHour = 56.25;
            $payoutPerMinute = 0.9375;
            $payoutPerSecond = 0.015625;

            $totalSalary = ($totalWorkHoursInSeconds / 3600) * $payoutPerHour +
                           (($totalWorkHoursInSeconds % 3600) / 60) * $payoutPerMinute +
                           ($totalWorkHoursInSeconds % 60) * $payoutPerSecond;

            // Format total salary as currency
            $formattedSalary = "₱" . number_format(max($totalSalary, 0), 2);

            // Format date range
            $dateRange = $row['FirstDate'] . " - " . $row['LastDate'];

            echo "<tr class='table-row' onclick=\"rowClicked(this)\">";
            echo "<td>{$row['Employee_ID']}</td>";
            echo "<td>{$row['Employee_Name']}</td>";
            echo "<td>{$noOfWorkDays}</td>";
            echo "<td>{$totalWorkHours}</td>";
            echo "<td>{$dateRange}</td>";
            echo "<td>{$formattedSalary}</td>";
            echo "</tr>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    ?>
</table>