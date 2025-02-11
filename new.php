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
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

// daysを計算する関数
function calculateDays($base_date_str = '2021-01-13') {
    $base_date = new DateTime($base_date_str);
    $current_date = new DateTime();
    $interval = $base_date->diff($current_date);
    return $interval->days; // 日数を返す
}

// 並び順を取得
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// エラーメッセージの初期化
$errorMessage = '';

// 投稿があった場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST["content"] ?? '';
    $days = $_POST["days"] ?? null;

    if (empty($days)) {
        // days が空の場合は基準日からの日数を設定
        $days = calculateDays();
    }

    // 同じdaysが存在するかチェック
    $checkSql = "SELECT COUNT(*) FROM blog WHERE days = :days";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':days', $days, PDO::PARAM_INT);
    $checkStmt->execute();
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        $errorMessage = "エラー: 同じ日付の投稿はできません。";
    } else {
        if (isset($_POST["edit_id"])) {
            // 編集の場合
            $sql = "UPDATE blog SET days = :days, content = :content, post_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $_POST["edit_id"], PDO::PARAM_INT);
        } else {
            // 新規投稿の場合
            $sql = "INSERT INTO blog (days, content, post_at) VALUES (:days, :content, NOW())";
            $stmt = $pdo->prepare($sql);
        }
        
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->execute();

        // リダイレクト
        header("Location: nikki.php");
        exit;
    }
}
?>

<!-- 新規投稿フォーム -->
<div id="postForm" class="modal" style="background:white; padding:20px; border-radius:5px; position:relative;">

    <h2>新規投稿</h2>
    <?php if ($errorMessage): ?>
        <p style="color: red;"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="days">経過日数:</label>
        <input type="number" name="days" id="days" value="<?= calculateDays() ?>" required>
        <br><br>
        <label for="content">内容:</label>
        <textarea name="content" id="content" rows="4" required></textarea>
        <button type="submit" name="wri">投稿</button>
    </form>
    <button onclick="closeModal()" style="position: absolute; top: 10px; right: 10px; border: none; background: transparent; font-size: 1.5rem; cursor: pointer;">&times;</button>
</div>
<script>
    function closeModal() {
        document.querySelector('#postForm').style.display = 'none';
    }
</script>
