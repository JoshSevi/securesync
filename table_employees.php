<?php
// get_employees.php
// This script fetches employee data from the database and returns it as HTML

// Include the database connection script
include_once('db_connection.php');
?>
<table>
    <tr>
        <th>Employee ID</th>
        <th>Employee Name</th>
        <th>Age</th>
        <th>Job Type</th>
        <th>Contact</th>
        <th>Date</th>
    </tr>
    <tr class="separator"><td colspan="3"></td></tr> <!-- Adjust colspan based on the number of columns -->
<?php
try {
    // Retrieve employee records sorted by Employee ID
    $sql = "SELECT Employee_ID, Employee_Name, Age, Job_Type, Contact, Date FROM employees ORDER BY Employee_ID";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Output employee records as HTML table rows
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr class='table-row'>";
        echo "<td>{$row['Employee_ID']}</td>";
        echo "<td>{$row['Employee_Name']}</td>";
        echo "<td>{$row['Age']}</td>";
        echo "<td>{$row['Job_Type']}</td>";
        echo "<td>{$row['Contact']}</td>";
        echo "<td>{$row['Date']}</td>";
        echo "</tr>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
</table>
