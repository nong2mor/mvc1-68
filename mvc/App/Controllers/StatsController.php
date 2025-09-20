<?php

namespace App\Controllers;

use App\Models\Pledge;

/**
 * StatsController
 *
 * แสดงสรุปสถิติการสนับสนุน (pledge)
 *
 * หน้าที่หลัก:
 * - ขอข้อมูลสถิติจาก Pledge::stats() (คาดว่าเป็น array ของแถวที่มี keys: status, cnt)
 * - คำนวณยอดรวมตามสถานะหลัก (success, rejected)
 * - โหลด view ที่จะแสดงผล
 *
 * ข้อสังเกตสำคัญ:
 * - Pledge::stats() อาจคืนค่าหลายแถว (เช่น ต่อ status ต่าง ๆ) ดังนั้นต้องรวมค่า (sum) ไม่ใช่แค่กำหนดทับ
 * - ตรวจสอบคีย์ในแต่ละแถว (status, cnt) และ cast ให้เป็นชนิดที่คาดหวัง (string/int)
 * - หากมีสถานะอื่น ๆ ในอนาคต ให้ขยาย logic ในการนับหรือส่งข้อมูลเพิ่มเติมไปยัง view
 */
class StatsController
{
    public function index()
    {
        // ดึงข้อมูลสถิติจาก model
        // คาดว่า $rows เป็น array รูปแบบ: [ ['status' => 'success', 'cnt' => 10], ['status'=>'rejected','cnt'=>2], ... ]
        $rows = (new Pledge())->stats();

        // กำหนดค่าเริ่มต้นเป็น 0 และใช้การรวม (sum) เผื่อ model คืนหลายแถวต่อสถานะ
        $success = 0;
        $rejected = 0;

        // วนแต่ละแถว มั่นใจว่า key มีอยู่และ cast ค่าเป็น int ก่อนนำมารวม
        foreach ($rows as $r) {
            $status = isset($r['status']) ? (string)$r['status'] : '';
            $cnt = isset($r['cnt']) ? (int)$r['cnt'] : 0;

            // รวมตามสถานะที่รู้จัก
            if ($status === 'success') {
                $success += $cnt;
            } elseif ($status === 'rejected') {
                $rejected += $cnt;
            } else {
                // หากมีสถานะอื่นที่ไม่คาดคิด ให้ข้ามไป (หรือขยายตามต้องการ)
                continue;
            }
        }

        // ส่งตัวแปรไปยัง view และคืน HTML (pattern ของโปรเจกต์ใช้ output buffering)
        ob_start();
        include __DIR__ . "/../Views/stats/index.php";
        return ob_get_clean();
    }
}
