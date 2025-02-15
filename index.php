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
    return $interval->days;
}

// 並び順を取得
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

// エラーメッセージの初期化
$errorMessage = '';

// 投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST["content"] ?? '';
    $days = $_POST["days"] ?? null;

    if (empty($days)) {
        $days = calculateDays();
    }

    // 同じ days の投稿があるかチェック
    $checkSql = "SELECT COUNT(*) FROM blog WHERE days = :days";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':days', $days, PDO::PARAM_INT);
    $checkStmt->execute();
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        $errorMessage = "エラー: 同じ日付の投稿はできません。";
    } else {
        if (isset($_POST["edit_id"])) {
            // 編集処理
            $sql = "UPDATE blog SET days = :days, content = :content, post_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $_POST["edit_id"], PDO::PARAM_INT);
        } else {
            // 新規投稿処理
            $sql = "INSERT INTO blog (days, content, post_at) VALUES (:days, :content, NOW())";
            $stmt = $pdo->prepare($sql);
        }
        
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->execute();

        // リダイレクト
        header("Location: index.php");
        exit;
    }
}

// 投稿リストを取得
$sql = "SELECT * FROM blog ORDER BY days $order";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日記システム</title>
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border: none;
            background: transparent;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <h1>日記システム</h1>

    <button onclick="openModal()">新規投稿</button>

    <!-- モーダルオーバーレイ -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>

    <!-- 新規投稿フォーム（モーダル） -->
    <div id="postForm" class="modal">
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
            <br>
            <button type="submit" name="wri">投稿</button>
        </form>
        <button class="close-btn" onclick="closeModal()">&times;</button>
    </div>

    <h2>投稿一覧</h2>
    <table border="1">
        <tr>
            <th><a href="?order=asc">経過日数 ↑</a> | <a href="?order=desc">経過日数 ↓</a></th>
            <th>内容</th>
            <th>投稿日時</th>
        </tr>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td><?= htmlspecialchars($post['days'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?></td>
                <td><?= htmlspecialchars($post['post_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <script>
        function openModal() {
            document.getElementById("postForm").style.display = "block";
            document.getElementById("modalOverlay").style.display = "block";
        }

        function closeModal() {
            document.getElementById("postForm").style.display = "none";
            document.getElementById("modalOverlay").style.display = "none";
        }
    </script>

</body>
</html>
