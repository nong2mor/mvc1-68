<?php
ob_start();

// เก็บพารามิเตอร์จาก query ที่ต้องการส่งต่อ (สำหรับ flow continue_pledge)
$keep = [];
foreach (['next', 'project_id', 'reward_tier_id', 'amount'] as $k) {
  if (isset($_GET[$k])) $keep[] = $k . '=' . urlencode((string)$_GET[$k]);
}
$qs = $keep ? '&' . implode('&', $keep) : '';
?>
<h2>เข้าสู่ระบบ</h2>

<!-- ฟอร์มล็อกอิน ส่งไปยัง AuthController::login พร้อมพารามิเตอร์ที่เก็บไว้ -->
<form method="post" action="?r=login<?= $qs ?>">
  <!-- ชื่อผู้ใช้ -->
  <input name="username" placeholder="username">
  <!-- รหัสผ่าน -->
  <input name="password" type="password" placeholder="password">
  <button type="submit">Login</button>
</form>

<?php
// ส่งเนื้อหาไปยัง layout หลัก
$content = ob_get_clean();
include __DIR__ . "/../layout.php";
?>