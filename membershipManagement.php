<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

if (isset($_SESSION['error'])) {
  echo "<script>alert('" . $_SESSION['error'] . "');</script>";
  unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
  echo "<script>alert('" . $_SESSION['success'] . "');</script>";
  unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Membership Management</title>
  <link rel="stylesheet" href="css/customerManagement.css">
</head>

<body>
  
  <div id="navbarContainer"></div>

  <div id="firstHalf">

    <h2>Membership Management</h2>

    <!-- Membership Form -->
    <form id="membershipForm" action="" method="POST">
      <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
      </div>

      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="phone">Phone: (0123456789)</label>
        <input type="text" id="phone" name="phone" pattern="[0-9]+" title="Please enter numbers only" required>
      </div>

      <input type="submit" value="Add Membership">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $name = $_POST['name'];
      $email = $_POST['email'];
      $phone = $_POST['phone'];

      $dbServername = "localhost";
      $dbUsername = "root";
      $dbPassword = "";
      $dbName = "customermanagementdb";

      $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

      if (!$conn) {
        die('Connection Failed: ' . mysqli_connect_error());
      } else {
        $stmt = $conn->prepare("INSERT INTO members (name, email, phone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $phone);

        if ($stmt->execute()) {
          echo "<p id='successMessage'>Membership added successfully...</p>";
        } else {
          echo "<p id='errorMessage'>Error adding membership: " . $stmt->error . "</p>";
        }

        $stmt->close();
        mysqli_close($conn);
      }
    }
    ?>

  </div>
  <div id="secondHalf">
    <!-- Membership List -->
    <h3>Membership List</h3>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="membershipList">
        <?php
        $dbServername = "localhost";
        $dbUsername = "root";
        $dbPassword = "";
        $dbName = "customermanagementdb";

        $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

        if (!$conn) {
          die('Connection Failed: ' . mysqli_connect_error());
        } else {
          $sql = "SELECT * FROM members";
          $result = mysqli_query($conn, $sql);

          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<tr>";
              echo "<td>" . $row['name'] . "</td>";
              echo "<td>" . $row['email'] . "</td>";
              echo "<td>" . $row['phone'] . "</td>";
              echo "<td><form action='includes/removeMember.php' method='POST' onsubmit='return confirmDelete();'><input type='hidden' name='memberId' value='" . $row['id'] . "'><input type='submit' value='Remove'></form></td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='4'>No members found.</td></tr>";
          }

          mysqli_close($conn);
        }
        ?>
      </tbody>
    </table>
  </div>

  <script src="js/navbar.js"></script>
  <script>
    function confirmDelete() {
      return confirm("Are you sure you want to delete this member?");
    }
  </script>
</body>

</html>
