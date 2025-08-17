<?php
session_start();
include('db.php');

// 获取查询关键字
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query)) {
    die("Please enter a search term.");
}

// 防止 SQL 注入，使用预处理语句
$stmt = $con->prepare("
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
        users ON videos.user_id = users.id 
    WHERE 
        videos.title LIKE ?
    ORDER BY 
        videos.creationdate DESC
");

$searchTerm = '%' . $query . '%';
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Results for "<?= htmlspecialchars($query) ?>"</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .video-item {
            margin-bottom: 20px;
        }

        .thumbnail {
            width: 120px;
            height: 90px;
            object-fit: cover;
        }
    </style>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <h1>Search Results for "<?= htmlspecialchars($query) ?>"</h1>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>


            <div class="video-item">
                <h3><a href="video.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h3>
                <img src="<?= htmlspecialchars($row['thumbnailpath']) ?>" alt="Thumbnail" class="thumbnail">
                <p>Uploader: <?= htmlspecialchars($row['username']) ?></p>
                <p>Views: <?= $row['views'] ?> | Length: <?= gmdate("i:s", $row['vidlength']) ?></p>
            </div>



            <table class="PineconiumTabNav">
                <tbody>
                    <tr>
                        <td>
                            <table class="TopStatusArea">
                                <thead>
                                    <tr>
                                        <div class="title-container">
                                            <img src="images/icon_newvideos.png" height="24px" width="24px">
                                            <h1 class="table_title">result Videos</h1>
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
                                                                <img class="video-thumbnail-image"
                                                                    src="<?php echo htmlspecialchars($row['thumbnailpath']); ?>"
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

        <?php endwhile; ?>
    <?php else: ?>
        <p>No videos found matching your search.</p>
    <?php endif; ?>

</body>

</html>