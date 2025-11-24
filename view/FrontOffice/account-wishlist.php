<?php
// view/FrontOffice/account-wishlist.php
// PURE VIEW: uses $wishlist
$wishlist = $wishlist ?? [];
?>
<h2>Wishlist</h2>

<?php if (empty($wishlist)): ?>
  <p>Your wishlist is empty.</p>
<?php else: ?>
  <ul>
    <?php foreach ($wishlist as $w): ?>
      <li>
        <a href="<?php echo htmlspecialchars($w['url'] ?? '#'); ?>"><?php echo htmlspecialchars($w['title'] ?? 'Item'); ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<form method="post" action="/controller/add-wishlist.php">
  <div><label>Title</label><br><input name="title"></div>
  <div><label>URL</label><br><input name="url"></div>
  <div style="margin-top:12px;"><button type="submit">Add to wishlist</button></div>
</form>