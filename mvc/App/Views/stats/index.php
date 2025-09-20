<?php
ob_start();

/**
 * View: สรุปสถิติ (Stats)
 *
 * ค่าที่คาดว่า controller จะส่งมา:
 *   - $success  : จำนวนการสนับสนุนที่สำเร็จ (int)
 *   - $rejected : จำนวนการสนับสนุนที่ถูกปฏิเสธ (int)
 *
 * วิธีการทำงาน:
 *   1. เริ่ม output buffering เพื่อเก็บเนื้อหาในตัวแปร $content
 *   2. แสดง HTML ของหน้านี้โดยใช้ค่าที่ controller ส่งมา
 *   3. กำหนด $content = ob_get_clean() แล้ว include layout ร่วมกับตัวแปรนี้
 *
 * หมายเหตุความปลอดภัย:
 *   - แม้ค่าจะเป็นตัวเลข ก็ทำการ cast/escape ด้วย htmlspecialchars เพื่อป้องกันการแสดงผลที่ไม่คาดคิด
 */

$success  = isset($success)  ? (int)$success  : 0;
$rejected = isset($rejected) ? (int)$rejected : 0;
?>
<h2>สรุปสถิติ</h2>

<!-- แสดงสถิติหลัก -->
<p>จำนวนการสนับสนุนที่สำเร็จ: <b><?= htmlspecialchars((string)$success) ?></b></p>
<p>จำนวนการสนับสนุนที่ถูกปฏิเสธ: <b><?= htmlspecialchars((string)$rejected) ?></b></p>

<?php
// เก็บเนื้อหาในตัวแปร $content แล้วเรียก layout หลัก (layout จะใช้ $content นี้)
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
