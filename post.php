<?php
// SQLiteデータベースに接続
try {
    $db = new PDO('sqlite:posts.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'DB接続エラー: ' . $e->getMessage();
    exit();
}

// テーブルが存在しない場合は作成
$db->exec('CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post TEXT NOT NULL,
    image_path TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

// 画像がアップロードされたらデータベースに保存
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
    $postText = $_POST['postText'];

    // アップロードされた画像の情報
    $file_name = $_FILES["image"]["name"];
    $file_tmp = $_FILES["image"]["tmp_name"];

    // 画像を保存するディレクトリ
    $upload_dir = 'uploads/';

    // 保存するファイルパス
    $upload_path = $upload_dir . $file_name;

    // 画像を指定したディレクトリに移動
    move_uploaded_file($file_tmp, $upload_path);

    // データベースに保存
    $stmt = $db->prepare('INSERT INTO posts (post, image_path) VALUES (:post, :image_path)');
    $stmt->bindParam(':post', $postText, PDO::PARAM_STR);
    $stmt->bindParam(':image_path', $upload_path, PDO::PARAM_STR);
    $stmt->execute();

    // アップロードされた画像を表示
    echo '<p>アップロードされた画像：</p>';
    echo '<img src="' . $upload_path . '" alt="アップロードされた画像">';
}

// 保存された投稿をデータベースから取得して表示
$result = $db->query('SELECT * FROM posts ORDER BY created_at DESC');
if ($result) {
    echo '<p>保存された投稿一覧：</p>';
    foreach ($result as $row) {
        echo '<p>' . $row['post'] . '</p>';
        echo '<img src="' . $row['image_path'] . '" alt="保存された画像">';
    }
}

?>

<!-- 画像アップロードフォーム -->
<form action="" method="post" enctype="multipart/form-data">
    <label for="postText">投稿文:</label>
    <input type="text" name="postText" id="postText">
    <br>
    <label for="image">画像を選択:</label>
    <input type="file" name="image" id="image" accept="image/*">
    <br>
    <button type="submit">投稿</button>
</form>

</body>
</html>
