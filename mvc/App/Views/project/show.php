<?php ob_start(); ?>
<h2><?=htmlspecialchars($proj['name'])?></h2>
<p>โค้ด: <?=htmlspecialchars($proj['code'])?></p>
<p>เป้าหมาย: <?=number_format($proj['goal_amount'],2)?> | ได้แล้ว: <?=number_format($sum,2)?></p>
<div class="progress"><div class="bar" style="width: <?=$progress?>%"></div></div>
<p>ความคืบหน้า: <?=$progress?>%</p>
<p>หมดเขต: <?=htmlspecialchars($proj['deadline'])?></p>

<h3>ระดับรางวัล</h3>
<ul>
<?php foreach($tiers as $t): ?>
  <li>
    <b><?=htmlspecialchars($t['name'])?></b>
    — ขั้นต่ำ <?=number_format($t['min_amount'],2)?>
    — โควตา <?=$t['quota']?>

    <?php if(!empty($_SESSION['user'])): ?>
      <?php if((int)$t['quota'] > 0): ?>
        <!-- ปุ่มสนับสนุนทันทีเมื่อล็อกอินแล้ว -->
        <form method="post" action="?r=pledge" style="display:inline-block; margin-left:10px">
          <input type="hidden" name="project_id" value="<?=$proj['id']?>">
          <input type="hidden" name="reward_tier_id" value="<?=$t['id']?>">
          <input name="amount" type="number" step="0.01"
                 min="<?=htmlspecialchars($t['min_amount'])?>"
                 value="<?=htmlspecialchars($t['min_amount'])?>"
                 style="width:120px">
          <button type="submit">สนับสนุน</button>
        </form>
      <?php else: ?>
        <span style="color:#999; margin-left:10px">โควตาหมด</span>
      <?php endif; ?>
    <?php else: ?>
      <!-- ยังไม่ล็อกอิน: ส่งไป login พร้อม next + params -->
      <a href="?r=login&next=continue_pledge&project_id=<?=$proj['id']?>&reward_tier_id=<?=$t['id']?>&amount=<?=$t['min_amount']?>"
         style="margin-left:10px">เข้าสู่ระบบเพื่อสนับสนุน</a>
    <?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>

<hr>

<?php if(!empty($_SESSION['user'])): ?>
  <h3>สนับสนุนแบบไม่รับรางวัล</h3>
  <form method="post" action="?r=pledge">
    <input type="hidden" name="project_id" value="<?=$proj['id']?>">
    <label>จำนวนเงิน:
      <input name="amount" type="number" step="0.01" min="1" value="100">
    </label>
    <input type="hidden" name="reward_tier_id" value="">
    <button type="submit">สนับสนุน</button>
  </form>
<?php else: ?>
  <!-- ไม่รับรางวัล: default 100 -->
  <p><a href="?r=login&next=continue_pledge&project_id=<?=$proj['id']?>&amount=100">เข้าสู่ระบบ</a> เพื่อสนับสนุน</p>
<?php endif; ?>

<?php $content=ob_get_clean(); include __DIR__."/../layout.php"; ?>
