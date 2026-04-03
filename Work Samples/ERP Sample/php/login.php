<?php

// DO NOT EDIT THIS IS PERFECT 
session_start(); // ← You need this at the top to use $_SESSION

$servername = "mydb.ics.purdue.edu";
$username = "g1145459";
$password = "IE332suxs";
$database = $username;

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$t = $_GET['q'];
$tmp = explode(",", $t);
$email = $tmp[0];
$pass  = $tmp[1];

$sql = "SELECT e.PersonID, e.Role, e.Password 
        FROM Employees e 
        JOIN People p ON e.PersonID = p.PersonID 
        WHERE p.Email = '$email'";

$result = mysqli_query($conn, $sql);
$rows = [];

while ($row = mysqli_fetch_array($result)) {
    $rows[] = $row;
}

if (isset($rows[0]) && strcmp($pass, $rows[0]["Password"]) == 0) {
    // ✅ Save session info here
    $_SESSION['PersonID'] = $rows[0]['PersonID'];
    $_SESSION['Role']     = $rows[0]['Role'];

    echo json_encode($rows); // If frontend uses this response
} else {
    echo "Invalid Username or Password";
}

$conn->close();
?>
