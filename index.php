<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance System</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="sidebar">
  <h2 style="color: white; text-align: center;">SecureSync</h2>
  <a href="javascript:void(0)" onclick="showSection('dashboard')" class="active">Dashboard</a>
  <a href="javascript:void(0)" onclick="showSection('employees')">Employees</a>
  <a href="javascript:void(0)" onclick="showSection('salary')">Salary</a>
  <a href="javascript:void(0)" onclick="showSection('salaryReceipts')">Salary Receipts</a>
  <a href="javascript:void(0)" onclick="showSection('deletedEmployees')">Deleted Employees</a>
</div>

<div class="date">
  <?php
    echo date('F d, Y');
  ?>
</div>

<div class="content">
  <div id="dashboard" class="section">
    <h1>Dashboard</h1>
    <table id="dashboardTable"></table>
  </div>

  <div id="employees" class="section" style="display:none;">
    <h1>Employees</h1>
    <table id="employeesTable"></table>

    
    <div id="overlay"></div> <!-- Overlay to darken background -->

<!-- Add Employee Form -->
<div id="formContainer" style="display:none;">
    <h2>Add Employee</h2>
    <form id="employeeForm" onsubmit="return addEmployee()">
        <!-- Your form fields go here -->
        <input type="text" id="name" placeholder="Name">
        <input type="text" id="age" placeholder="Age">
        <input type="text" id="jobType" placeholder="Job Type">
        <input type="text" id="contact" placeholder="Contact">
        <button type="submit">Submit</button>
        <button type="button" onclick="closeForm('formContainer')">Cancel</button>
    </form>
</div>

<!-- Edit Employee Form -->
<!-- Edit Employee Form -->
<div id="editFormContainer" style="display:none;">
    <h2>Edit Employee</h2>
    <form id="editEmployeeForm" onsubmit="return editEmployee()">
        <!-- Input field for Employee ID -->
        <label for="employeeID">Employee ID:</label>
        <input type="text" id="employeeID" name="employeeID" required>

        <!-- Input field for New Name -->
        <label for="newName">New Name:</label>
        <input type="text" id="newName" name="newName" required>

        <!-- Input field for New Age -->
        <label for="newAge">New Age:</label>
        <input type="text" id="newAge" name="newAge" required>

        <!-- Input field for New Job Type -->
        <label for="newJobType">New Job Type:</label>
        <input type="text" id="newJobType" name="newJobType" required>

        <!-- Input field for New Contact -->
        <label for="newContact">New Contact:</label>
        <input type="text" id="newContact" name="newContact" required>

        <!-- Submit and Cancel buttons -->
        <button type="submit">Save Changes</button>
        <button type="button" onclick="closeForm('editFormContainer')">Cancel</button>
    </form>
</div>



    <div class="button-container">
      <!-- Add Employee Button -->

      <button type="button" onclick="openForm('formContainer')">Add Employee</button>
      <button type="button" onclick="openForm('editFormContainer')">Edit Employee</button>
      <button onclick="deleteEmployee()">Delete Employee</button>
    </div>
    <!-- Add Employee form -->
    <div id="addEmployeeForm" style="display: none;">
      <h2>Add Employee</h2>
      <input type="text" id="employeeName" placeholder="Employee Name"><br>
      <input type="text" id="employeeAge" placeholder="Employee Age"><br>
      <input type="text" id="employeeJobType" placeholder="Job Type"><br>
      <input type="text" id="employeeContact" placeholder="Contact"><br>
      <button onclick="submitAddEmployee()">Submit</button>
    </div>
  </div>

  <div id="salary" class="section">
    <h1>Salary</h1>
    <table id="salaryTable"></table>
<div class="button-container">
  <button onclick="printReceipt()">Print Receipt</button>
  <button onclick="printReceiptAll()">Print Receipt All</button>
</div>
</div>

<div id="salary" class="section">
    <h1>Salary</h1>
    <table id="salaryTable"></table>
<div class="button-container">
  <button onclick="printReceipt()">Print Receipt</button>
  <button onclick="printReceiptAll()">Print Receipt All</button>
</div>
</div>
<div id="salaryReceipts" class="section">
    <h1>Salary Receipts</h1>
    <table id="salaryReceiptsTable"></table>
  </div>

  <div id="deletedEmployees" class="section">
    <h1>Deleted Employees</h1>
    <table id="deletedEmployeesTable"></table>
  </div>
</div>
</div>
<script src="script.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</body>
</html>