<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_complaint'])) {
    $cust = (int) $_POST['customer_id'];
    $ord  = (int) $_POST['order_id'];
    $text = trim($_POST['complaint_text']);

    $conn = new mysqli(
        'mydb.itap.purdue.edu',
        'g1145459',
        'IE332suxs',
        'g1145459'
    );
    if ($conn->connect_error) {
        die('DB Conn Error: ' . $conn->connect_error);
    }

    // Check if this Order belongs to the Customer
    $chk = $conn->prepare("SELECT 1 FROM Orders WHERE InvoiceNumber=? AND CustomerID=?");
    $chk->bind_param('ii', $ord, $cust);
    $chk->execute();
    $chk->store_result();

    if ($chk->num_rows === 0) {
        $flash = '<p class="flash error">That Order ID does not belong to this Customer.</p>';
    } else {
        // ✅ Only one correct Sales query
        $empQuery = $conn->query("SELECT PersonID FROM Employees WHERE Role = 'Sales' ORDER BY RAND() LIMIT 1");

        if ($empQuery && $empQuery->num_rows > 0) {
            $empRow = $empQuery->fetch_assoc();
            $salesEmpID = (int) $empRow['PersonID'];

            // Insert the complaint with assigned employee
            $stmt = $conn->prepare("INSERT INTO Complaints(CustomerID, OrderID, ComplaintText, Status, EmployeeID) VALUES (?, ?, ?, 'Open', ?)");
            $stmt->bind_param('iisi', $cust, $ord, $text, $salesEmpID);

            if (!$stmt->execute()) {
                $flash = '<p class="flash error">Insert error: ' . htmlspecialchars($stmt->error) . '</p>';
            } else {
                $flash = '<p class="flash success">Complaint Logged and Assigned to Sales Employee #' . $salesEmpID . '!</p>';
            }
            $stmt->close();
        } else {
            $flash = '<p class="flash error">No sales employee found to assign complaint.</p>';
        }
    }

    $chk->close();
    $conn->close();
}
?>


<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CRM Login & Complaint System</title>
  <link rel="icon" type="image/x-icon" href="images/marioFace.png">
  <link rel="stylesheet" href="../global/styles.css">
</head>
<body>

  <!-- logo header -->
<header style="text-align:center; margin-bottom:50px;">
  <img src="images/logo.jpg" class="page-logo" alt="Mario Mart Logo" />
</header>
  
  <!-- Group Member Section -->
  <div class="group-section">
    <div class="group-member">
      <img src="images/abarius.jpg" alt="Alexandras Abarius">
      <p class="hover-name">Alexandras Abarius</p>
      <p class="static-name">Alexandras Abarius</p>
    </div>
    <div class="group-member">
      <img src="images/bao.jpg" alt="Kevin Bao">
      <p class="hover-name">Kevin Bao</p>
      <p class="static-name">Kevin Bao</p>
    </div>
    <div class="group-member">
      <img src="images/deLeon.jpg" alt="Hector de Leon">
      <p class="hover-name">Hector de Leon</p>
      <p class="static-name">Hector de Leon</p>
    </div>
    <div class="group-member">
        <img src="images/hickman.jpg" alt="Jason Hickman">
        <p class="hover-name">Jason Hickman</p>
        <p class="static-name">Jason Hickman</p>
      </div>
      <div class="group-member">
        <img src="images/keating.jpg" alt="Natalie Keating">
        <p class="hover-name">Natalie Keating</p>
        <p class="static-name">Natalie Keating</p>
      </div>
    
  </div>

  <!-- Side-by-side Forms -->
  <div class="form-container">
    <form class="login-section" action="" onsubmit="login()">
  <h2>Login to Company</h2>
  <input
    type="text"
    id="email"
    name="email"
    placeholder="Email"
    required
  >
  <input
    type="password"
    id="password"
    name="password"
    placeholder="Password"
    required
  >
  <button type="submit" class="action-btn" style="margin-bottom: 10px;">Login</button>
  <button type="button" class="empty-btn" onclick="emptyDatabase()">Empty Database</button>
  <div id="empty-database-message" style="display: none;">Database cleared successfully!</div>
</form>

    <!-- Self‐post to index.php -->
      <form method="post" class="complaint-section">
  <h2>Make a Complaint</h2>
  <input
    type="number"
    name="customer_id"
    placeholder="Customer ID"
    required
  >
  <input
    type="number"
    name="order_id"
    placeholder="Order ID"
    required
  >
  <textarea
    name="complaint_text"
    placeholder="Describe your complaint..."
    rows="6"
    required
  ></textarea>
  <button
    type="submit"
    name="submit_complaint"
    class="action-btn"
  >
    Make Complaint
  </button>
</form>
<!-- THIS is where your flash shows up -->
  <?= $flash ?>

  <div id="temp"></div>

  <script>
      function login() {
        email=document.getElementById("email").value;
        pass=document.getElementById("password").value;
        str=email+","+pass;
        event.preventDefault();
        var xhttp;
        xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
          if (this.readyState == 4 && this.status == 200) {
            var role = JSON.parse(this.responseText)["0"]["Role"];
            console.log(role);
            //Do something with role to create a session and redirect to the new webpage
            var temp = JSON.parse(this.responseText);
            console.log(temp);

            var role = temp[0].Role;
            var name = temp[0].FirstName + " " + temp[0].LastName;
            var id = temp[0].PersonID;
            console.log(role);
            console.log(name);
            console.log(id);
            
            // //start session
            // sessionStorage.setItem("role", role);
            // sessionStorage.setItem("name", name);
            // sessionStorage.setItem("id", id);

            // // //timeout stuff
            // var sessionTimeout = 1;
            // var loginDuration = new Date();
            // loginDuration.setTime(loginDuration.getTime()+(sessionTimeout*60*60*1000));
            document.cookie = "session=valid; role="+role+"; name="+name+"; id="+id+"; path=/";

            // //redirect based on role
            if (role === "Manager") {
              window.location.href = "https://web.ics.purdue.edu/~g1145459/g9_www_source/G9_ERP_Project/managerModule/Mdashboard.php";
            } else if (role === "Sales") {
              window.location.href = "https://web.ics.purdue.edu/~g1145459/g9_www_source/G9_ERP_Project/salesModule/Sdashboard.php";
            } else {
              alert("Invalid Role Recieved.");
            }
          }
        };
      
        xhttp.open("GET", "../global/login.php?q="+str, true);
        xhttp.send();
      }
      </script>

<!-- script for emptying database LOGIN FAILS WHEN THIS IS PRESENT -->
	<script>
function emptyDatabase() {
  if (confirm("Are you sure you want to empty the database?")) {
    fetch("empty_database.php", {
      method: "POST"
    })
    .then(response => response.text())
    .then(data => {
      alert("Database has been cleared.");
    })
    .catch(error => {
      console.error("Error:", error);
      alert("Failed to clear database.");
    });
  }
}
</script>

	  
</body>
</html>
