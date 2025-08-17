<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

/* Stanard MySQL time support, which is YYYY-MM-DD HH-MM-SS */
$currentDateTime = date('Y-m-d H:i:s');

/* Fetch videos from the database, I know it looks ugly but shut up. */
$videoQuery = "
    SELECT 
        videos.id,
	      videos.title, 
        videos.filepath, 
        videos.thumbnailpath, 
        videos.creationdate, 
        videos.vidlength,
        videos.views,
        users.username 
    FROM 
        videos 
    INNER JOIN 
        users 
    ON 
        videos.user_id = users.id 
    ORDER BY 
        videos.creationdate DESC
";

$result = $con->query($videoQuery);

/* Do the same but for more popular videos */
$queryTopVideos = "
    SELECT 
        videos.id,
        videos.title, 
        videos.filepath, 
        videos.thumbnailpath, 
        videos.creationdate, 
        videos.vidlength,
        videos.views,
        users.username,
        COUNT(CASE WHEN likes.type = 'like' THEN 1 END) as likes_count
    FROM 
        videos 
    INNER JOIN 
        users 
    ON 
        videos.user_id = users.id 
    LEFT JOIN 
        likes ON likes.video_id = videos.id
    WHERE 
        WEEK(videos.creationdate) = WEEK('$currentDateTime') 
    GROUP BY 
        videos.id
    ORDER BY 
        likes_count DESC
    LIMIT 3
";

$topvidresult = $con->query($queryTopVideos);


$randomVideoQuery = "
    SELECT 
        videos.id,
        videos.title, 
        videos.filepath, 
        videos.thumbnailpath, 
        videos.creationdate, 
        videos.vidlength,
        videos.views,
        users.username 
    FROM 
        videos 
    INNER JOIN 
        users 
    ON 
        videos.user_id = users.id 
    ORDER BY 
        RAND()
    LIMIT 10
";

$RandomVidResult = $con->query($videoQuery);

?>

<!DOCTYPE html>
<html>

<head>
  <title>LoliPron &#187; Home</title>
  <!-- Styles and Favicon management-->
  <link rel="stylesheet" href="styles.css">
  <link rel="icon" type="image/x-icon" href="images/logos/favicon.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <!-- Header and Navagation control -->
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
              <!-- <a href="about.html">About Open</a> -->
              <a href="tos.html">Terms of Service</a>
            </div>
            <div class="nav-actions">
              <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Search title name..." required>
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

  <!-- Main Layout-->
  <table class="PineconiumTabNav">
    <tbody>
      <tr>
        <td>


          <!-- Video list area-->
          <table class="TopStatusArea">


            <thead>
              <tr>
                <div class="title-container">
                  <img src="images/icon_recommend.png" height="24px" width="24px">
                  <h1 class="table_title">Recommended For You</h1>
                </div>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <?php if ($RandomVidResult->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <a href="video.php?id=<?php echo $row['id']; ?>" class="video-container-link">
                        <div class="video-container">
                          <div class="video-thumbnail">
                            <img class="video-thumbnail-image" src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>"
                              alt="Thumbnail">
                          </div>
                          <div class="video-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                          </div>
                          <div class="video-info">
                            by: <?php echo htmlspecialchars($row['username']); ?> /
                            <?php echo htmlspecialchars($row['vidlength']); ?> mins /
                            <?php echo htmlspecialchars($row['views']); ?> views
                          </div>
                        </div>
                      </a>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <p>No videos found. Maybe try <a href="index.php">refreshing</a> or searching?</p>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>

          <table class="TopStatusArea">
            <thead>
              <tr>
                <div class="title-container">
                  <img src="images/icon_newvideos.png" height="24px" width="24px">
                  <h1 class="table_title">Newest Videos</h1>
                </div>
              </tr>
            </thead>
            <tbody>
              <tr>
                <!-- this should perferably be updated every 25 mins to save stuff like updating time. -->
                <td>
                  <?php
                  // Reset the result pointer to reuse the same query result
                  mysqli_data_seek($result, 0);
                  ?>
                  <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <a href="video.php?id=<?php echo $row['id']; ?>" class="video-container-link">
                        <div class="video-container">
                          <div class="video-thumbnail">
                            <img class="video-thumbnail-image" src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>"
                              alt="Thumbnail">
                          </div>
                          <div class="video-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                          </div>
                          <div class="video-info">
                            by: <?php echo htmlspecialchars($row['username']); ?> /
                            <?php echo htmlspecialchars($row['vidlength']); ?> mins /
                            <?php echo htmlspecialchars($row['views']); ?> views
                          </div>
                        </div>
                      </a>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <p>No videos found. Maybe try <a href="index.php">refreshing</a> or searching?</p>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
          <table class="TopStatusArea">
            <thead>
              <tr>
                <div class="title-container">
                  <img src="images/icon_topvideo.png" height="24px" width="24px">
                  <h1 class="table_title">Top Videos this Week</h1>
                </div>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <?php if ($topvidresult->num_rows > 0): ?>
                    <?php while ($row = $topvidresult->fetch_assoc()): ?>
                      <a href="video.php?id=<?php echo $row['id']; ?>" class="video-container-link">
                        <div class="video-container">
                          <div class="video-thumbnail">
                            <img class="video-thumbnail-image" src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>"
                              alt="Thumbnail">
                          </div>
                          <div class="video-title">
                            <?php echo htmlspecialchars($row['title']); ?>
                          </div>
                          <div class="video-info">
                            by: <?php echo htmlspecialchars($row['username']); ?> /
                            <?php echo htmlspecialchars($row['vidlength']); ?> mins /
                            <?php echo htmlspecialchars($row['views']); ?> views
                          </div>
                        </div>
                      </a>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <p>No videos found. Maybe try <a href="index.php">refreshing</a> or searching?</p>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
  </table>
  <table class="UpdatesSect">
    <!-- Footer -->
    <tfoot>
      <tr>
        <td>
          <p class="footerText">&copy; somebody 2024. All rights reserved.</p>
        </td>
      </tr>
    </tfoot>
  </table>
</body>

</html>