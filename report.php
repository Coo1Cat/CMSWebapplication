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
$sql = "SELECT p.*, c.name as customer_name, i.item_name FROM purchases p JOIN customers c ON p.customer_id=c.id JOIN cust_item i ON p.item_id=i.item_id";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $purchases[] = $row;
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Report Page</title>
  <link rel="stylesheet" href="css/Report.css">
</head>

<body>
  <header>
    <h1>ABC company</h1>
  </header>

  <div id="navbarContainer"></div>

  <h2 style="margin-left: 20px;">Report Page</h2>

  <!-- Report Table -->
  <table>
    <thead>
      <tr>
        <th>Purchase ID</th>
        <th>Customer Name</th>
        <th>Item Name</th>
        <th>Purchase Date</th>
        <th>Purchase Quantity</th>
        <th>Total Price</th>
      </tr>
    </thead>
    <tbody id="ReportList">
      <?php if (count($purchases) > 0) : ?>
        <?php foreach ($purchases as $purchase) : ?>
          <tr>
            <td><?= htmlspecialchars($purchase['purchase_id']) ?></td>
            <td><?= htmlspecialchars($purchase['customer_name']) ?></td>
            <td><?= htmlspecialchars($purchase['item_name']) ?></td>
            <td><?= htmlspecialchars($purchase['purchase_date']) ?></td>
            <td><?= htmlspecialchars($purchase['purchase_quantity']) ?></td>
            <td><?= htmlspecialchars($purchase['total_price']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else : ?>
        <tr>
          <td colspan="6">No purchases found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>

</html>
