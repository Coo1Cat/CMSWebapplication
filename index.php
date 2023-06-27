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

// Get the number of customers
$sqlCount = "SELECT COUNT(*) AS customer_count FROM members";
$resultCount = mysqli_query($conn, $sqlCount);
$customerCount = 0;

if ($resultCount && mysqli_num_rows($resultCount) > 0) {
  $row = mysqli_fetch_assoc($resultCount);
  $customerCount = $row['customer_count'];
}

// Get all customers
$sqlCustomers = "SELECT * FROM members";
$resultCustomers = mysqli_query($conn, $sqlCustomers);

// Update member information
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['member_id']) && isset($_POST['name']) && isset($_POST['phone'])) {
    $memberId = $_POST['member_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];

    $sqlUpdate = "UPDATE customers SET name='$name', phone='$phone' WHERE id='$memberId'";
    $resultUpdate = mysqli_query($conn, $sqlUpdate);

    if ($resultUpdate) {
      echo "<script>
              window.onload = function() {
                window.alert('Member information updated successfully.');
                window.location.href = 'index.php'; // Redirect after showing the alert
              }
            </script>";
      exit(); // Stop further execution of the script
    } else {
      echo "<script>
              window.onload = function() {
                window.alert('Error updating member information: " . mysqli_error($conn) . "');
              }
            </script>";
    }
  }
}

// Delete member
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['delete_customer_id'])) {
    $deleteCustomerId = $_POST['delete_customer_id'];

    $sqlDelete = "DELETE FROM customers WHERE id='$deleteCustomerId'";
    $resultDelete = mysqli_query($conn, $sqlDelete);

    if ($resultDelete) {
      echo "<script>
              window.onload = function() {
                window.alert('Customer deleted successfully.');
                window.location.href = 'index.php'; // Redirect after showing the alert
              }
            </script>";
      exit(); // Stop further execution of the script
    } else {
      echo "<script>
              window.onload = function() {
                window.alert('Error deleting customer: " . mysqli_error($conn) . "');
              }
            </script>";
    }
  }
}

// Get the number of items
$sqlItemCount = "SELECT COUNT(*) AS item_count FROM cust_item";
$resultItemCount = mysqli_query($conn, $sqlItemCount);
$itemCount = 0;

if ($resultItemCount && mysqli_num_rows($resultItemCount) > 0) {
  $row = mysqli_fetch_assoc($resultItemCount);
  $itemCount = $row['item_count'];
}

// Calculate the total quantity of all items
$sqlTotalQuantity = "SELECT SUM(item_quantity) AS total_quantity FROM cust_item";
$resultTotalQuantity = mysqli_query($conn, $sqlTotalQuantity);

if ($resultTotalQuantity && mysqli_num_rows($resultTotalQuantity) > 0) {
  $rowTotalQuantity = mysqli_fetch_assoc($resultTotalQuantity);
  $itemTotalQuantity = $rowTotalQuantity['total_quantity'];
} else {
  $itemTotalQuantity = 0;
}

// Get the total quantity and total price
$sqlTotal = "SELECT SUM(item_quantity) AS total_quantity, SUM(item_quantity * item_price) AS expected_net FROM cust_item";
$resultTotal = mysqli_query($conn, $sqlTotal);
$totalQuantity = 0;
$expectedNet = 0;

if ($resultTotal && mysqli_num_rows($resultTotal) > 0) {
  $row = mysqli_fetch_assoc($resultTotal);
  $totalQuantity = $row['total_quantity'];
  $expectedNet = $row['expected_net'];
}

// Get the item names and quantities
$sqlItems = "SELECT item_name, item_quantity FROM cust_item";
$resultItems = mysqli_query($conn, $sqlItems);

// Get customer purchases
$sqlPurchases = "SELECT member_name, SUM(purchase_quantity) AS total_quantity, SUM(total_price) AS total_transaction, MAX(purchase_date) AS last_transaction FROM purchases GROUP BY member_name";
$resultPurchases = mysqli_query($conn, $sqlPurchases);


// Get the number of purchases
$sqlPurchaseCount = "SELECT COUNT(*) AS purchase_count FROM purchases";
$resultPurchaseCount = mysqli_query($conn, $sqlPurchaseCount);
$purchaseCount = 0;

if ($resultPurchaseCount && mysqli_num_rows($resultPurchaseCount) > 0) {
  $rowPurchaseCount = mysqli_fetch_assoc($resultPurchaseCount);
  $purchaseCount = $rowPurchaseCount['purchase_count'];
}
$username = $_SESSION['username'];

mysqli_close($conn);

?>

<!DOCTYPE html>
<html>

<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
    .tab-button {
      cursor: pointer;
      padding: 8px 16px;
      background-color: #f1f1f1;
      border: none;
      border-bottom: 2px solid transparent;
      transition: background-color 0.3s;
    }

    .tab-button.active {
      background-color: #fff;
      border-bottom: 2px solid #333;
    }

    .data-table {
      display: none;
      padding: 16px;
    }

    .data-table.active {
      display: block;
    }
  </style>
</head>

<body>
  <div id="navbarContainer"></div>

  <h2 style="margin-left: 20px;color: #771717;">Dashboard</h2>

  <section id="notSoMain">
    <div class="widgetout">
      <h3>Welcome User <?php echo $username; ?>, To JJA Enterprise Management System.</h3>
    </div>
  </section>

  <section id="main">
    <div class="widget">
      <h2>Summary Information</h2>
      <p></p>
      <div class="tab-buttons">
        <button class="tab-button active" data-table-id="customer-table">Members</button>
        <button class="tab-button" data-table-id="item-table">Items</button>
        <button class="tab-button" data-table-id="purchase-table">Customer Purchases</button>
      </div>

      <div class="data-table active" id="customer-table">
        <?php
        if ($resultCustomers && mysqli_num_rows($resultCustomers) > 0) {
          $rowNumber = 1;
          while ($customer = mysqli_fetch_assoc($resultCustomers)) {
            echo "<p>No. " . $rowNumber . "</p>";
            echo "<p>Name: " . $customer['name'] . "</p>";
            echo "<p>Phone: " . $customer['phone'] . "</p>";
            echo "<p>Email: " . $customer['email'] . "</p>";
            echo "<hr>";
            $rowNumber++;
          }
        } else {
          echo "No customers found.";
        }
        ?>
      </div>

      <div class="data-table" id="item-table">
        <!-- <h2>Items Information</h2> -->
        <p>Type of Items: <?php echo $itemCount; ?></p>
        <p>Total Quantity: <?php echo $totalQuantity; ?></p>
        <p>Expected Net: $<?php echo $expectedNet; ?></p>
        <h3>Item List:</h3>
        <?php
        if ($resultItems && mysqli_num_rows($resultItems) > 0) {
          $rowNumber = 1;
          while ($item = mysqli_fetch_assoc($resultItems)) {
            echo "<p>No. " . $rowNumber . "</p>";
            echo "<p>Item Name: " . $item['item_name'] . "</p>";
            echo "<p>Quantity: " . $item['item_quantity'] . "</p>";
            echo "<hr>";
            $rowNumber++;
          }
        } else {
          echo "No items found.";
        }
        ?>
      </div>

      <div class="data-table" id="purchase-table">
        <h3>Purchase List:</h3>
        <?php
        if ($resultPurchases && mysqli_num_rows($resultPurchases) > 0) {
          $rowNumber = 1;
          while ($purchase = mysqli_fetch_assoc($resultPurchases)) {
            $customerName = $purchase['member_name'] ?? "Non-Member"; // Check if value is null and assign "Non-Member" if true
            echo "<p>No. " . $rowNumber . "</p>";
            echo "<p>Customer Name: " . $customerName . "</p>";
            echo "<p>Total Purchase Quantity: " . $purchase['total_quantity'] . "</p>";
            echo "<p>Total Transaction: $" . $purchase['total_transaction'] . "</p>";
            echo "<p>Last Transaction Date: " . $purchase['last_transaction'] . "</p>";
            echo "<hr>";
            $rowNumber++;
          }
        } else {
          echo "No customer purchases found.";
        }
        ?>
      </div>
    </div>

    <div class="widget" style="display: flex; flex-direction: column; justify-content: space-between; align-items: center; padding-top: 50px; padding-bottom: 50px;">

      <div class="widget" style="display: flex; flex-direction: column; justify-content: space-between; align-items: center; padding-top: 50px; padding-bottom: 50px; border-style:none; box-shadow:5px 10px;margin-bottom:100px;width:300px;">
        <h1 style="font-size: 30px;">Number of Members: <?php echo $customerCount; ?></h1>

      </div>
      <div class="widget" style="display: flex; flex-direction: column; justify-content: space-between; align-items: center; padding-top: 50px; padding-bottom: 50px;border-style:none; box-shadow:5px 10px;margin-bottom:100px; width:300px;">
        <h1 style="font-size: 30px;">Number of Items: <?php echo $itemTotalQuantity; ?></h1>

      </div>

      <div class="widget" style="display: flex; flex-direction: column; justify-content: space-between; align-items: center; padding-top: 50px; padding-bottom: 50px;border-style:none; box-shadow:5px 10px;width:300px;">
        <h1 style="font-size: 30px;">Number of Purchases: <?php echo $purchaseCount; ?></h1>

      </div>
    </div>


  </section>

  <footer>
    <p>&copy; 2023 ABC Company. All rights reserved.</p>
  </footer>

  <script src="js/navbar.js"></script>
  <script>
    const tabButtons = document.querySelectorAll('.tab-button');
    const dataTables = document.querySelectorAll('.data-table');

    tabButtons.forEach(button => {
      button.addEventListener('click', () => {
        const tableId = button.getAttribute('data-table-id');
        showDataTable(tableId);
      });
    });

    function showDataTable(tableId) {
      tabButtons.forEach(button => {
        if (button.getAttribute('data-table-id') === tableId) {
          button.classList.add('active');
        } else {
          button.classList.remove('active');
        }
      });

      dataTables.forEach(table => {
        if (table.getAttribute('id') === tableId) {
          table.classList.add('active');
        } else {
          table.classList.remove('active');
        }
      });
    }
  </script>
</body>

</html>