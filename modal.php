<?php
// modal.php

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

    // リクエストされたIDのデータを取得
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id']; // IDを整数として処理
        $sql = "SELECT id, content, days, post_at FROM blog WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $post = $stmt->fetch();

        if ($post) {
            ?>
            <div class="modal" style="background: white; padding: 20px; margin: 50px auto; width: 1000%; height: 500px; position: relative; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 8px;">
                <p><strong><?php echo htmlspecialchars($post['days'], ENT_QUOTES, 'UTF-8'); ?> 日</strong></p>
                <form action="index.php" method="post" style="margin-bottom: 20px;">
　　　　　　　　　　<!-- days をテキスト入力として変更 -->
                    <div>日数を変更</div>
                    <input type="text" name="days" value="<?php echo htmlspecialchars($post['days'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; padding: 10px; box-sizing: border-box;">
                    <input type="hidden" name="edit_id" value="<?php echo $post['id']; ?>">
                    <div>本文</div>
                    <textarea name="content" rows="6" cols="50" style="width: 100%; font-size: 1rem; padding: 10px; box-sizing: border-box;"><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <br>
                    <input type="submit" name="wri" value="更新" style="margin-top: 10px; padding: 10px 20px; border: none; background: #28a745; color: white; border-radius: 4px; cursor: pointer;">
                    <span style="margin-left: 10px;"><strong>投稿日時:</strong> <?php echo htmlspecialchars($post['post_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                </form>
                <button onclick="closeModal()" style="position: absolute; top: 10px; right: 10px; border: none; background: transparent; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <script>
                function closeModal() {
                    // モーダルを閉じる
                    document.querySelector('.modal').style.display = 'none';
                }
            </script>
            <style>
                /* レスポンシブスタイル */
                @media screen and (max-width: 768px) {
                    .modal {
                        width: 90%;
                        margin: 20px auto;
                        padding: 15px;
                        font-size: 0.9rem;
                    }

                    textarea {
                        font-size: 0.9rem;
                        padding: 8px;
                    }

                    button, input[type="submit"] {
                        padding: 8px 16px;
                        font-size: 0.9rem;
                    }
                }
            </style>
            <?php
        } else {
            echo "<p>データが見つかりません。</p>";
        }
    } else {
        echo "<p>IDが指定されていません。</p>";
    }
} catch (PDOException $e) {
    echo "データベースエラー: " . $e->getMessage();
}
?>
