<?php
session_start();

if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

if (isset($_POST['submit'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  // Check if user exists in database
  $dbServername = "localhost";
  $dbUsername = "root";
  $dbPassword = "";
  $dbName = "customermanagementdb";

  $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

  if (!$conn) {
    die('Connection Failed: ' . mysqli_connect_error());
  } else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt) {
      $stmt->bind_param("s", $username);

      if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
          // User exists
          while ($row = mysqli_fetch_assoc($result)) {
            // Verify password
            if (password_verify($password, $row['password'])) {
              // Password is correct
              $_SESSION['user_id'] = htmlspecialchars($row['id']);
              $_SESSION['username'] = htmlspecialchars($row['username']);
              header("Location: index.php");
              exit();
            } else {
              // Password is incorrect
              $_SESSION['error'] = "Invalid username or password";
              header("Location: login.php");
              exit();
            }
          }
        } else {
          // User does not exist
          $_SESSION['error'] = "Invalid username or password";
          header("Location: login.php");
          exit();
        }
      } else {
        $_SESSION['error'] = "Error executing statement";
        header("Location: login.php");
        exit();
      }

      $stmt->close();
    } else {
      $_SESSION['error'] = "Error preparing statement";
      header("Location: login.php");
      exit();
    }
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Login</title>
  <link rel="stylesheet" href="css/authentication.css">
</head>

<body>
  <!--<h2>Login</h2>-->

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

  <form action="" method="POST" id="firstHalf">
    <h2>LOGIN</h2>

    <div class="form-group">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>
    </div>

    <div class="form-group">
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
    </div>

    <input type="submit" name="submit" value="Login">
    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
  </form>
</body>

</html>
