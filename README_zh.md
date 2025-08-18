**其他语言版本: [English](README.md), [中文](README_zh.md).**

# 如何托管一个 *shareVid* 网站
**注意**：本教程最初是为运行 Linux 的服务器计算机设计，并通过 Apache 进行托管。根据您使用的托管方式，您可能需要执行不同的操作来使 shareVid 正常运行。

项目预览：https://vhost.locherli.my

## 1. 硬件要求
要开始运行一个基于 shareVid 的视频托管网站，您需要以下内容：
1. 具有以下最低配置的服务器计算机：
   - 存储：至少 1 个硬盘，总磁盘空间1TB左右。
   - 内存：8GB
   - 操作系统：任何专为服务器设计的操作系统（推荐使用任意 Linux 服务器发行版）。
   - 网络：以太网或（如果可能）无线网络。
2. 任意客户端计算机（用于控制服务器计算机）。
3. 用于以下用途的软件：
   - 网站服务器托管（如 Apache）。
   - PHP 支持。
   - SSH 或远程桌面（取决于客户端和服务器计算机的操作系统）。
   - 网站数据库（通常为 SQL）。
   - 任意文本编辑程序（如 Nano、Neovim 或 VSCode）。
   - 网站安全提供程序。
4. 一个注册的域名（如果您希望网站公开访问）。

## 2. 开始步骤
首先，确保您的服务器和客户端计算机都已开机，然后通过客户端 PC 以 root 身份登录到服务器计算机。确保您的客户端 PC 上有 `source.zip` 文件夹并解压。您需要将 zip 文件夹中的所有内容发送到服务器计算机，然后将这些内容移动到服务器的托管目录（如果使用 Apache 和 Linux，通常为 `/var/www/html/`）。将所有内容移动到服务器计算机后，确保可以通过本地主机 IP（任何以 192.168.0.*xx* 开头的 IP 地址）访问它。

## 3. 创建 SQL 表
首先，创建一个 SQL 数据库，您可以随意命名。然后在数据库中添加以下四个表：`users`、`comments`、`likes` 和 `videos`，以及它们各自的列。以下是供您参考的 SQL 代码：
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    trn_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    backgroundpath VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    profilepicturepath VARCHAR(255)
);
CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    filepath VARCHAR(255) NOT NULL,
    thumbnailpath VARCHAR(255) NOT NULL,
    creationdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    views INT DEFAULT 0,
    vidlength INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('like', 'dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (video_id) REFERENCES videos(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_like (video_id, user_id)
);
```

您还需要将 `db.php` 文件中的占位符字段替换为与数据库信息匹配的内容。完成后，尝试访问页面并注册一个账户。如果您能够成功访问主页并登录，那么您几乎已经准备好开始托管自己的 shareVid 网站！如果无法访问，或者只能访问部分内容且问题较少，请检查您的 SQL 数据库以及 `db.php` 文件。
