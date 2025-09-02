<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('db.php');

/* User ID pulling system, I call it UIDPS! */
$userID = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($userID === null && isset($_SESSION['username'])) {
    /* is the user logged in but didn't provide a profile ID? */
    $username = $_SESSION['username'];
    $usr_query = "SELECT * FROM users WHERE username='$username'";
    $usr_result = mysqli_query($con, $usr_query);
    if ($usr_result) {
        $usr_dat = mysqli_fetch_assoc($usr_result);
        $userID = $usr_dat['id'];
    } else {
        echo "<p>Error fetching user data: " . mysqli_error($con) . "</p>";
        exit();
    }
} else if ($userID !== null) {
    /* if the user did provide an ID, send them to whatever user's profile has that ID */
    $usr_query = "SELECT * FROM users WHERE id='$userID'";
    $usr_result = mysqli_query($con, $usr_query);
    if (mysqli_num_rows($usr_result) > 0) {
        $usr_dat = mysqli_fetch_assoc($usr_result);
    } else {
        /* if no id is found */
        echo "<p>STOP 100! User ID not found.</p>";
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

/* fetch their videos */
$vid_query = "SELECT * FROM videos WHERE user_id='$userID' ORDER BY creationdate DESC";
$vid_result = mysqli_query($con, $vid_query);
if (!$vid_result) {
    echo "<p>FATAL! Error fetching videos: " . mysqli_error($con) . "</p>";
    exit();
}

/* check if the user is viewing their own profile */
$isOwnProfile = isset($_SESSION['username']) && $usr_dat['username'] === $_SESSION['username'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Open » <?php echo htmlspecialchars($usr_dat['username']); ?></title>
    <!-- Styles and Favicon management-->
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/logos/favicon.png">
    <!-- Re-add a feature YouTube once had but decided to remove, that being profile backgrounds! -->
    <style>
        body {
            background-color: #0a0a0a;
            /* 添加黑色背景作为后备 */
        }
    </style>
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
                            <a href="tos.html">Terms of Service</a>
                        </div>
                        <div class="nav-actions">


                            <form action="search.php" method="GET">
                                <input type="text" name="query" placeholder="Search title name..." required>
                                <input type="hidden" name="user_id" value="<?php echo $userID; ?>">
                                <button type="submit">Search!</button>
                            </form>


                            <?php if (isset($_SESSION['username'])): ?>
                                <a href="upload.php">Upload</a>
                                <a href="profile.php"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                                <a href="logout.php">Logout</a>
                            <?php else: ?>
                                <a href="login.php">Login</a>
                                <a href="register.php">Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Main Layout -->
    <table class="PineconiumTabNav">
        <tbody>
            <tr>
                <td>
                    <div class="profile-header" style="background-image: url('<?php echo htmlspecialchars($usr_dat['bannerpath'] ?? 'images/default_banner.png'); ?>'); 
                               background-size: cover; 
                               background-position: center; 
                               background-repeat: no-repeat;">
                        <div class="profile-info">
                            <div class="profile-picture">
                                <img src="<?php echo htmlspecialchars($usr_dat['id']) . '.png'; ?>" width="72"
                                    height="72" alt="Profile Picture"
                                    onerror="this.src='/images/default_pfp.png'; this.onerror=null;">
                            </div>
                            <h1 class="profile-username"><?php echo htmlspecialchars($usr_dat['username']); ?></h1>
                            <p class="profile-joined">Joined:
                                <?php echo date('F j, Y', strtotime($usr_dat['trn_date'])); ?>
                            </p>
                            <?php if ($isOwnProfile): ?>
                                <button onclick="window.location.href='customize.php'">Customize avatar!</button>
                            <?php else: ?>
                                <button>Subscribe</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <table class="TopStatusArea">
                        <thead>
                            <tr>
                                <div class="title-container">
                                    <h1 class="table_title">User's Videos</h1>
                                </div>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($vid_result->num_rows > 0): ?>
                                <tr>
                                    <td>
                                        <?php while ($row = $vid_result->fetch_assoc()): ?>
                                            <a href="video.php?id=<?php echo $row['id']; ?>" class="video-container-link">
                                                <div class="video-container">
                                                    <div class="video-thumbnail">
                                                        <img src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>"
                                                            alt="Thumbnail">
                                                    </div>
                                                    <div class="video-details">
                                                        <div class="video-title"><?php echo htmlspecialchars($row['title']); ?>
                                                        </div>
                                                        <div class="video-info">
                                                            <?php echo htmlspecialchars($row['views'] ?? '0'); ?> views /
                                                            <?php echo htmlspecialchars($row['duration'] ?? '0'); ?> mins
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endwhile; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <?php if ($isOwnProfile): ?>
                                            <p>You currently have no videos. Why not try <a href="upload.php">uploading</a>
                                                some?</p>
                                        <?php else: ?>
                                            <p>No videos found.</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>


</body>

</html>