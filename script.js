// scripts.js
// Function to show a specific section and load data if necessary

function showSection(sectionId) {
    var sections = document.getElementsByClassName('section');
    for (var i = 0; i < sections.length; i++) {
        sections[i].style.display = 'none';
    }
    var selectedSection = document.getElementById(sectionId);
    if (selectedSection) {
        selectedSection.style.display = 'block';

        var links = document.querySelectorAll('.sidebar a');
        for (var i = 0; i < links.length; i++) {
            links[i].classList.remove('active');
        }
        document.querySelector('.sidebar a[href="javascript:void(0)"][onclick="showSection(\'' + sectionId + '\')"]').classList.add('active');

        // Load data for the selected section if necessary
        if (sectionId === 'employees') {
            loadEmployeesTable();
        } else if (sectionId === 'salaryReceipts') {
            loadSalaryReceiptsTable();
        } else if (sectionId === 'deletedEmployees') {
            loadDeletedEmployeesTable();
        } else if (sectionId === 'salary') {
            loadSalaryTable();
        } else if (sectionId === 'dashboard') {
            loadDashboardTable();
        } else {
            console.error("Section with ID", sectionId, "not found.");
        }
    }
}

// Function to set up periodic reloads
function setupPeriodicReloads() {
    setInterval(loadEmployeesTable, 1000); // Reload every 30 seconds
    setInterval(loadSalaryReceiptsTable, 1000);
    setInterval(loadDeletedEmployeesTable, 1000);
    setInterval(loadSalaryTable, 1000);
    setInterval(loadDashboardTable, 1000);
}

  
  window.onload = function() {
    showSection('dashboard');
    loadEmployeesTable(); // Load employees table on page load
    loadSalaryTable(); // Load salary table on page load
    loadDashboardTable();
    loadDeletedEmployeesTable();
    loadSalaryReceiptsTable();
    setupPeriodicReloads(); // Start periodic reloads
  }
  
// Function to load employees table via AJAX
function loadEmployeesTable() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "table_employees.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById('employeesTable').innerHTML = xhr.responseText;
            } else {
                console.error("Error loading employees table:", xhr.status);
            }
        }
    };
    xhr.send();
}

// Function to load the salary receipts table via AJAX
function loadSalaryReceiptsTable() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "table_salaryReceipts.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById('salaryReceiptsTable').innerHTML = xhr.responseText;
            } else {
                console.error("Error loading salary receipts table:", xhr.status);
            }
        }
    };
    xhr.send();
}

// Function to load the deleted employees table via AJAX
function loadDeletedEmployeesTable() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "table_deletedEmployees.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById('deletedEmployeesTable').innerHTML = xhr.responseText;
            } else {
                console.error("Error loading deleted employees table:", xhr.status);
            }
        }
    };
    xhr.send();
}

// Function to load the salary table via AJAX
function loadSalaryTable() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "table_salary.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById('salaryTable').innerHTML = xhr.responseText;
            } else {
                console.error("Error loading salary table:", xhr.status);
            }
        }
    };
    xhr.send();
}

// Function to load dashboard table via AJAX
function loadDashboardTable() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "table_dashboard.php", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById('dashboardTable').innerHTML = xhr.responseText;
            } else {
                console.error("Error loading dashboard table:", xhr.status);
            }
        }
    };
    xhr.send();
}

// Function to reload the salary table
function reloadSalaryTable() {
    loadSalaryTable();
}

function addEmployee() {
    var name = document.getElementById("name").value;
    var age = document.getElementById("age").value;
    var jobType = document.getElementById("jobType").value;
    var contact = document.getElementById("contact").value;

    // Validate age input
    if (!Number.isInteger(parseInt(age))) {
        alert("Age must be an integer.");
        return false; // Prevent form submission
    }

    // Proceed with adding employee
    if (name && age && jobType && contact) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "handle_ajax.php?action=add", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert(xhr.responseText);
                document.getElementById("employeeForm").reset(); // Reset form fields after successful submission
                startFingerprintEnrollment(name);
                loadEmployeesTable(); // Reload table after enrolling employee
                closeForm('formContainer');
            }
        };
        xhr.send("name=" + encodeURIComponent(name) + "&age=" + encodeURIComponent(age) + "&jobType=" + encodeURIComponent(jobType) + "&contact=" + encodeURIComponent(contact));
        return false; // Prevent form submission
    } else {
        alert("Please fill in all employee details.");
        return false; // Prevent form submission
    }
}

function openForm(formId) {
    document.getElementById("overlay").style.display = "block";
    document.getElementById(formId).style.display = "block";
}


function closeForm(formId) {
    document.getElementById("overlay").style.display = "none";
    document.getElementById(formId).style.display = "none";

}



// Function to start fingerprint enrollment
function startFingerprintEnrollment(employeeName) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "handle_ajax.php?action=startEnrollment", true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.message);
                    checkFingerprintStatus();
                } catch (e) {
                    console.error("Failed to parse JSON response:", xhr.responseText);
                }
            } else {
                console.error("Error in enrollment request:", xhr.status, xhr.statusText);
                console.error("Response:", xhr.responseText);
            }
        }
    };
    xhr.send(JSON.stringify({ employee_name: employeeName }));
}




// Function to edit an employee
function editEmployee() {
    // Get the values of the input fields for the employee details
    var employeeIDInput = document.getElementById("employeeID");
    var newNameInput = document.getElementById("newName");
    var newAgeInput = document.getElementById("newAge");
    var newJobTypeInput = document.getElementById("newJobType");
    var newContactInput = document.getElementById("newContact");

    // Extract the values from the input fields
    var employeeID = employeeIDInput.value.trim();
    var newName = newNameInput.value.trim();
    var newAge = newAgeInput.value.trim();
    var newJobType = newJobTypeInput.value.trim();
    var newContact = newContactInput.value.trim();

    // Validate that all fields are filled (you can customize validation as needed)
    if (!employeeID || !newName || !newAge || !newJobType || !newContact) {
        alert("Please fill in all employee details.");
        return;
    }

    // Prepare the data to be sent via AJAX
    var formData = new FormData();
    formData.append('employeeID', employeeID);
    formData.append('newName', newName);
    formData.append('newAge', newAge);
    formData.append('newJobType', newJobType);
    formData.append('newContact', newContact);

    // Create an AJAX request
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "handle_ajax.php?action=edit", true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                // Handle successful response
                alert(xhr.responseText);
                loadEmployeesTable(); // Reload table after editing employee
                closeForm('editFormContainer'); // Close the form after editing
            } else {
                // Handle error response
                console.error("Error editing employee:", xhr.status);
            }
        }
    };

    // Send the AJAX request with the form data
    xhr.send(formData);

    // Open the edit form container (adjust as per your implementation)
    openEditFormContainer();
}



function openEditFormContainer() {
    // Open the edit form
    openForm(); // Assuming openForm() handles opening the form

    // Display the edit form container
    var editFormContainer = document.getElementById("editFormContainer");
    if (editFormContainer) {
        editFormContainer.style.display = "block";
    }
}


// Function to delete an employee
function deleteEmployee() {
    var employeeID = prompt("Enter employee ID to delete:");

    if (employeeID) {
        var confirmation = confirm("Are you sure you want to delete employee with ID " + employeeID + "?");

        if (confirmation) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "handle_ajax.php?action=delete", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.message);
                        loadEmployeesTable(); // Reload table after deleting employee
                    } catch (e) {
                        console.error("Failed to parse JSON response:", xhr.responseText);
                    }
                }
            };
            xhr.send("employeeID=" + encodeURIComponent(employeeID));
        } else {
            alert("Deletion cancelled.");
        }
    } else {
        alert("Please enter the employee ID.");
    }
}

// // Function to initialize the page
// window.onload = function() {
//     showSection('employees');
//     loadEmployeesTable(); // Load employees table on page load
// };


// Function to print salary receipt for a specific employee
function printReceipt() {
    var employeeID = prompt("Enter Employee ID:");
    if (employeeID != null && employeeID != "") {
        var confirmation = confirm("Are you sure you want to print the salary receipt for Employee ID " + employeeID + "?");
        if (confirmation) {
            $.ajax({
                type: "POST",
                url: "handle_ajax.php",
                data: { action: "printReceipt", employeeID: employeeID },
                success: function(response) {
                    alert(response);
                    reloadSalaryTable(); // Reload salary table after printing receipt
                    loadDashboardTable();
                }
            });
        } else {
            alert("Printing cancelled.");
        }
    } else {
        alert("Invalid Employee ID.");
    }
}

// Function to delete attendance records for a specific employee
function deleteAttendanceRecords(employeeID) {
    $.ajax({
        type: "POST",
        url: "handle_ajax.php",
        data: { action: "deleteAttendanceRecords", employeeID: employeeID },
        success: function(response) {
            alert(response);
            reloadSalaryTable(); // Reload salary table after deleting attendance records
            loadDashboardTable();
        }
    });
}

// Function to print salary receipts for all employees
function printReceiptAll() {
    var confirmation = confirm("Are you sure you want to print salary receipts for all employees?");
    if (confirmation) {
        // AJAX call to handle_ajax.php for printing salary receipts for all employees
        $.ajax({
            type: "POST",
            url: "handle_ajax.php",
            data: { action: "printAllReceipts" },
            success: function(response) {
                alert(response);
                // Reload the dashboard and salary tables after printing receipts
                loadDashboardTable();
                loadSalaryTable();
            }
        });
    } else {
        alert("Printing cancelled.");
    }
}



    function rowClicked(row) {
    var alreadyHighlighted = row.classList.contains('highlight');

    // Remove 'highlight' class from all rows
    var allRows = document.querySelectorAll('.table-row');
    allRows.forEach(function (currentRow) {
        currentRow.classList.remove('highlight');
    });

    // Add 'highlight' class to the clicked row if it wasn't already highlighted
    if (!alreadyHighlighted) {
        row.classList.add('highlight');
    }
}


// Function to display salary receipt summary
function displayReceiptSummary(summary) {
    // Create a div element to hold the receipt
    var receiptDiv = document.createElement('div');
    receiptDiv.classList.add('receipt');

    // Create paragraph elements for each line of the summary
    var lines = summary.split('\n');
    lines.forEach(function(line) {
        var paragraph = document.createElement('p');
        paragraph.textContent = line;
        receiptDiv.appendChild(paragraph);
    });

    // Append the receipt to the body or a designated container
    document.body.appendChild(receiptDiv);
}
