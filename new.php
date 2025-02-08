<?php
// new.php - 新規投稿用のモーダル
?>
<div id="new-post-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: white; padding: 20px; margin: 10% auto; width: 50%; border-radius: 10px;">
        <h2>新規投稿</h2>
        <form action="index.php" method="post">
            <div>本文</div>
            <textarea name="content" rows="4" cols="50"></textarea>
            <br>
            <input type="submit" name="wri" value="保存">
            <button type="button" onclick="closeNewPostModal()">キャンセル</button>
        </form>
    </div>
</div>

<script>
function openNewPostModal() {
    document.getElementById('new-post-modal').style.display = 'block';
}

function closeNewPostModal() {
    document.getElementById('new-post-modal').style.display = 'none';
}
</script>

<button onclick="openNewPostModal()" style="position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; font-size: 24px; border-radius: 50%; background: #007bff; color: white; border: none;">+</button>
