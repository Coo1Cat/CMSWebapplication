<?php
session_start();

if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

if (isset($_POST['submit'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($password != $confirm_password) {
    $_SESSION['error'] = "Passwords do not match";
    header("Location: register.php");
    exit();
  } elseif (strlen($password) < 10 || !preg_match("/[a-z]/", $password) || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
    $_SESSION['error'] = "Password must be at least 10 characters long and contain at least one uppercase letter, one lowercase letter, and one number";
    header("Location: register.php");
    exit();
  } else {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $dbServername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "customermanagementdb";

    $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

    if (!$conn) {
      die('Connection Failed: ' . mysqli_connect_error());
    } else {
      // Check if username already exists
      $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
      if ($stmt) {
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
          $result = $stmt->get_result();
          if ($result->num_rows > 0) {
            // Username already exists
            $_SESSION['error'] = "Username already taken";
            header("Location: register.php");
            exit();
          } else {
            // Username is available
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt) {
              $stmt->bind_param("ss", $username, $hashed_password);

              if ($stmt->execute()) {
                $_SESSION['success'] = "Account created successfully. Please login.";
                header("Location: login.php");
                exit();
              } else {
                $_SESSION['error'] = "Error creating account";
                header("Location: register.php");
                exit();
              }

              $stmt->close();
            } else {
              $_SESSION['error'] = "Error preparing statement";
              header("Location: register.php");
              exit();
            }
          }
        } else {
          $_SESSION['error'] = "Error executing statement";
          header("Location: register.php");
          exit();
        }

        $stmt->close();
      } else {
        $_SESSION['error'] = "Error preparing statement";
        header("Location: register.php");
        exit();
      }
    }
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Register</title>
  <link rel="stylesheet" href="css/authentication.css">
</head>

<body>
  <!--<h2>Register</h2>--->

  <?php
  if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']);
  }
  ?>

  <form action="" method="POST" id="firstHalf">
    <h2>REGISTER</h2>

    <div class="form-group">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>
    </div>

    <div class="form-group">
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
      <p class="password-requirements">Password must be at least 10 characters long and contain at least one uppercase letter, one lowercase letter, and one number</p>
    </div>

    <div class="form-group">
      <label for="confirm_password">Confirm Password:</label>
      <input type="password" id="confirm_password" name="confirm_password" required>
    </div>

    <input type="submit" name="submit" value="Register">
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
  </form>
</body>

</html>