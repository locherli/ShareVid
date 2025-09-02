# How to host a *shareVid* site
NOTE: This tutorial was originally designed for server computers running Linux and are being hosted via Apache. Depending on what you will use to host shareVid, you may need to do different things to get it to work.

Preview of this project: 

![display1](https://raw.githubusercontent.com/locherli/ShareVid/refs/heads/master/display_1.jpeg)
![display2](https://raw.githubusercontent.com/locherli/ShareVid/refs/heads/master/display_2.jpeg)

## Making your SQL tables.
First, create an SQL database, you can call it whatever you want. Now add these 2 tables to it, `users`, `comments`, `likes`, and `videos`, as well as their respective columns. Here is the cheatsheet for you:
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

You'll also need to replace the placeholder fields in `db.php` to match the database's information. Once that is done, try going to the page and registering an account. If you can successfully access the main page and be logged in, then you are nearly ready to start hosting your own shareVid! If you can't, or can only access one part with little to no problems, check on both your SQL database as well as `db.php`.
