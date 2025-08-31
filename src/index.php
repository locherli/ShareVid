<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php');

$currentDateTime = date('Y-m-d H:i:s');

$page_limit = 16;
$current_page = !empty($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($current_page - 1) * $page_limit;

// 获取总记录数用于分页计算
$totalQuery = "SELECT COUNT(*) as total FROM videos";
$totalResult = $con->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalVideos = $totalRow['total'];
$totalPages = ceil($totalVideos / $page_limit);

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
    LIMIT $page_limit OFFSET $offset
";

$result = $con->query($videoQuery);
if (!$result) {
  die('Query data failed: ' . mysqli_error($con));
}

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
    LIMIT $page_limit OFFSET $offset
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
    LIMIT $page_limit OFFSET $offset
";

$RandomVidResult = $con->query($randomVideoQuery);

?>

<!DOCTYPE html>
<html>

<head>
  <title>vid -> Home</title>
  <!-- Styles and Favicon management-->
  <link rel="stylesheet" href="styles.css">
  <link rel="icon" type="image/x-icon" href="images/logos/favicon.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- 不要问我为什么不把这个style放在styles.css中，因为某些不可知的原因，这样做它不生效。 -->
  <style>
    /* Tab styling */
    .tabs {
      display: flex;
      justify-content: flex-start;
      background: rgba(30, 30, 30, 0.95);
      border-bottom: 1px solid #333333;
      margin: 20px auto;
      width: 100%;
      max-width: 1200px;
      border-radius: 8px 8px 0 0;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .tablinks {
      background: none;
      border: none;
      outline: none;
      padding: 14px 20px;
      color: #cccccc;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      border-bottom: 2px solid transparent;
    }

    .tablinks:hover {
      background: rgba(79, 195, 247, 0.1);
      color: #4fc3f7;
    }

    .tablinks.active {
      color: #4fc3f7;
      border-bottom: 2px solid #4fc3f7;
      background: rgba(79, 195, 247, 0.15);
    }

    .tabcontent {
      display: none;
      background: #1e1e1e;
      border: 1px solid #333333;
      border-top: none;
      margin: 0 auto;
      width: 100%;
      max-width: 1200px;
      border-radius: 0 0 8px 8px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
      padding: 20px;
    }

    .tabcontent.active {
      display: block;
    }

    /* Responsive design for tabs */
    @media (max-width: 768px) {
      .tabs {
        flex-wrap: wrap;
        justify-content: center;
      }

      .tablinks {
        padding: 12px 15px;
        font-size: 14px;
      }

      .tabcontent {
        max-width: 95%;
        padding: 15px;
      }
    }

    @media (max-width: 480px) {
      .tablinks {
        padding: 10px 12px;
        font-size: 13px;
        flex-grow: 1;
        text-align: center;
      }
    }

    .pagination {
      display: flex;
      justify-content: center;
    }
  </style>
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

          <!-- Tab navigation -->
          <div class="tabs">
            <button class="tablinks active" onclick="openTab(event, 'recommended')">Recommended For You</button>
            <button class="tablinks" onclick="openTab(event, 'latest')">Newest Videos</button>
            <button class="tablinks" onclick="openTab(event, 'top')">Top Videos this Week</button>
          </div>

          <!-- Recommended Tab Content -->
          <div id="recommended" class="tabcontent" style="display: block;">
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
                      <?php while ($row = $RandomVidResult->fetch_assoc()): ?>
                        <a href="video.php?id=<?php echo $row['id']; ?>" class="video-container-link">
                          <div class="video-container">
                            <div class="video-thumbnail">
                              <img class="video-thumbnail-image"
                                src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>" alt="Thumbnail">
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
          </div>

          <!-- Latest Tab Content -->
          <div id="latest" class="tabcontent">
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
                              <img class="video-thumbnail-image"
                                src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>" alt="Thumbnail">
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
          </div>

          <!-- Top Tab Content -->
          <div id="top" class="tabcontent">
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
                              <img class="video-thumbnail-image"
                                src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>" alt="Thumbnail">
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
          </div>

        </td>
      </tr>
    </tbody>
  </table>

  <!-- 选页 -->
  <div class="pagination">
    <?php if (($current_page - 1) > 0): ?>
      <a href="index.php?page=<?php echo ($current_page - 1); ?>">Previous Page</a>
    <?php endif; ?>

    <span>Page <?php echo $current_page; ?> of <?php echo $totalPages; ?></span>

    <?php if ($current_page < $totalPages): ?>
      <a href="index.php?page=<?php echo ($current_page + 1); ?>">Next Page</a>
    <?php endif; ?>
  </div>

  <table class="UpdatesSect">
    <!-- Footer -->
    <tfoot>
      <tr>
        <td>
          <p class="footerText">&copy;2024. All rights reserved.</p>
        </td>
      </tr>
    </tfoot>
  </table>

  <script>
    function openTab(evt, tabName) {
      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tabcontent");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
      document.getElementById(tabName).style.display = "block";
      evt.currentTarget.className += " active";
    }


  </script>
</body>

</html>