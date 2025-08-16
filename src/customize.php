<!-- 
* Open Video Hosting Project Main Page
* Version: 10e (Aug. 7th 2024)
*
* Note that some stuff such as donation and database control either have empty or placeholder values.
* It is up to the hoster of this Open page to control how these work and will need to fill in these
* values with their correct data. See HOSTING.MD for more information.
*
* Originally written by Daniel B. (better known as Pineconium) ;-)
-->

<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

/* fetch da data */
$username = $_SESSION['username'];
$usr_query = "SELECT * FROM users WHERE username='$username'";
$usr_result = mysqli_query($con, $usr_query);

if ($usr_result && mysqli_num_rows($usr_result) > 0) {
    $usr_dat = mysqli_fetch_assoc($usr_result);
} else {
    echo "<p>Error fetching user data: " . mysqli_error($con) . "</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $upload_ok = true;

    /* this is where the profile pictures get handelled */
    if (!empty($_FILES['profile_picture']['name'])) {
        $targ_dir = "usergen/img/pfp/";
        $targ_file = $targ_dir . $usr_dat['id'] . ".png";
        $img_file = strtolower(pathinfo($targ_file, PATHINFO_EXTENSION));
        
        /* The next three lines check if the file submitted is...
	    * - A PNG image (JPEG and GIF support soon!)
	    * - Under 5,000,000 (~5MB) big
	    * - And is actually a fucking image
	    */
        
        $check = getimagesize($_FILES['profile_picture']['tmp_name']);
        if ($check === false) {
            echo "Error submiting. Cause? Uploaded file is not a vaild image.";
            $upload_ok = false;
        }

        if ($_FILES['profile_picture']['size'] > 5000000) {
            echo "Error submiting. Cause? File is over 5MB.";
            $upload_ok = false;
        }

        /* check if any errors happening */
        if ($upload_ok) {
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targ_file)) {
                echo "A problem occured while uploading your profile picture.";
                $upload_ok = false;
            }
        }
    }

    /* do the same shit but for backgrounds */
    if (!empty($_FILES['background']['name'])) {
        $targ_dir = "usergen/img/bg/";
        $targ_file = $targ_dir . $usr_dat['id'] . ".png";
        $img_file = strtolower(pathinfo($targ_file, PATHINFO_EXTENSION));
        
        $check = getimagesize($_FILES['background']['tmp_name']);
        if ($check === false) {
            echo "Error submiting. Cause? Uploaded file is not a vaild image.";
            $upload_ok = false;
        }

        if ($_FILES['background']['size'] > 5000000) {
            echo "Error submiting. Cause? File is over 5MB.";
            $upload_ok = false;
        }

        if ($img_file != "png") {
            echo "Error submiting. Cause? Uploaded file is not a PNG image.";
            $upload_ok = false;
        }

        /* check for errors again */
        if ($upload_ok) {
            if (!move_uploaded_file($_FILES['background']['tmp_name'], $targ_file)) {
                echo "A problem occured while uploading your background.";
                $upload_ok = false;
            } else {
                $updateBackgroundQuery = "UPDATE users SET backgroundpath='$targ_file' WHERE id='".$usr_dat['id']."'";
                if (!mysqli_query($con, $updateBackgroundQuery)) {
                    echo "Error updating background path: " . mysqli_error($con);
                }
            }
        }
    }

    if ($upload_ok) {
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Open &raquo; Customize Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/logos/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <table class="PineconiumLogoSector">
        <thead>
            <tr>
                <th><img src="images/header.gif"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="navbar">
                        <div class="nav-links">
                            <a href="index.php">Home Page</a>
                            <a href="about.html">About Open</a>
                            <a href="tos.html">Terms of Service</a>
                        </div>
                        <div class="nav-actions">
                            <input type="text" placeholder="Search Openly...">
                            <button>Search!</button>
                            <a href="profile.php"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="PineconiumTabNav">
        <tbody>
            <tr>
                <td>
		            <center>
                        <h1>Customize Your Profile</h1>
                        <form action="customize.php" method="post" enctype="multipart/form-data">
                            <label for="profile_picture">Profile Picture:</label>
                            <input type="file" name="profile_picture" id="profile_picture" accept="image/png">
                            <br>
                            <label for="background">Background:</label>
                            <input type="file" name="background" id="background" accept="image/png">
                            <br>
                            <label for="banner">Banner:</label>
                            <input type="file" name="banner" id="banner" accept="image/png">
                            <br>
                            <button type="submit">Save Changes</button>
                        </form>
                    </center>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="UpdatesSect">
        <tfoot>
            <tr>
                <td><p class="footerText">&copy; Pineconium 2024. All rights reserved. Powered by OpenViHo version 10a</p></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

