
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('db.php');

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

/* Registration control stuff */
if(isset($_POST['username'])){
    $username = stripslashes($_REQUEST['username']);    // Removes backslashes.
    $username = mysqli_real_escape_string($con, $username);

    $password = stripslashes($_REQUEST['password']);    // Removes backslashes.
    $password = mysqli_real_escape_string($con, $password);

    $email = stripslashes($_REQUEST['email']);          // Removes backslashes.
    $email = mysqli_real_escape_string($con, $email);

    /* Check if username or email already exists */
    $query = "SELECT * FROM `users` WHERE username='$username' OR email='$email'";
    $result = mysqli_query($con, $query) or die(mysqli_error($con));

    $rows = mysqli_num_rows($result);
    if($rows == 0){
        // Get next available ID
        $result = $con->query("SELECT MAX(id) AS max_id FROM users");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $maxid = (int)$row['max_id'];
        } else {
            $maxid = 0;
        }
        $userID = $maxid + 1;
        
        // Insert new user with email
        $query = "INSERT INTO users (id, username, password, email) VALUES ('$userID', '$username', '".md5($password)."', '$email')";
        $insert_result = mysqli_query($con, $query) or die(mysqli_error($con));
        
        if($insert_result) {
            header("Location: index.php");  
            exit();
        } else {
            echo "<p>Error during registration. Please try again.</p>";
        }
    } else {
        echo "<p>Username or email already exists. Please choose another.</p>";
    }
} else {
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Open &#187; Register</title>
        <!-- Styles and Favicon management-->
        <link rel="stylesheet" href="styles.css">
        <link rel="icon" type="image/x-icon" href="images/logos/favicon.png">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <!-- Header and Navigation control -->
        <table class="PineconiumLogoSector">
          <thead>
            <tr>
              <th><img src="images/header.png"></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="navbar">
                  <div class="nav-links">
                      <a href="index.php">Home Page</a>
                      <a href="about.php">About Open</a>
                      <a href="tos.php">Terms of Service</a>
                  </div>
                  <div class="nav-actions">
                      <input type="text" placeholder="Search Openly...">
                      <button>Search!</button>
                      <a href="login.php">Login</a>
                      <a href="register.php">Register</a>
                  </div>
              </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Main Layout-->
        <table class="PineconiumTabNav">
          <tbody>
            <tr>
              <td>
                <h1 class="loginpage_title">Register to Open...</h1>
                <p class="loginpage_text">Register in here to create an Open account. Already have one? <a href="login.php">Login instead</a></p>
                <form name="registration" action="" method="post">
                  <input type="text" name="username" placeholder="Username" required />
                  <input type="email" name="email" placeholder="Email Address" required />
                  <input type="password" name="password" placeholder="Password" required />
                  <input type="submit" name="submit" value="Register" />
              </form>
              </td>
            </tr>
          </tbody>
        </table>
        
        <table class="UpdatesSect">
          <!-- Footer -->
          <tfoot>
            <tr>
                <td><p class="footerText">&copy; Pineconium 2024. All rights reserved. Powered by OpenViHo version 10a</p></td>
            </tr>
          </tfoot>
        </table>
    </body>
</html>
<?php } ?>