<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$dbServername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "customermanagementdb";

$conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
  die('Connection Failed: ' . mysqli_connect_error());
}

// Get purchases
$purchases = [];
$sql = "SELECT p.*, m.name as member_name, i.item_name FROM purchases p LEFT JOIN members m ON p.member_id=m.id JOIN cust_item i ON p.item_id=i.item_id";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $purchases[] = $row;
  }
}

// Handle purchase deletion
if (isset($_POST['delete_purchase'])) {
  $purchaseID = $_POST['delete_purchase'];
  $sql = "DELETE FROM purchases WHERE purchase_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $purchaseID);
  $stmt->execute();
  $stmt->close();
  // Redirect to the sales report page after deletion
  header("Location:salesreport.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Sales Report</title>
  <link rel="stylesheet" href="css/customerManagement.css">
</head>
<body>
  <div id="navbarContainer"></div>

  <div id="secondHalf">
    <h2>Sales Report</h2>
    <table>
      <tr>
        <th>Purchase ID</th>
        <th>Member ID</th>
        <th>Member Name</th>
        <th>Item ID</th>
        <th>Item Name</th>
        <th>Purchase Date</th>
        <th>Quantity</th>
        <th>Total Price (RM)</th>
        <th>Action</th>
      </tr>
      <?php foreach ($purchases as $purchase) : ?>
        <tr>
          <td><?= htmlspecialchars($purchase['purchase_id']) ?></td>
          <td><?= $purchase['member_id'] ? htmlspecialchars($purchase['member_id']) : "Non-Member" ?></td>
          <td><?= $purchase['member_id'] ? htmlspecialchars($purchase['member_name']) : "Non-Member" ?></td>
          <td><?= htmlspecialchars($purchase['item_id']) ?></td>
          <td><?= getItemName($conn, $purchase['item_id']) ?></td>
          <td><?= htmlspecialchars($purchase['purchase_date']) ?></td>
          <td><?= htmlspecialchars($purchase['purchase_quantity']) ?></td>
          <td><?= htmlspecialchars($purchase['total_price']) ?></td>
          <td>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this purchase?');">
              <input type="hidden" name="delete_purchase" value="<?= $purchase['purchase_id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <script src="js/navbar.js"></script>

  <?php
  function getItemName($conn, $itemID) {
    $sql = "SELECT item_name FROM cust_item WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['item_name'];
  }
  ?>
</body>
</html>

