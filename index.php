
<?php
// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=oyna_0;charset=utf8';
$username = 'oyna_0';
$password = '8pzvjU00';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// daysを計算する関数
function calculateDays($base_date_str = '2021-01-13') {
    $base_date = new DateTime($base_date_str);
    $current_date = new DateTime();
    $interval = $base_date->diff($current_date);
    return $interval->days; // 日数を返す
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);

    // 並び順を取得
    $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

    // エラーメッセージの初期化
    $errorMessage = '';

    // 投稿があった場合の処理
    if (isset($_POST["wri"])) {
        $content = $_POST["content"];
        $days = isset($_POST["days"]) ? $_POST["days"] : null;

        if (empty($days)) {
            // days が空の場合は基準日からの日数を設定
            $days = calculateDays(); // 関数で計算
        }

        // 同じdaysが存在するかチェック
        if (isset($_POST["edit_id"])) {
            $edit_id = $_POST["edit_id"];
            $checkSql = "SELECT COUNT(*) FROM blog WHERE days = :days AND id != :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':days', $days);
            $checkStmt->bindParam(':id', $edit_id);
        } else {
            $checkSql = "SELECT COUNT(*) FROM blog WHERE days = :days";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':days', $days);
        }
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

        if ($exists > 0) {
            $errorMessage = "エラー: 同じ日付の投稿はできません。";
        } else {
            if (isset($_POST["edit_id"])) {
                // 編集の場合
                $sql = "UPDATE blog SET days = :days, content = :content, post_at = NOW() WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':days', $days);
                $stmt->bindParam(':content', $content);
                $stmt->bindParam(':id', $_POST["edit_id"]);
            } else {
                // 新規投稿の場合
                $sql = "INSERT INTO blog (days, content, post_at) VALUES (:days, :content, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':days', $days);
                $stmt->bindParam(':content', $content);
            }

            $stmt->execute();

            // データの追加または更新に成功したらリダイレクト
            header("Location: index.php");
            exit; // リダイレクト後はスクリプトの実行を停止
        }
    }

    // 並び順変更のリンク
    $newOrder = $order === 'ASC' ? 'desc' : 'asc';
    $orderLabel = $order === 'ASC' ? '小さい順' : '大きい順';
    echo "<a href='index.php?order=$newOrder' style='margin-bottom: 20px; display: inline-block;'>$orderLabelに並び替え</a>";

    // daysを計算して表示
    $calculatedDays = calculateDays();
    echo "<h2 style='display: inline;'>今日は $calculatedDays 日</h2>";

    // 検索フォームを右に配置
    echo '<form action="index.php" method="get" style="display: inline-block; margin-left: 20px;">';
    echo '<input type="text" name="search" placeholder="検索キーワードを入力" style="padding: 5px;">';
    echo '<input type="submit" value="検索" style="padding: 5px;">';
    echo '</form>';

    // エラーメッセージを表示
    if (!empty($errorMessage)) {
        echo "<p style='color: red;'>$errorMessage</p>";
    }

    // 検索キーワードの処理
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    if (!empty($search)) {
        $sql = "SELECT id, days, content, post_at FROM blog WHERE content LIKE :search ORDER BY days $order";
        $stmt = $pdo->prepare($sql);
        $searchKeyword = "%$search%";
        $stmt->bindParam(':search', $searchKeyword);
        $stmt->execute();
    } else {
        $sql = "SELECT id, days, content, post_at FROM blog ORDER BY days $order";
        $stmt = $pdo->query($sql);
    }

    // データ表示
    foreach ($stmt as $row) {
        echo "<div class='post' onclick='openModal(" . $row["id"] . ")' style='cursor: pointer;'>";
        echo "<p><strong>" . htmlspecialchars($row["days"], ENT_QUOTES, 'UTF-8') . " 日</strong></p>";
        echo "<p>" . htmlspecialchars($row["content"], ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<hr>";
        echo "</div>";
    }

    // 新規投稿用フォーム
    ?>
    
    <button class="floating-btn" onclick="openNewPostModal()">+</button>
    
    <div id="new-post-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div id="modal-content" style="position:relative; background:white; margin:auto; padding:20px; width:80%; top:10%; border-radius:10px;">
            <!-- モーダル内の閉じるボタン -->
            <span onclick="closeNewPostModal()" style="position:absolute; top:10px; right:20px; cursor:pointer;">&times;</span>
        </div>
    </div>
    
    <script>
    function openNewPostModal() {
        const modalContainer = document.getElementById('new-post-modal');
        fetch('new.php')
            .then(response => response.text())
            .then(html => {
                document.getElementById('modal-content').innerHTML = '<span onclick="closeNewPostModal()" style="position:absolute; top:10px; right:20px; cursor:pointer;">&times;</span>' + html;
                modalContainer.style.display = 'block';
            })
            .catch(error => console.error('エラー:', error));
    }
    
    function closeNewPostModal() {
        const modalContainer = document.getElementById('new-post-modal');
        modalContainer.style.display = 'none';
    }
    
    // モーダル外をクリックしたときにモーダルを閉じる
    window.onclick = function(event) {
        const modalContainer = document.getElementById('new-post-modal');
        if (event.target == modalContainer) {
            closeNewPostModal();
        }
    }
    </script>
    
    <style>
    .floating-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        background-color: #007bff;
        color: white;
        font-size: 24px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        border: none;
        outline: none;
        transition: 0.3s;
    }
    
    .floating-btn:hover {
        background-color: #0056b3;
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
    }
    </style>

    <!-- モーダル用コンテナ -->
    <div id="modal-container" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:
