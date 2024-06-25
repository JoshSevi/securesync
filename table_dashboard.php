<?php
include_once('db_connection.php');
?>

<table>
        <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Total Hours</th>
            <th>Date</th>
        </tr>
        <tr class="separator"><td colspan="3"></td></tr> <!-- Adjust colspan based on the number of columns -->
        <?php
        include_once('db_connection.php');
        try {
            $sql = "SELECT a.Employee_ID, e.Employee_Name, a.Time_In, COALESCE(a.Time_Out, '---') AS Time_Out, a.Date 
                    FROM attendance a 
                    LEFT JOIN employees e ON a.Employee_ID = e.Employee_ID 
                    ORDER BY a.Date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $timeIn = strtotime($row['Time_In']);
                $timeOut = strtotime($row['Time_Out']);

                if ($timeOut !== false && $timeOut < $timeIn) {
                    $timeOut += 86400; // Add seconds for next day
                }

                $totalSeconds = $timeOut - $timeIn;
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $seconds = $totalSeconds % 60;

                $totalHours = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

                echo "<tr class='table-row' onclick=\"rowClicked(this)\">";
                echo "<td>{$row['Employee_ID']}</td>";
                echo "<td>{$row['Employee_Name']}</td>";
                echo "<td>{$row['Time_In']}</td>";
                echo "<td>{$row['Time_Out']}</td>";
                echo "<td>{$totalHours}</td>";
                echo "<td>{$row['Date']}</td>";
                echo "</tr>";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </table>