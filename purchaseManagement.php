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

// Get members
$members = [];
$sql = "SELECT * FROM members";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
}

// Get items
$items = [];
$sql = "SELECT * FROM cust_item";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}

// Handle form submission
if (isset($_POST['submit'])) {
    $purchase_id = $_POST['purchase_id'];
    $member_id = $_POST['member_id'];
    $member_name = $_POST['member_name'];
    $item_id = $_POST['item_id'];
    $purchase_date = $_POST['purchase_date'];
    $purchase_quantity = $_POST['purchase_quantity'];
    $total_price = $_POST['total_price'];

    // Validate inputs
    $errors = [];

    if ($_POST['membership'] === 'member') {
        if (empty($_POST['phone'])) {
            $errors[] = "Please enter a member phone number.";
        } else {
            // Check if member exists
            $phone = $_POST['phone'];
            $memberSql = "SELECT * FROM members WHERE phone='$phone'";
            $memberResult = mysqli_query($conn, $memberSql);
            if (mysqli_num_rows($memberResult) === 0) {
                $errors[] = "Member with phone number '$phone' does not exist.";
            } else {
                // Get member details
                $memberDetails = mysqli_fetch_assoc($memberResult);
                $member_id = $memberDetails['id'];
                $member_name = $memberDetails['name'];
                $_POST['email'] = $memberDetails['email'];
            }
        }
    }

    if (empty($item_id)) {
        $errors[] = "Please select an item.";
    }

    if (empty($purchase_date)) {
        $errors[] = "Please enter a purchase date.";
    }

    if (empty($purchase_quantity)) {
        $errors[] = "Please enter the purchase quantity.";
    } elseif (!is_numeric($purchase_quantity) || $purchase_quantity <= 0) {
        $errors[] = "Purchase quantity must be a positive number.";
    }

    if (empty($errors)) {
        // Get the item details
        $itemDetailsSql = "SELECT * FROM cust_item WHERE item_id='$item_id'";
        $itemDetailsResult = mysqli_query($conn, $itemDetailsSql);
        $item = mysqli_fetch_assoc($itemDetailsResult);

        // Check if item exists
        if (!$item) {
            $_SESSION['error'] = "Invalid item selected.";
            header("Location: purchaseManagement.php");
            exit();
        }

        // Calculate new quantity and total price
        $newQuantity = $item['item_quantity'] - $purchase_quantity;
        $newTotalPrice = $purchase_quantity * $item['item_price'];

        // Check if quantity is sufficient
        if ($newQuantity < 0) {
            $_SESSION['error'] = "Insufficient quantity available for the selected item.";
            header("Location: purchaseManagement.php");
            exit();
        }

        // Update item quantity
        $updateQuantitySql = "UPDATE cust_item SET item_quantity='$newQuantity' WHERE item_id='$item_id'";
        mysqli_query($conn, $updateQuantitySql);

        // Insert purchase into database
        if ($_POST['membership'] === 'guest') {
            // Guest purchase
            $stmt = $conn->prepare("INSERT INTO purchases (purchase_id,item_id,purchase_date,purchase_quantity,total_price) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $purchase_id, $item_id, $purchase_date, $purchase_quantity, $newTotalPrice);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Purchase added successfully.";
                    header("Location: purchaseManagement.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Error adding purchase";
                    header("Location: purchaseManagement.php");
                    exit();
                }

                $stmt->close();
            } else {
                $_SESSION['error'] = "Error preparing statement";
                header("Location: purchaseManagement.php");
                exit();
            }
        } else {
            // Member purchase
            $stmt = $conn->prepare("INSERT INTO purchases (purchase_id,member_id,member_name,item_id,purchase_date,purchase_quantity,total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sisssss", $purchase_id, $member_id, $member_name, $item_id, $purchase_date, $purchase_quantity, $newTotalPrice);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Purchase added successfully.";
                    header("Location: purchaseManagement.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Error adding purchase";
                    header("Location: purchaseManagement.php");
                    exit();
                }

                $stmt->close();
            } else {
                $_SESSION['error'] = "Error preparing statement";
                header("Location: purchaseManagement.php");
                exit();
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: purchaseManagement.php");
        exit();
    }
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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Purchase Item</title>
    <link rel="stylesheet" href="css/customerManagement.css">
</head>

<body>
    <div id="navbarContainer"></div>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<script>alert('" . $_SESSION['error'] . "');</script>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<script>alert('" . $_SESSION['success'] . "');</script>";
        unset($_SESSION['success']);
    }
    ?>

    <div id="firstHalf">
        <h2>Purchase</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label>Membership:</label>
                <input type="radio" id="membership_guest" name="membership" value="guest" onchange="updateMembership(this.value)" checked>
                <label for="membership_guest">Guest</label>
                <input type="radio" id="membership_member" name="membership" value="member" onchange="updateMembership(this.value)">
                <label for="membership_member">Member</label>

            </div>

            <div id="memberDetails" style="display:none;">
                <div class="form-group">
                    <label for="phone">Member Phone Number:</label>
                    <input type="text" id="phone" name="phone" oninput="updateMemberDetails(this.value)">
                </div>
                <div class="form-group">
                    <label for="member_id">Member ID:</label>
                    <input type="text" id="member_id" name="member_id" readonly>
                </div>
                <div class="form-group">
                    <label for="member_name">Member Name:</label>
                    <input type="text" id="member_name" name="member_name" readonly>
                </div>
                <div class="form-group">
                    <label for="email">Member Email:</label>
                    <input type="text" id="email" name="email" readonly>
                </div>
            </div>

            <h3>Purchase Details</h3>
            <div class="form-group">
                <label for="purchase_id">Purchase ID:</label>
                <input type="text" id="purchase_id" name="purchase_id" value="<?= 'P' . uniqid() ?>" readonly>
            </div>

            <div class="form-group">
                <label for="item_id">Item:</label>
                <select id="item_id" name="item_id" onchange="updateItemDetails(this.value)" required>
                    <option value="">--Select Item--</option>
                    <?php foreach ($items as $item) : ?>
                        <option value="<?= htmlspecialchars($item['item_id']) ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="itemDetails"></div>

            <div class="form-group">
                <label for="purchase_date">Purchase Date:</label>
                <input type="date" id="purchase_date" name="purchase_date" required>
            </div>

            <div class="form-group">
                <label for="purchase_quantity">Purchase Quantity:</label>
                <input type="number" id="purchase_quantity" name="purchase_quantity" min="1" oninput="updateTotalPrice()" required>
            </div>

            <div class="form-group">
                <label for="item_unit_price">Item Unit Price:</label>
                <input type="text" id="item_unit_price" name="item_unit_price" readonly>
            </div>

            <div class="form-group">
                <label for="total_price">Total Price:</label>
                <input type="text" id="total_price" name="total_price" readonly>
            </div>

            <input type="submit" name="submit" value="Add Purchase">
        </form>
    </div>

    <script src="js/navbar.js"></script>

    <!-- Add this script to handle form events -->
    <script>
        function updateMembership(membership) {
            const memberDetailsContainer = document.getElementById('memberDetails');
            if (membership === 'member') {
                memberDetailsContainer.style.display = 'block';
            } else {
                memberDetailsContainer.style.display = 'none';
            }
        }

        function updateMemberDetails(phone) {
            const memberIDField = document.getElementById('member_id');
            const memberNameField = document.getElementById('member_name');
            const emailField = document.getElementById('email');

            if (phone) {
                const member = <?= json_encode($members) ?>.find(m => m.phone === phone);
                if (member) {
                    memberIDField.value = member.id;
                    memberNameField.value = member.name;
                    emailField.value = member.email;
                } else {
                    memberIDField.value = '';
                    memberNameField.value = '';
                    emailField.value = '';
                }
            } else {
                memberIDField.value = '';
                memberNameField.value = '';
                emailField.value = '';
            }
        }

        function updateItemDetails(itemId) {
            const itemDetailsContainer = document.getElementById('itemDetails');
            if (itemId) {
                const item = <?= json_encode($items) ?>.find(i => i.item_id === itemId);
                if (item) {
                    itemDetailsContainer.innerHTML = `
          <div class='form-group'>
            <label>Item ID:</label> ${item.item_id}
          </div>
          <div class='form-group'>
            <label>Item Image:</label><br>
            <img src='${item.item_image}' alt='Item Image' width='100'>
          </div>
          <!--<input type='hidden' id='item_price' value='${item.item_price}'>-->
        `;
                    document.getElementById('item_unit_price').value = item.item_price;
                    updateTotalPrice();
                } else {
                    itemDetailsContainer.innerHTML = '';
                    document.getElementById('item_unit_price').value = '';
                    updateTotalPrice();
                }
            } else {
                itemDetailsContainer.innerHTML = '';
                document.getElementById('item_unit_price').value = '';
                updateTotalPrice();
            }
        }

        function updateTotalPrice() {
            const purchaseQuantity = document.getElementById("purchase_quantity").value;
            const itemUnitPrice = document.getElementById("item_unit_price").value;
            const totalPriceField = document.getElementById("total_price");
            const totalPrice = purchaseQuantity * itemUnitPrice;
            totalPriceField.value = totalPrice.toFixed(2);
        }
    </script>

</body>

</html>