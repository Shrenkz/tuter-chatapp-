<?php
session_start();
include_once "php/config.php";

if (!isset($_SESSION['unique_id'])) {
  header("location: login.php");
}

$sql = mysqli_query($conn, "SELECT * FROM users WHERE unique_id = {$_SESSION['unique_id']}");
if (mysqli_num_rows($sql) > 0) {
  $row = mysqli_fetch_assoc($sql);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newFirstName = mysqli_real_escape_string($conn, $_POST['fname']);
  $newLastName = mysqli_real_escape_string($conn, $_POST['lname']);
  $newPassword = mysqli_real_escape_string($conn, $_POST['password']);
  $newStatus = mysqli_real_escape_string($conn, $_POST['status']);

  $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

  if (isset($_FILES['image'])) {
    $img_name = $_FILES['image']['name'];
    $img_type = $_FILES['image']['type'];
    $tmp_name = $_FILES['image']['tmp_name'];

    $img_explode = explode('.', $img_name);
    $img_ext = end($img_explode);

    $extensions = ["jpeg", "png", "jpg"];
    if (in_array($img_ext, $extensions) === true) {
      $types = ["image/jpeg", "image/jpg", "image/png"];
      if (in_array($img_type, $types) === true) {
        $time = time();
        $new_img_name = $time . $img_name;
        $imageDirectory = "php/images/";

        if (move_uploaded_file($tmp_name, $imageDirectory . $new_img_name)) {
          $newImageName = $new_img_name;

          $updateSql = mysqli_query($conn, "UPDATE users SET fname = '$newFirstName', lname = '$newLastName', password = '$hashedPassword', status = '$newStatus', img = '$newImageName' WHERE unique_id = {$_SESSION['unique_id']}");

          if ($updateSql) {
            header("location: update_profile.php");
            exit();
          } else {
            $error = "Profile update failed.";
          }
        } else {
          $error = "Image upload failed. Please try again!";
        }
      } else {
        $error = "Invalid image type. Please upload JPEG, JPG, or PNG files.";
      }
    } else {
      $error = "Invalid image extension. Please upload JPEG, JPG, or PNG files.";
    }
  } else {
    $updateSql = mysqli_query($conn, "UPDATE users SET fname = '$newFirstName', lname = '$newLastName', password = '$hashedPassword', status = '$newStatus' WHERE unique_id = {$_SESSION['unique_id']}");

    if ($updateSql) {
      header("location: update_profile.php");
      exit();
    } else {
      $error = "Profile update failed.";
    }
  }
}
?>

<?php include_once "header.php"; ?>
<style>
  .form form .field.input select {
  height: 40px;
  width: 100%;
  font-size: 16px;
  padding: 0 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  outline: none;
  cursor: pointer;
  margin-top: 5px; 
}

.form form .field.input label {
  margin-bottom: 5px;
}

.form form .field.input {
  display: flex;
  margin-bottom: 15px;
  flex-direction: column;
  position: relative;
}
</style>
<body>
  <div class="wrapper">
    <section class="form update-profile">
      <header>Update Profile</header>
      <form action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
        <div class="error-text"><?php if (isset($error)) echo $error; ?></div>
        <div class="name-details">
          <div class="field input">
            <label>First Name</label>
            <input type="text" name="fname" placeholder="First name" value="<?php echo $row['fname']; ?>" required>
          </div>
          <div class="field input">
            <label>Last Name</label>
            <input type="text" name="lname" placeholder="Last name" value="<?php echo $row['lname']; ?>" required>
          </div>
        </div>
        <div class="field input">
          <label>New Password</label>
          <input type="password" name="password" placeholder="Enter new password" required>
          <i class="fas fa-eye"></i>
        </div>
        <div class="field input">
          <label>Status</label>
          <select name="status" required>
            <option value="Active now" <?php echo ($row['status'] == 'Active now') ? 'selected' : ''; ?>>Active now</option>
            <option value="Offline now" <?php echo ($row['status'] == 'Offline now') ? 'selected' : ''; ?>>Offline now</option>
          </select>
        </div>
        <div class="field image">
          <label>Select New Image</label>
          <input type="file" name="image" accept="image/x-png,image/gif,image/jpeg,image/jpg">
        </div>
        <div class="field button">
          <input type="submit" name="submit" value="Update Profile">
        </div>
      </form>
      <div class="link"><a href="users.php">Back to Chat</a></div>
    </section>
  </div>

  <script src="javascript/pass-show-hide.js"></script>
  <script src="javascript/confirm_password.js"></script>
</body>
</html>
