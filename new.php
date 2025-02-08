<?php
// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=oyna_0;charset=utf8';
$username = 'oyna_0';
$password = '8pzvjU00';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // フォームからのデータ取得
        $days = $_POST['days'] ?? 0;
        $content = $_POST['content'] ?? '';
        
        // データベースに新規投稿を挿入
        $sql = "INSERT INTO blog (days, content, post_at) VALUES (:days, :content, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
        
        // 投稿成功メッセージ
        echo "<p>新しい投稿が作成されました。</p>";
        echo "<script>setTimeout(() => { window.location.reload(); }, 1500);</script>"; // 1.5秒後にリロード
    }
} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
}
?>

<!-- 新規投稿フォーム -->
<div style="background:white; padding:20px; border-radius:5px;">
    <h2>新規投稿</h2>
    <form method="POST" action="">
        <label for="days">経過日数:</label>
        <input type="number" name="days" id="days" required>
        <br><br>
        <label for="content">内容:</label>
        <textarea name="content" id="content" rows="4" required></textarea>
        <br><br>
        <button type="submit">投稿する</button>
        <button type="button" onclick="closeModal()">キャンセル</button>
    </form>
</div>
