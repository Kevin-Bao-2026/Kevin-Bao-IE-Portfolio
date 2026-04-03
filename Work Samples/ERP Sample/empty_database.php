<?php
$servername = "mydb.itap.purdue.edu";
$username = "g1145459";
$password = "IE332suxs";
$database = $username;

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Disable foreign key checks to avoid constraint issues
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Tables to fully truncate
$tablesToTruncate = [
    'OrderDetails',
    'Orders',
    'Customers',
    'Complaints',
    'Pickups',
    'IndividualCustomers',
    'CompanyCustomers'
];

// Truncate transactional tables
foreach ($tablesToTruncate as $table) {
    $sql = "TRUNCATE TABLE $table";
    if (!$conn->query($sql)) {
        echo "Error truncating $table: " . $conn->error;
        exit;
    }
}

// Delete only customers from People table
$deletePeopleSQL = "DELETE FROM People WHERE Email NOT LIKE '%@mariomart.com'";
if (!$conn->query($deletePeopleSQL)) {
    echo "Error deleting from People table: " . $conn->error;
    exit;
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Reset AUTO_INCREMENT on People table to the next available ID
$reset_sql = "
  SET @max_id = (SELECT MAX(PersonID) FROM People);
  SET @max_id = IFNULL(@max_id, 0);
  SET @sql = CONCAT('ALTER TABLE People AUTO_INCREMENT = ', @max_id + 1);
  PREPARE stmt FROM @sql;
  EXECUTE stmt;
  DEALLOCATE PREPARE stmt;
";

if (!$conn->multi_query($reset_sql)) {
    echo "Error resetting AUTO_INCREMENT: " . $conn->error;
}


echo "Database cleared successfully (excluding startup data).";

$conn->close();
?>
