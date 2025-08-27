<?php
$ok = flash('success');
$err = flash('error');
$info = flash('info');
if ($ok || $err || $info):
?>
<div style="margin:.75rem 0; display:grid; gap:.5rem">
  <?php if ($ok): ?>
    <div style="padding:.5rem 1rem;border:1px solid #c7e5c5;background:#eaf7e9;color:#155724"><?= $ok ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div style="padding:.5rem 1rem;border:1px solid #f1c0c0;background:#fdeaea;color:#721c24"><?= $err ?></div>
  <?php endif; ?>
  <?php if ($info): ?>
    <div style="padding:.5rem 1rem;border:1px solid #cbe3ff;background:#eef6ff;color:#0c5460"><?= $info ?></div>
  <?php endif; ?>
</div>
<?php endif; ?>
