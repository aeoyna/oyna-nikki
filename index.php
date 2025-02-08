<?php
// index.php - メインページ

$dsn = 'mysql:host=localhost;dbname=oyna_0;charset=utf8';
$username = 'oyna_0';
$password = '8pzvjU00';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

function calculateDays($base_date_str = '2021-01-13') {
    $base_date = new DateTime($base_date_str);
    $current_date = new DateTime();
    $interval = $base_date->diff($current_date);
    return $interval->days;
}

function calculateDateFromDays($base_date_str = '2021-01-13', $days = 0) {
    $base_date = new DateTime($base_date_str);
    $calculated_date = $base_date->add(new DateInterval("P{$days}D"));
    return [
        'year' => $calculated_date->format('Y'),
        'date' => $calculated_date->format('Y-m-d'),
        'weekday' => $calculated_date->format('l')
    ];
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);

    $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';

    if (isset($_POST["wri"])) {
        $content = $_POST["content"];
        $days = isset($_POST["days"]) ? $_POST["days"] : null;

        if (empty($days)) {
            $days = calculateDays();
        }

        if (isset($_POST["edit_id"])) {
            $sql = "UPDATE blog SET days = :days, content = :content, post_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':days', $days);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':id', $_POST["edit_id"]);
        } else {
            $sql = "INSERT INTO blog (days, content, post_at) VALUES (:days, :content, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':days', $days);
            $stmt->bindParam(':content', $content);
        }

        $stmt->execute();
        header("Location: index.php");
        exit;
    }

    $newOrder = $order === 'ASC' ? 'desc' : 'asc';
    $orderLabel = $order === 'ASC' ? '小さい順' : '大きい順';
    echo "<a href='index.php?order=$newOrder' style='margin-bottom: 20px; display: inline-block;'>$orderLabelに並び替え</a>";

    $calculatedDays = calculateDays();
    $currentDateInfo = calculateDateFromDays('2021-01-13', $calculatedDays);
    echo "<h1 style='text-align: center;'>星霜拾遺</h1>";
    echo "<h2>今日は $calculatedDays 日 (" . $currentDateInfo['date'] . ", " . $currentDateInfo['weekday'] . ")</h2>";

    echo '<form action="index.php" method="get" style="display: inline-block; margin-left: 20px;">';
    echo '<input type="text" name="search" placeholder="検索キーワードを入力" style="padding: 5px;">';
    echo '<input type="submit" value="検索" style="padding: 5px;">';
    echo '</form>';

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

    include 'new.php';
} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
}
?>
