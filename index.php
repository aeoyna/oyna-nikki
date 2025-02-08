<?php
// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=oyna_0;charset=utf8';
$username = 'oyna_0';
$password = '8pzvjU00';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// 基準日から経過日数を計算する関数
function calculateDays($base_date_str = '2021-01-13') {
    $base_date = new DateTime($base_date_str);
    $current_date = new DateTime();
    $interval = $base_date->diff($current_date);
    return $interval->days;
}

// 日付と曜日を計算する関数
function calculateDateFromDays($base_date_str = '2021-01-13', $days = 0) {
    $base_date = new DateTime($base_date_str);
    $calculated_date = $base_date->add(new DateInterval("P{$days}D")); // 日数を加算
    return [
        'year' => $calculated_date->format('Y'),       // 西暦
        'date' => $calculated_date->format('Y-m-d'),   // 日付
        'weekday' => $calculated_date->format('l')     // 曜日 (Monday, Tuesday...)
    ];
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
    $newOrder = $order === 'ASC' ? 'desc' : 'asc';
    $orderLabel = $order === 'ASC' ? '小さい順' : '大きい順';
    
    echo "<a href='index.php?order=$newOrder' style='margin-bottom: 20px; display: inline-block;'>$orderLabelに並び替え</a>";

    $calculatedDays = calculateDays();
    $currentDateInfo = calculateDateFromDays('2021-01-13', $calculatedDays);
    echo "<h1 style='text-align: center;'>星霜拾遺</h1>";
    echo "<h2>今日は $calculatedDays 日 (" . $currentDateInfo['date'] . ", " . $currentDateInfo['weekday'] . ")</h2>";

    echo '<button onclick="openNewPostModal()" style="padding: 10px; font-size: 16px;">＋</button>';

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

    foreach ($stmt as $row) {
        $dateInfo = calculateDateFromDays('2021-01-13', $row["days"]);
        echo "<div class='post' onclick='openModal(" . $row["id"] . ")' style='cursor: pointer;'>";
        echo "<p><strong>" . htmlspecialchars($row["days"], ENT_QUOTES, 'UTF-8') . " 日 (" . $dateInfo['date'] . ", " . $dateInfo['weekday'] . ")</strong></p>";
        echo "<p>" . nl2br(htmlspecialchars($row["content"], ENT_QUOTES, 'UTF-8')) . "</p>";
        echo "<hr>";
        echo "</div>";
    }
    ?>

    <!-- モーダル用コンテナ -->
    <div id="modal-container" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;"></div>

    <script>
    function openModal(id) {
        const modalContainer = document.getElementById('modal-container');
        fetch(`modal.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                modalContainer.innerHTML = html;
                modalContainer.style.display = 'block';
            })
            .catch(error => console.error('エラー:', error));
    }
    function openNewPostModal() {
        const modalContainer = document.getElementById('modal-container');
        fetch('new.php')
            .then(response => response.text())
            .then(html => {
                modalContainer.innerHTML = html;
                modalContainer.style.display = 'block';
            })
            .catch(error => console.error('エラー:', error));
    }
    function closeModal() {
        const modalContainer = document.getElementById('modal-container');
        modalContainer.style.display = 'none';
        modalContainer.innerHTML = '';
    }
    </script>
    <?php
} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
}
?>
