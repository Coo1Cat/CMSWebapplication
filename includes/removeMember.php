<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $memberId = $_POST['memberId'];

  $dbServername = "localhost";
  $dbUsername = "root";
  $dbPassword = "";
  $dbName = "customermanagementdb";

  $conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

  if (!$conn) {
    die('Connection Failed: ' . mysqli_connect_error());
  } else {
    // Check if member is associated with any sales
    $salesSql = "SELECT COUNT(*) as sales_count FROM purchases WHERE member_id = ?";
    $salesStmt = $conn->prepare($salesSql);
    $salesStmt->bind_param("i", $memberId);
    $salesStmt->execute();
    $salesResult = $salesStmt->get_result();
    $salesRow = $salesResult->fetch_assoc();
    if ($salesRow['sales_count'] > 0) {
      // Member is associated with sales
      $_SESSION['error'] = "This member is currently associated with sales. Please remove the associated sales before removing this member.";
      header("Location: ../membershipManagement.php");
      exit();
    }

    // Remove member
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->bind_param("i", $memberId);

    if ($stmt->execute()) {
      $_SESSION['success'] = "Member removed successfully.";
    } else {
      $_SESSION['error'] = "Error removing member: " . $stmt->error;
    }

    $stmt->close();
    mysqli_close($conn);
    header("Location: ../membershipManagement.php");
    exit();
  }
}
?>
