<?php
// handle_ajax.php

header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


// Include the database connection file
include_once('db_connection.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'startEnrollment') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['employee_name'])) {
            $employee_name = $input['employee_name'];
            $output = shell_exec("python3 register_fingerprint.py " . escapeshellarg($employee_name) . " 2>&1");
            header('Content-Type: application/json');
            echo $output; // Ensure the Python script returns JSON
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Employee name not provided']);
        }
    } else {
        handleEmployeeActions($action);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    handleEmployeeActions($_GET['action']);
}

function handleEmployeeActions($action) {
    switch ($action) {
        case 'add':
            addEmployee();
            break;
        case 'edit':
            editEmployee();
            break;
        case 'delete':
            deleteEmployee();
            break;
        default:
            echo json_encode(["status" => "error", "message" => "Invalid employee action specified."]);
            break;
    }
}

// Function to handle salary-related actions
function handleSalaryActions($action, $employeeID = null) {
    switch ($action) {
        case 'printReceipt':
            printReceipt($employeeID);
            break;
        case 'deleteAttendanceRecords':
            deleteAttendanceRecords($employeeID);
            break;
        case 'printAllReceipts':
            printAllReceipts();
            break;
        default:
            echo "Invalid salary action specified.";
            break;
    }
}


// Check for salary-related actions
if (isset($_POST['action'])) {
    handleSalaryActions($_POST['action'], $_POST['employeeID'] ?? null);
}



// Function to add a new employee
function addEmployee() {
    global $conn;

    $name = $_POST['name'] ?? '';
    $age = $_POST['age'] ?? '';
    $jobType = $_POST['jobType'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $date = date("Y-m-d");

    // Validate age input
    if (!ctype_digit($age)) {
        echo json_encode(["status" => "error", "message" => "Age must be an integer."]);
        return;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO employees (Employee_Name, Age, Job_Type, Contact, Date, Fingerprint_Template) VALUES (?, ?, ?, ?, ?, NULL)");
        $stmt->bindParam(1, $name);
        $stmt->bindParam(2, $age);
        $stmt->bindParam(3, $jobType);
        $stmt->bindParam(4, $contact);
        $stmt->bindParam(5, $date);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "Employee added successfully."]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    }
}



// Function to edit an existing employee
function editEmployee() {
    global $conn;

    $employeeID = $_POST['employeeID'] ?? '';
    $newName = $_POST['newName'] ?? '';
    $newAge = $_POST['newAge'] ?? '';
    $newJobType = $_POST['newJobType'] ?? '';
    $newContact = $_POST['newContact'] ?? '';

    try {
        $stmt = $conn->prepare("UPDATE employees SET Employee_Name = ?, Age = ?, Job_Type = ?, Contact = ? WHERE Employee_ID = ?");
        $stmt->bindParam(1, $newName);
        $stmt->bindParam(2, $newAge);
        $stmt->bindParam(3, $newJobType);
        $stmt->bindParam(4, $newContact);
        $stmt->bindParam(5, $employeeID);
        $stmt->execute();
        echo "Employee updated successfully.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


// Function to delete an employee and related attendance records
function deleteEmployee() {
    global $conn;

    $employeeID = $_POST['employeeID'] ?? '';

    if (!$employeeID) {
        echo json_encode(["status" => "error", "message" => "Employee ID not provided."]);
        return;
    }

    try {
        // Fetch employee data
        $stmt = $conn->prepare("SELECT * FROM employees WHERE Employee_ID = ?");
        $stmt->bindParam(1, $employeeID);
        $stmt->execute();
        $employeeData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Insert data into deleted_employees table
        $insertSql = "INSERT INTO deleted_employees (Employee_ID, Employee_Name, Age, Job_Type, Contact, fingerprint_template, Date)
                      VALUES (:employeeID, :employeeName, :age, :jobType, :contact, :fingerprintTemplate, :date)";
        
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bindParam(':employeeID', $employeeData['Employee_ID']);
        $insertStmt->bindParam(':employeeName', $employeeData['Employee_Name']);
        $insertStmt->bindParam(':age', $employeeData['Age']);
        $insertStmt->bindParam(':jobType', $employeeData['Job_Type']);
        $insertStmt->bindParam(':contact', $employeeData['Contact']);
        $insertStmt->bindParam(':fingerprintTemplate', $employeeData['fingerprint_template']);
        $insertStmt->bindParam(':date', $employeeData['Date']);
        $insertStmt->execute();

        // Delete employee from employees table
        $deleteStmt = $conn->prepare("DELETE FROM employees WHERE Employee_ID = ?");
        $deleteStmt->bindParam(1, $employeeID);
        $deleteStmt->execute();

        // Delete related attendance records
        $deleteAttendanceStmt = $conn->prepare("DELETE FROM attendance WHERE Employee_ID = ?");
        $deleteAttendanceStmt->bindParam(1, $employeeID);
        $deleteAttendanceStmt->execute();
        
        // Check if any rows were affected
        if ($deleteStmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Employee and related attendance records deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Employee ID does not exist."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
    }
}


// Function to print salary receipt for a specific employee
function printReceipt($employeeID) {
    global $conn;

    try {
        // Calculate additional data
        $sql = "SELECT Employee_ID, Employee_Name, 
                COUNT(DISTINCT strftime('%Y-%m-%d', Date)) AS DaysOfWork,
                MIN(Date) AS FirstDate,
                MAX(Date) AS LastDate,
                SUM(CASE WHEN strftime('%s', Time_Out) < strftime('%s', Time_In)
                         THEN strftime('%s', Time_Out) + 86400 - strftime('%s', Time_In)
                         ELSE strftime('%s', Time_Out) - strftime('%s', Time_In)
                    END) AS TotalSeconds
                FROM attendance 
                WHERE Employee_ID = :employeeID
                GROUP BY Employee_ID, Employee_Name";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $noOfWorkDays = $row['DaysOfWork'];
        $totalWorkHoursInSeconds = $row['TotalSeconds'];
        $totalWorkHours = gmdate("H:i:s", $totalWorkHoursInSeconds);

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

        // Print the summary
        $summary = "\nSalary receipt printed for \nEmployee ID \n{$row['Employee_ID']} {$row['Employee_Name']}." .
                    "\nNo. Days of work: $noOfWorkDays" .
                    "\nTotal Work Hours: $totalWorkHours" .
                    "\nDate Range: $dateRange" .
                    "\nTotal Salary: $formattedSalary\n\n";
        
        echo $summary;
        
        // Insert receipt summary into salary_receipts table
        $insertSql = "INSERT INTO salary_receipts (Employee_ID, Employee_Name, DaysOfWork, TotalWorkHours, DateRange, TotalSalary, PrintDate)
                      VALUES (:employeeID, :employeeName, :noOfWorkDays, :totalWorkHours, :dateRange, :totalSalary, :printDate)";
        
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bindParam(':employeeID', $row['Employee_ID']);
        $insertStmt->bindParam(':employeeName', $row['Employee_Name']);
        $insertStmt->bindParam(':noOfWorkDays', $noOfWorkDays);
        $insertStmt->bindParam(':totalWorkHours', $totalWorkHours);
        $insertStmt->bindParam(':dateRange', $dateRange);
        $insertStmt->bindParam(':totalSalary', $formattedSalary);
        $insertStmt->bindParam(':printDate', date("Y-m-d H:i:s"));
        $insertStmt->execute();

        // Now, you can delete attendance records for this employee if needed
        deleteAttendanceRecords($employeeID);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Function to delete attendance records for a specific employee
function deleteAttendanceRecords($employeeID) {
    global $conn;

    try {
        $stmt = $conn->prepare("DELETE FROM attendance WHERE Employee_ID = ?");
        $stmt->bindParam(1, $employeeID);
        $stmt->execute();
        echo "Attendance records for Employee ID $employeeID deleted.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Function to print salary receipts for all employees
function printAllReceipts() {
    global $conn;

    try {
        // Fetch all employee IDs
        $stmt = $conn->query("SELECT DISTINCT Employee_ID FROM attendance");
        $employeeIDs = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Print receipts for each employee and delete attendance records
        foreach ($employeeIDs as $employeeID) {
            printReceipt($employeeID);
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>