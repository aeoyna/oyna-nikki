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

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日記投稿</title>
    <style>
        /* モーダルのスタイル */
        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
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

    <!-- モーダルを開くボタン -->
    <button onclick="openModal()">新規投稿</button>

    <!-- モーダルの背景 -->
    <div id="modalOverlay" class="modal-overlay" onclick="closeModal()"></div>

    <!-- 新規投稿フォーム (モーダル) -->
    <div id="postForm" class="modal">
        <h2>新規投稿</h2>
        <p id="errorMessage" style="color: red;">
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <form method="POST" action="">
            <label for="days">経過日数:</label>
            <input type="number" name="days" id="days" value="<?= calculateDays() ?>" required>
            <br><br>
            <label for="content">内容:</label>
            <textarea name="content" id="content" rows="4" required></textarea>
            <br><br>
            <button type="submit" name="wri">投稿</button>
        </form>
        <button class="close-btn" onclick="closeModal()">&times;</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function openModal() {
                document.getElementById('postForm').style.display = 'block';
                document.getElementById('modalOverlay').style.display = 'block';
            }

            function closeModal() {
                document.getElementById('postForm').style.display = 'none';
                document.getElementById('modalOverlay').style.display = 'none';
            }

            // グローバルスコープで関数を使えるようにする
            window.openModal = openModal;
            window.closeModal = closeModal;
        });
    </script>

</body>
</html>
