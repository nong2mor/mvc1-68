<?php

namespace App\Controllers;

use App\Models\Project;
use App\Models\RewardTier;
use App\Models\Pledge;

/**
 * ProjectController
 *
 * ควบคุมการแสดงรายการโปรเจกต์ (index) และหน้ารายละเอียดโปรเจกต์ (show)
 *
 * หมายเหตุสรุป:
 * - Controller นี้เป็น thin controller: ดึงข้อมูลจาก models แล้วส่งไปที่ view
 * - ไม่ทำการเปลี่ยนแปลงข้อมูล (read-only) — การสร้าง/แก้ไข จะอยู่ใน controller อื่น
 * - ควรแน่ใจว่า models ที่เรียกมีการ sanitize/validate input ภายในตัว (เช่น cast id, limit)
 */
class ProjectController
{
    /**
     * index
     *
     * แสดงหน้ารายการโปรเจกต์ (search / filter / sort รองรับผ่าน query params)
     *
     * Query parameters ที่รองรับ:
     * - q        : string, คำค้นหา (optional)
     * - category : id ของ category เพื่อกรองรายการ (optional)
     * - sort     : วิธีการเรียง (เช่น 'newest', 'popular' ฯลฯ). ค่า default = 'newest'
     *
     * การทำงาน:
     * 1. อ่านค่า query params จาก $_GET
     * 2. เรียก Model Project->list($q,$cat,$sort) เพื่อดึงข้อมูล (model ควรรับผิดชอบ SQL และ pagination)
     * 3. ดึงหมวดหมู่ทั้งหมดจาก Category model เพื่อให้ view สามารถแสดงตัวกรองได้
     * 4. โหลด view (App/Views/project/index.php) โดยใช้ output buffering แล้วคืนค่า HTML เป็น string
     *
     * คืนค่า:
     * - string HTML (จาก view) — pattern นี้ช่วยให้ router/runner สามารถ echo ผลลัพธ์ได้
     *
     * ความปลอดภัย / ข้อสังเกต:
     * - ไม่ควร trust ค่า $_GET โดยตรงใน SQL: model ต้อง sanitize/prepare คำสั่ง SQL
     * - หากต้องการ pagination ให้ขยายพารามิเตอร์และส่งไปยัง model
     */
    public function index()
    {
        // อ่าน parameter จาก query string (ใช้ null coalescing เพื่อกัน undefined index)
        $q = $_GET['q'] ?? null;
        $cat = $_GET['category'] ?? null;
        $sort = $_GET['sort'] ?? 'newest';

        // ดึงรายการโปรเจกต์จาก model — model จะจัดการ query, search, sort
        $projects = (new Project())->list($q, $cat, $sort);

        // ดึงหมวดหมู่ทั้งหมดเพื่อให้ view แสดง filter ได้ (Category model อยู่ใน App\Models)
        $categories = (new \App\Models\Category())->all();

        // โหลด view: ใช้ output buffering pattern ของโปรเจกต์
        ob_start();
        include __DIR__ . "/../Views/project/index.php";
        return ob_get_clean();
    }

    /**
     * show
     *
     * แสดงหน้ารายละเอียดโปรเจกต์เดียว (project detail)
     *
     * Query parameters:
     * - id : integer id ของโปรเจกต์ (required) — ถ้าไม่มีหรือไม่พบ จะส่ง 404
     *
     * การทำงาน:
     * 1. อ่าน id จาก $_GET และ cast เป็น int
     * 2. ดึงข้อมูลโปรเจกต์จาก Project->find($id)
     * 3. ถ้าไม่พบโปรเจกต์: ส่ง HTTP 404 แล้วคืนข้อความสั้นๆ
     * 4. ดึง reward tiers ที่เกี่ยวข้องจาก RewardTier->byProject($id)
     * 5. คำนวณยอดรวมที่ได้รับสำเร็จจาก Pledge->sumSuccessByProject($id)
     * 6. คำนวณเปอร์เซนต์ความคืบหน้าของการระดมทุน (progress)
     * 7. ส่งข้อมูลทั้งหมดไปยัง view (App/Views/project/show.php)
     *
     * คืนค่า:
     * - ถ้าพบโปรเจกต์: string HTML ของ view
     * - ถ้าไม่พบ: ตั้ง HTTP 404 และคืนข้อความ "Project not found"
     *
     * ความปลอดภัย / ข้อสังเกต:
     * - Casting id เป็น int ป้องกัน SQL injection ในกรณีที่ model ใช้ id โดยตรง
     * - Model ควรใช้ prepared statements สำหรับการเรียก DB
     * - คำนวณ progress ให้ปลอดภัยเมื่อ goal_amount = 0 (หลีกเลี่ยงการหารด้วยศูนย์)
     */
    public function show()
    {
        // อ่าน id จาก query string และ cast เป็น integer ให้ชัดเจน
        $id = (int)($_GET['id'] ?? 0);

        // ดึงข้อมูลโปรเจกต์จาก model
        $proj = (new Project())->find($id);

        // ถ้าไม่พบโปรเจกต์: ส่ง 404
        if (!$proj) {
            http_response_code(404);
            return "Project not found";
        }

        // ดึงระดับรางวัลที่เป็นของโปรเจกต์นี้
        $tiers = (new RewardTier())->byProject($id);

        // ดึงผลรวมยอดที่สำเร็จของโปรเจกต์ (model คืนค่าเป็นจำนวนตัวเลข)
        $sum = (new Pledge())->sumSuccessByProject($id);

        // คำนวณ progress: ตรวจ goal_amount ก่อนเพื่อหลีกเลี่ยงหารด้วยศูนย์
        $progress = 0;
        if (!empty($proj['goal_amount']) && is_numeric($proj['goal_amount']) && $proj['goal_amount'] > 0) {
            $progress = min(100, round($sum * 100 / $proj['goal_amount']));
        }

        // โหลด view และคืน HTML
        ob_start();
        include __DIR__ . "/../Views/project/show.php";
        return ob_get_clean();
    }
}
