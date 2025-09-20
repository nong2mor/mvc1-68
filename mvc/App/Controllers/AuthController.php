<?php

namespace App\Controllers;

use App\Models\User;

/**
 * AuthController
 *
 * ควบคุมการแสดงหน้า login, การตรวจสอบข้อมูลผู้ใช้ และการ logout
 *
 * หมายเหตุสำคัญ:
 * - โค้ดปัจจุบันเปรียบเทียบรหัสผ่านแบบตรงตัว ($user['password'] === $p)
 *   ซึ่งหมายความว่ารหัสผ่านในฐานข้อมูลอาจถูกเก็บเป็น plain text
 *   หรือเป็นรูปแบบที่ไม่ได้ใช้ password_hash(). ในการใช้งานจริง
 *   ควรใช้ password_hash() เมื่อสร้างบัญชี และ password_verify() ในการตรวจสอบ
 * - ฟังก์ชันนี้เริ่ม session ด้วย session_start() เพื่อเรียกใช้งาน $_SESSION.
 * - มี flow พิเศษสำหรับ "continue_pledge" ที่เก็บข้อมูลชั่วคราวไว้ใน
 *   $_SESSION['pending_pledge'] เพื่อให้ผู้ใช้สามารถทำรายการ pledge ต่อหลัง login
 */
class AuthController
{
    /**
     * แสดงฟอร์มล็อกอิน
     * - ใช้ output buffering เพื่อคืน HTML เป็น string (controller pattern ของโปรเจกต์)
     */
    public function showLogin()
    {
        ob_start();
        include __DIR__ . "/../Views/auth/login.php";
        return ob_get_clean();
    }

    /**
     * ดำเนินการล็อกอิน
     *
     * ขั้นตอนหลัก:
     * 1. เริ่ม session (session_start) เพื่อใช้ $_SESSION
     * 2. อ่าน username/password จาก POST
     * 3. ค้นหาผู้ใช้จากโมเดล User
     * 4. ตรวจสอบรหัสผ่าน (ปัจจุบันเป็นการเปรียบเทียบแบบตรงตัว)
     * 5. ถ้าสำเร็จ: เก็บข้อมูลผู้ใช้ใน $_SESSION['user'] และ redirect
     * 6. ถ้าไม่สำเร็จ: ตั้ง $_SESSION['error'] แล้ว redirect กลับหน้า login
     *
     * ข้อมูลที่เก็บลง session เมื่อสำเร็จ:
     * - $_SESSION['user'] = ['id' => ..., 'name' => ...]
     *
     * Flow พิเศษ 'continue_pledge':
     * - หากมีพารามิเตอร์ GET next=continue_pledge จะเก็บข้อมูล pending_pledge
     *   (project_id, reward_tier_id, amount) ลงใน session แล้ว redirect ไปยัง
     *   ?r=pledge-continue เพื่อให้ระบบทำรายการต่อหลัง login เสร็จ
     *
     * ความปลอดภัย/ปรับปรุงที่แนะนำ:
     * - เปลี่ยนการเก็บรหัสผ่านใน DB เป็น password_hash() และตรวจสอบด้วย password_verify()
     * - ใช้ rate limiting / lockout หลังพยายามล็อกอินล้มเหลวหลายครั้ง
     * - เซนิตาไลซ์/validate ค่า input เพิ่มเติมตามต้องการ
     */
    public function login()
    {
        // เริ่ม session เพื่อเข้าถึง/จัดการ $_SESSION
        session_start();

        // อ่านค่าจาก POST (ใช้ null coalescing operator ป้องกัน undefined index)
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';

        // ดึงข้อมูลผู้ใช้จากโมเดล (สมมติว่า User::findByUsername คืนข้อมูล associative array)
        $user = (new User())->findByUsername($u);

        // ตรวจสอบความถูกต้องของรหัสผ่าน
        // ปัจจุบันเป็นการเทียบแบบตรงตัว ซึ่งไม่ปลอดภัยในงานจริง (แนะนำ password_verify)
        if ($user && $user['password'] === $p) {
            // เก็บข้อมูลสำคัญของผู้ใช้ลง session เพื่อบ่งชี้ว่าล็อกอินเรียบร้อยแล้ว
            // display_name ควรมีในข้อมูลที่ findByUsername คืนกลับมา
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['display_name']];

            // หากมีการเรียกโดยตั้งใจทำ pledge ก่อนล็อกอิน (continue flow)
            // เก็บข้อมูล pending_pledge ไว้ใน session แล้วส่งผู้ใช้ไปต่อยังการประมวลผล
            $next = $_GET['next'] ?? null;
            if ($next === 'continue_pledge') {
                $_SESSION['pending_pledge'] = [
                    'project_id'     => (int)($_GET['project_id'] ?? 0),
                    'reward_tier_id' => (isset($_GET['reward_tier_id']) && $_GET['reward_tier_id'] !== '') ? (int)$_GET['reward_tier_id'] : null,
                    'amount'         => (float)($_GET['amount'] ?? 0),
                ];
                // เปลี่ยนเส้นทางไปยัง controller ที่จะทำรายการ pledge ต่อ
                header("Location: ?r=pledge-continue");
                exit;
            }

            // ปกติแล้วให้กลับไปหน้าโครงการหลัก
            header("Location: ?r=");
            exit;
        }

        // กรณีล็อกอินไม่สำเร็จ: ตั้งค่า error ใน session แล้ว redirect ไปหน้า login
        // Layout/view จะต้องอ่าน $_SESSION['error'] หรือ $_SESSION['flash'] เพื่อแสดงข้อความ
        $_SESSION['error'] = "Invalid credentials";
        header("Location: ?r=login");
        exit;
    }

    /**
     * ออกจากระบบ
     * - เรียก session_start() เพื่อให้แน่ใจว่าสามารถเข้าถึง session ปัจจุบันได้
     * - session_destroy() จะลบข้อมูล session ทั้งหมด (แนะนำให้ใช้ session_unset() ก่อนหากต้องการความแน่นอน)
     */
    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: ?r=login");
        exit;
    }
}
