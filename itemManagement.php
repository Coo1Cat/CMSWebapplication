<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html>
<div id="navbarContainer"></div>

<head>
  <title>Item Management</title>
  <link rel="stylesheet" href="css/customerManagement.css">
  <style>
    .error {
      color: red;
    }
  </style>
</head>

<body>
  <object data="navbar.html" width="100%" height="50"></object>

  <div id="firstHalf">

    <h2>Item Management</h2>

    <!-- Item Form -->
    <form id="itemForm" action="" method="POST" enctype="multipart/form-data">
      <p class="error" id="validationMessage"></p>
      <?php
      if (isset($_SESSION['success'])) {
        echo "<script>alert('" . $_SESSION['success'] . "');</script>";
        unset($_SESSION['success']);
      }
      if (isset($_SESSION['error'])) {
        echo "<script>alert('" . $_SESSION['error'] . "');</script>";
        unset($_SESSION['error']);
      }
      ?>
      <div class="form-group">
        <label for="item_id">Item ID*:</label>
        <?php
        // Generate a unique item ID
        $item_id = uniqid();
        echo "<input type='text' id='item_id' name='item_id' value='$item_id' readonly>";
        ?>
      </div>

      <div class="form-group">
        <label for="item_name">Item Name*:</label>
        <input type="text" id="item_name" name="item_name" required>
      </div>

      <div class="form-group">
        <label for="item_quantity">Item Quantity*:</label>
        <input type="number" id="item_quantity" name="item_quantity" required>
      </div>

      <div class="form-group">
        <label for="item_price">Item Price*:</label>
        <input type="number" step="0.01" id="item_price" name="item_price" required>
      </div>

      <div class="form-group">
        <label for="item_image">Item Image* (max size: 5MB):</label>
        <input type="file" id="item_image" name="item_image" accept=".jpg,.jpeg,.png" required>
      </div>

      <input type="submit" value="Add Item">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      if (isset($_POST['remove'])) {
        // Remove item
        $item_id = $_POST['item_id'];

        $dbServername = "localhost";
        $dbUsername = "root";
        $dbPassword = "";
        $dbName = "customermanagementdb";

        $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

        if (!$conn) {
          die('Connection Failed: ' . mysqli_connect_error());
        } else {
          // Check if the item is associated with any sales
          $stmt = $conn->prepare("SELECT COUNT(*) FROM purchases WHERE item_id = ?");
          $stmt->bind_param("s", $item_id);
          $stmt->execute();
          $stmt->bind_result($salesCount);
          $stmt->fetch();
          $stmt->close();

          if ($salesCount > 0) {
            // The item is associated with sales, display error message
            echo "<script>";
            echo "alert('This item is currently associated with sales. Please remove the associated sales before removing this item.');";
            echo "window.location = 'itemManagement.php';";
            echo "</script>";
          } else {
            // No associated sales, proceed with item removal
            $stmt = $conn->prepare("DELETE FROM cust_item WHERE item_id = ?");
            $stmt->bind_param("s", $item_id);

            if ($stmt->execute()) {
              $_SESSION['success'] = "Item removed successfully...";
              header("Location: itemManagement.php");
              exit();
            } else {
              $_SESSION['error'] = "Error removing item";
              header("Location: itemManagement.php");
              exit();
            }

            $stmt->close();
            mysqli_close($conn);
          }
        }
      } else {
        // Add item
        $item_id = $_POST['item_id'];
        $item_name = $_POST['item_name'];
        $item_quantity = $_POST['item_quantity'];
        $item_price = $_POST['item_price'];

        // Handle file upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["item_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists and rename it if necessary
        while (file_exists($target_file)) {
          $new_filename = uniqid() . '.' . $imageFileType;
          $target_file = $target_dir . $new_filename;
        }

        // Check if image file is a actual image or fake image
        if (isset($_FILES["item_image"])) {
          $check = getimagesize($_FILES["item_image"]["tmp_name"]);
          if ($check !== false) {
            $uploadOk = 1;
          } else {
            $_SESSION['error'] = "File is not an image";
            header("Location: itemManagement.php");
            exit();
          }
        }

        // Check if file already exists
        if (file_exists($target_file)) {
          $_SESSION['error'] = "File already exists";
          header("Location: itemManagement.php");
          exit();
        }

        // Check file size
        if ($_FILES["item_image"]["size"] > 5000000) {
          $_SESSION['error'] = "File is too large. Maximum allowed size is 5MB";
          header("Location: itemManagement.php");
          exit();
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
          $_SESSION['error'] = "Only JPG, JPEG and PNG files are allowed";
          header("Location: itemManagement.php");
          exit();
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
          $_SESSION['error'] = "File was not uploaded";
          header("Location: itemManagement.php");
          exit();

          // If everything is ok, try to upload file
        } else {
          if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
            // File uploaded successfully
            $dbServername = "localhost";
            $dbUsername = "root";
            $dbPassword = "";
            $dbName = "customermanagementdb";

            $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

            if (!$conn) {
              die('Connection Failed: ' . mysqli_connect_error());
            } else {
              $stmt = $conn->prepare("INSERT INTO cust_item (item_id, item_name, item_quantity, item_price, item_image) VALUES (?, ?, ?, ?, ?)");
              if ($stmt) {
                $stmt->bind_param("ssids", $item_id, $item_name, $item_quantity, $item_price, $target_file);

                if ($stmt->execute()) {
                  $_SESSION['success'] = "Item added successfully...";
                  header("Location: itemManagement.php");
                  exit();
                } else {
                  $_SESSION['error'] = "Error adding item";
                  header("Location: itemManagement.php");
                  exit();
                }

                $stmt->close();
              } else {
                $_SESSION['error'] = "Error preparing statement";
                header("Location: itemManagement.php");
                exit();
              }
            }
          } else {
            $_SESSION['error'] = "Error uploading file";
            header("Location: itemManagement.php");
            exit();
          }
        }
      }
    }
    ?>

  </div>
  <div id="secondHalf">
    <!-- Item List -->
    <h3>Item List</h3>
    <table>
      <thead>
        <tr>
          <th>Item ID</th>
          <th>Item Name</th>
          <th>Item Quantity</th>
          <th>Item Price</th>
          <th>Item Image</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="itemList">
        <?php
        $dbServername = "localhost";
        $dbUsername = "root";
        $dbPassword = "";
        $dbName = "customermanagementdb";

        $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

        if (!$conn) {
          die('Connection Failed: ' . mysqli_connect_error());
        } else {
          $sql = "SELECT * FROM cust_item";
          $result = mysqli_query($conn, $sql);

          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
              echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
              echo "<td>" . htmlspecialchars($row['item_quantity']) . "</td>";
              echo "<td>" . htmlspecialchars(number_format($row['item_price'], 2)) . "</td>";
              echo "<td><img src='" . htmlspecialchars($row['item_image']) . "' width='100'></td>";
              echo "<td><form action='' method='POST'><input type='hidden' name='remove' value='1'><input type='hidden' name='item_id' value='" . htmlspecialchars($row['item_id']) . "'><input type='submit' value='Remove'></form></td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='6'>No items found.</td></tr>";
          }

          mysqli_close($conn);
        }
        ?>
      </tbody>
    </table>
  </div>

  <script src="js/navbar.js"></script>
  <script>
    // Form validation
    document.getElementById("itemForm").addEventListener("submit", function(event) {
      var item_name = document.getElementById("item_name").value;
      var item_quantity = document.getElementById("item_quantity").value;
      var item_price = document.getElementById("item_price").value;
      var item_image = document.getElementById("item_image").value;

      if (item_name == "" || item_quantity == "" || item_price == "" || item_image == "") {
        event.preventDefault();
        document.getElementById("validationMessage").innerHTML = "Please enter all fields marked with a (*)";
      }
    });
  </script>

</body>

</html>