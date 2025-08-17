<?php
session_start();
session_destroy();        // 销毁会话
header('Location: index.php');    // 修正：需要添加"Location:"前缀
exit();
?>