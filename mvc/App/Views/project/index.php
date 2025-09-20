<?php ob_start(); ?>
<?php
// หน้ารายการโครงการ — แสดงฟิลเตอร์และลิสต์โปรเจกต์
// ค่าที่คาดจาก controller: $projects, $categories
?>
<h2>โครงการทั้งหมด</h2>

<form method="get" action="">
  <!-- route ปัจจุบัน (โฮม) -->
  <input type="hidden" name="r" value="">
  <!-- ช่องค้นหา -->
  <input name="q" placeholder="ค้นหา" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  <!-- เลือกหมวดหมู่ -->
  <select name="category">
    <option value="">ทุกหมวดหมู่</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= $c['id'] ?>" <?= (($_GET['category'] ?? '') == $c['id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($c['name']) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <!-- การเรียงลำดับ -->
  <select name="sort">
    <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>ใหม่สุด</option>
    <option value="ending_soon" <?= ($_GET['sort'] ?? '') === 'ending_soon' ? 'selected' : '' ?>>ใกล้หมดเวลา</option>
    <option value="most_funded" <?= ($_GET['sort'] ?? '') === 'most_funded' ? 'selected' : '' ?>>ระดมได้มากสุด</option>
  </select>
  <button>ไป</button>
</form>

<ul>
  <?php foreach ($projects as $p): ?>
    <li>
      <!-- ชื่อโปรเจกต์ -->
      <a href="?r=project&id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
      — หมวด: <?= htmlspecialchars($p['category_name']) ?>
      — เป้า <?= number_format($p['goal_amount'], 2) ?>
      — ได้แล้ว <?= number_format($p['raised_sum'], 2) ?>
      — หมดเขต: <?= htmlspecialchars($p['deadline']) ?>
    </li>
  <?php endforeach; ?>
</ul>
<?php $content = ob_get_clean();
include __DIR__ . "/../layout.php"; ?>