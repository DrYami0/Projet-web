<?php
// view/FrontOffice/account-travelers.php
// PURE VIEW: uses $travelers (array)
$travelers = $travelers ?? [];
?>
<h2>Travelers</h2>

<?php if (empty($travelers)): ?>
  <p>No travelers yet.</p>
<?php else: ?>
  <ul>
    <?php foreach ($travelers as $t): ?>
      <li>
        <?php echo htmlspecialchars($t['name'] ?? ''); ?> —
        <?php echo htmlspecialchars($t['dob'] ?? ''); ?> —
        <?php echo htmlspecialchars($t['passport_number'] ?? ''); ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<h3>Add Traveler</h3>
<form method="post" action="/controller/add-traveler.php">
  <div><label>Name</label><br><input type="text" name="name" required></div>
  <div><label>Date of Birth</label><br><input type="date" name="dob"></div>
  <div><label>Passport number</label><br><input type="text" name="passport_number"></div>
  <div style="margin-top:12px;"><button type="submit">Add traveler</button></div>
</form>