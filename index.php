// index.php
session_start();
require_once 'config.php';
require_once 'database.php';
require_once 'BlogPost.php';

try {
    $database = new Database(__DIR__ . '/config.php');
    $blogPost = new BlogPost($database);

    // 処理
    $order = $_GET['order'] ?? 'DESC';
    $search = $_GET['search'] ?? '';
    $posts = $blogPost->getPosts($order, $search);

    // 出力処理（セキュアなHTMLエスケープ）
    function e($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
} catch (Exception $e) {
    // エラーログに記録し、安全なエラーメッセージを表示
    error_log($e->getMessage());
    die('システムエラーが発生しました');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>星霜拾遺</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>星霜拾遺</h1>
    
    <div class="controls">
        <a href="?order=<?= $order === 'ASC' ? 'desc' : 'asc' ?>">
            <?= $order === 'ASC' ? '大きい順' : '小さい順' ?>に並び替え
        </a>
        
        <form action="" method="get">
            <input type="text" name="search" placeholder="検索キーワード">
            <button type="submit">検索</button>
        </form>
    </div>

    <div class="posts">
        <?php foreach ($posts as $post): ?>
            <div class="post" data-id="<?= e($post['id']) ?>">
                <p class="days"><?= e($post['days']) ?> 日</p>
                <p class="content"><?= e($post['content']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
