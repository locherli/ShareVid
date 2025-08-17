<!-- 
* Open Video Hosting Project Main Page
* Version: 10e (August 7th 2024)
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

/* obtain video id from the url, which is numeric instead of a random string */
$vid_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($vid_id === null) {
    echo "<p>STOP 802! Video ID not provided.</p>";
    exit();
}

// Handle like/dislike POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user_query = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($con, $user_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $user_result = mysqli_stmt_get_result($stmt);
    if ($user_row = mysqli_fetch_assoc($user_result)) {
        $user_id = $user_row['id'];
        $type = null;
        if (isset($_POST['like'])) {
            $type = 'like';
        } elseif (isset($_POST['dislike'])) {
            $type = 'dislike';
        }
        if ($type) {
            // Check if already exists
            $check_query = "SELECT type FROM likes WHERE video_id = ? AND user_id = ?";
            $check_stmt = mysqli_prepare($con, $check_query);
            mysqli_stmt_bind_param($check_stmt, "ii", $vid_id, $user_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            if ($check_row = mysqli_fetch_assoc($check_result)) {
                if ($check_row['type'] === $type) {
                    // Already same, remove (toggle off)
                    $delete_query = "DELETE FROM likes WHERE video_id = ? AND user_id = ?";
                    $delete_stmt = mysqli_prepare($con, $delete_query);
                    mysqli_stmt_bind_param($delete_stmt, "ii", $vid_id, $user_id);
                    mysqli_stmt_execute($delete_stmt);
                } else {
                    // Different, update to new type
                    $update_query = "UPDATE likes SET type = ? WHERE video_id = ? AND user_id = ?";
                    $update_stmt = mysqli_prepare($con, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "sii", $type, $vid_id, $user_id);
                    mysqli_stmt_execute($update_stmt);
                }
            } else {
                // Insert new
                $insert_query = "INSERT INTO likes (video_id, user_id, type) VALUES (?, ?, ?)";
                $insert_stmt = mysqli_prepare($con, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, "iis", $vid_id, $user_id, $type);
                mysqli_stmt_execute($insert_stmt);
            }
        }
    }
    // Redirect to prevent form resubmission and refresh counts
    header("Location: video.php?id=" . $vid_id);
    exit();
}

$vid_query = "SELECT videos.*, users.username, users.backgroundpath,
              (SELECT COUNT(*) FROM likes WHERE video_id = videos.id AND type = 'like') as likes,
              (SELECT COUNT(*) FROM likes WHERE video_id = videos.id AND type = 'dislike') as dislikes
              FROM videos 
              JOIN users ON videos.user_id = users.id 
              WHERE videos.id='$vid_id'";
$vid_result = mysqli_query($con, $vid_query);

if (mysqli_num_rows($vid_result) == 0) {
    echo "<p>STOP 801! Video ID requested not found.</p>";
    exit();
}

$vid_data = mysqli_fetch_assoc($vid_result);

$comm_query = "SELECT comments.*, users.username 
                 FROM comments 
                 JOIN users ON comments.user_id = users.id 
                 WHERE comments.video_id='$vid_id' 
                 ORDER BY comments.created_at DESC";
$comm_result = mysqli_query($con, $comm_query);

$simi_query = "SELECT * 
                 FROM videos 
                 WHERE (user_id='{$vid_data['user_id']}' OR title LIKE '%{$vid_data['title']}%') 
                 AND id != '$vid_id' 
                 ORDER BY creationdate DESC 
                 LIMIT 5";
$simi_result = mysqli_query($con, $simi_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Open &#187; <?php echo htmlspecialchars($vid_data['title']); ?></title>
    <!-- Styles and Favicon management-->
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/logos/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<!-- Make the background match the users profile background, akin to what old YouTube is like. -->
<body style="background-color: #000000;">
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
                            <input type="text" placeholder="Search Openly...">
                            <button>Search!</button>
                            <!-- check if the user is signed in -->
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
                    <table class="TopStatusArea">
                        <thead>
                            <tr>
                                <div class="title-container">
                                    <!-- Profile Picture MUST be on the left-hand side to the video title and creator information -->
                                    <h1 class="table_title"><?php echo htmlspecialchars($vid_data['title']); ?></h1><br>
                                    <p>by, <?php echo htmlspecialchars($vid_data['username']); ?> - (SUBCOUNT) subscribers</p>
                                </div>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <video width="100%" controls>
                                        <source src="usergen/vid/<?php echo htmlspecialchars($vid_data['id']); ?>.mp4" type="video/mp4">
                                        <!-- fallout -->
                                        ERROR 901: Your browser doesn't support the video tag.
                                    </video>
                                    <table class="TopStatusArea">
                                        <!-- TODO: Make like and dislike icons and fix stuff regarding the like counter-->
<thead>
    <tr>
        <td>
            <?php echo htmlspecialchars($vid_data['views']); ?> views - 
            <?php if (isset($_SESSION['username'])): ?>
            <form method="POST" action="video.php?id=<?php echo $vid_id; ?>" style="display: inline;">
                <button type="submit" name="like">Like</button> 
            </form>
            <?php else: ?>
            <button disabled>Like (Login required)</button>
            <?php endif; ?>
            <?php echo htmlspecialchars($vid_data['likes']); ?> like(s) - 
            <?php if (isset($_SESSION['username'])): ?>
            <form method="POST" action="video.php?id=<?php echo $vid_id; ?>" style="display: inline;">
                <button type="submit" name="dislike">Dislike</button> 
            </form>
            <?php else: ?>
            <button disabled>Dislike (Login required)</button>
            <?php endif; ?>
            <?php echo htmlspecialchars($vid_data['dislikes']); ?> dislike(s)<br>
            <h3>Description</h3>
        </td>
    </tr>
</thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    Uploaded on: <?php echo htmlspecialchars($vid_data['creationdate']); ?><br>
                                                    <?php echo nl2br(htmlspecialchars($vid_data['description'])); ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <table class="TopStatusArea">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <h1 class="table_title">Similar Videos</h1>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <?php if (mysqli_num_rows($simi_result) > 0): ?>
                                                            <?php while($similar = mysqli_fetch_assoc($simi_result)): ?>
                                                                <div class="similar-video">
                                                                    <a href="video.php?id=<?php echo $similar['id']; ?>">
                                                                        <img src="path_to_thumbnail/<?php echo htmlspecialchars($similar['thumbnailpath']); ?>" alt="Thumbnail">
                                                                        <div><?php echo htmlspecialchars($similar['title']); ?></div>
                                                                    </a>
                                                                </div>
                                                            <?php endwhile; ?>
                                                        <?php else: ?>
                                                            <p>No similar videos found.</p>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <table class="TopStatusArea">
                                            <thead>
                                                <tr>
                                                    <td>
                                                        <h1 class="table_title">Comments</h1>
                                                    </td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <?php if (isset($_SESSION['username'])): ?>
                                                            <form action="comment.php" method="post">
                                                                <input type="hidden" name="video_id" value="<?php echo $vid_id; ?>">
                                                                <textarea name="comment" placeholder="Write a comment..." required></textarea>
                                                                <button type="submit">Post</button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <?php if (mysqli_num_rows($comm_result) > 0): ?>
                                                            <?php while($comment = mysqli_fetch_assoc($comm_result)): ?>
                                                                <div class="comment">
                                                                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>: 
                                                                    <?php echo htmlspecialchars($comment['content']); ?>
                                                                </div>
                                                            <?php endwhile; ?>
                                                        <?php else: ?>
                                                            <p>No comments yet.</p>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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