<?php

namespace App\Controllers;

use App\Models\Pledge;
use App\Models\Project;
use App\Models\RewardTier;

/**
 * PledgeController
 *
 * รับผิดชอบการสร้าง pledge (การสนับสนุน) ทั้งแบบที่ล็อกอินอยู่แล้ว
 * และ flow "continue after login" ที่เก็บข้อมูลชั่วคราวไว้ใน session
 *
 * แนวทางการออกแบบใน controller นี้:
 * - ตรวจสอบการล็อกอินผ่าน requireLogin() ก่อนการกระทำที่ต้องมีผู้ใช้
 * - ตรวจสอบเงื่อนไขของโปรเจกต์ (มีอยู่จริง, ยังไม่หมดเขต)
 * - ตรวจสอบระดับรางวัล (reward tier) เมื่อมีการเลือก
 * - ทำการบันทึก pledge, อัปเดตยอดของโปรเจกต์, ลดโควตารางวัล ภายใน transaction
 * - เมื่อไม่ผ่านเงื่อนไข ให้บันทึก pledge เป็นสถานะ 'rejected' และ redirect พร้อม flash
 */
class PledgeController
{
    /**
     * requireLogin
     *
     * ตรวจสอบว่า session มีผู้ใช้ล็อกอินหรือไม่ หากยังไม่ได้ล็อกอินจะ redirect ไปหน้า login
     * - เรียก session_start() เพื่อเข้าถึง $_SESSION (idempotent เช็ค session_status ไม่จำเป็นแต่ปลอดภัย)
     * - คืนค่า $_SESSION['user'] เมื่อมี เพื่อให้ controller อื่นใช้ข้อมูลผู้ใช้ได้
     *
     * คืนค่า:
     *   - array user (เช่น ['id'=>..,'name'=>..]) หากล็อกอิน
     * side-effect:
     *   - redirect และ exit หากยังไม่ล็อกอิน
     */
    private function requireLogin()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            // ถ้าไม่ล็อกอิน ให้บังคับไปหน้า login (ระบบ routing ของโปรเจกต์ ใช้พารามิเตอร์ ?r=login)
            header("Location: ?r=login");
            exit;
        }
        return $_SESSION['user'];
    }

    /**
     * continuePledge
     *
     * Flow พิเศษเมื่อผู้ใช้คลิก "เข้าสู่ระบบเพื่อสนับสนุน" ขณะยังไม่ล็อกอิน
     * - เมื่อกลับมาหลังล็อกอิน จะอ่านข้อมูลจาก $_SESSION['pending_pledge']
     *   ซึ่งถูกตั้งไว้โดย AuthController เมื่อผู้ใช้คลิกลิงก์เข้าระบบพร้อมพารามิเตอร์ next=continue_pledge
     * - หากมี pending data จะย้ายค่าลงใน $_POST แล้วเรียก store() เพื่อประมวลผลการสนับสนุนต่อ
     *
     * เหตุผล: ง่ายต่อการ reuse ของ logic ใน store() ที่คาดว่ารับข้อมูลจาก POST
     */
    public function continuePledge()
    {
        $this->requireLogin();

        // อ่านข้อมูล pending ที่เก็บไว้ก่อนล็อกอิน
        $pending = $_SESSION['pending_pledge'] ?? null;
        if (!$pending) {
            // ถ้าไม่มี pending data ให้กลับไปหน้าโครงการหลัก
            header("Location: ?r=");
            exit;
        }

        // ย้ายข้อมูลจาก pending ไปยัง POST เพื่อให้ store() ทำงานต่อได้
        $_POST = [
            'project_id'     => $pending['project_id'],
            'amount'         => $pending['amount'],
            'reward_tier_id' => $pending['reward_tier_id'] ?? ''
        ];
        // ลบ pending หลังย้ายข้อมูลแล้ว
        unset($_SESSION['pending_pledge']);

        // เรียก store() เพื่อทำรายการต่อ (จะตรวจ login อีกครั้งภายใน)
        return $this->store();
    }

    /**
     * store
     *
     * หลักการทำงาน:
     * 1. ตรวจสอบผู้ใช้ (requireLogin)
     * 2. ดึงค่า project_id, amount, reward_tier_id จาก POST และ cast ให้ชนิดที่เหมาะสม
     * 3. ตรวจสอบว่าโปรเจกต์มีอยู่จริง และยังไม่หมดเขต
     * 4. ถ้ามี reward tier: ตรวจสอบความสัมพันธ์กับโปรเจกต์, ตรวจ min_amount และ quota
     * 5. ถ้าทุกอย่างถูกต้อง: ทำ transaction บันทึก pledge, อัปเดตยอดโปรเจกต์, ลดโควตา
     * 6. ถ้าเกิดข้อผิดพลาดในเงื่อนไข ให้บันทึกเป็น rejected และแจ้งผู้ใช้ผ่าน flash
     *
     * หมายเหตุความปลอดภัย:
     * - ควรมีการ validate/normalize input เพิ่มเติมตามความต้องการ (เช่น limit max amount, sanitize)
     */
    public function store()
    {
        // ตรวจ login และรับข้อมูลผู้ใช้ (array)
        $user = $this->requireLogin();

        // ดึงค่า input จาก POST และ cast
        $project_id = (int)($_POST['project_id'] ?? 0);
        $amount     = (float)($_POST['amount'] ?? 0);
        $reward_id  = !empty($_POST['reward_tier_id']) ? (int)$_POST['reward_tier_id'] : null;

        // หาโปรเจกต์จากโมเดล
        $proj = (new Project())->find($project_id);
        if (!$proj) {
            // หากโปรเจกต์ไม่พบ ให้บันทึกเป็น rejected และ redirect
            return $this->reject($user['id'], $project_id, $reward_id, $amount, 'project_not_found');
        }

        // ตรวจสอบ deadline: ถ้าวันปัจจุบันเลยวันที่ปิดแล้ว ให้ปฏิเสธ
        if (strtotime($proj['deadline']) <= time()) {
            return $this->reject($user['id'], $project_id, $reward_id, $amount, 'deadline_passed');
        }

        // หากมีการเลือก reward tier ให้ตรวจสอบความถูกต้องของ tier
        $tier = null;
        if ($reward_id) {
            $tier = (new RewardTier())->find($reward_id);

            // ตรวจว่า tier มีอยู่ และเป็นของโปรเจกต์เดียวกัน
            if (!$tier || (int)$tier['project_id'] !== $project_id) {
                return $this->reject($user['id'], $project_id, $reward_id, $amount, 'invalid_reward');
            }

            // ตรวจมูลค่าขั้นต่ำของรางวัล
            if ($amount < (float)$tier['min_amount']) {
                return $this->reject($user['id'], $project_id, $reward_id, $amount, 'below_min_reward');
            }

            // ตรวจโควตารางวัลต้องมากกว่า 0
            if ((int)$tier['quota'] <= 0) {
                return $this->reject($user['id'], $project_id, $reward_id, $amount, 'no_quota');
            }
        } else {
            // ถ้าไม่เลือก reward tier ต้องระบุยอด > 0
            if ($amount <= 0) {
                return $this->reject($user['id'], $project_id, $reward_id, $amount, 'amount_must_be_positive');
            }
        }

        // ทำงานภายใน transaction เพื่อให้การบันทึก pledge, อัปเดตยอด, ลดโควตา ทำพร้อมกัน
        // หากเกิดข้อผิดพลาด ให้ rollback ทั้งหมด
        $pdo = (new \App\Core\Database())::pdo();
        try {
            $pdo->beginTransaction();

            // สร้าง pledge ใหม่เป็นสถานะ success
            (new Pledge())->create([
                'user_id'        => $user['id'],
                'project_id'     => $project_id,
                'reward_tier_id' => $reward_id,
                'amount'         => $amount,
                'status'         => 'success'
            ]);

            // อัปเดตยอดที่ได้รับของโปรเจกต์ (raised/ current_amount)
            (new Project())->updateRaised($project_id, $amount);

            // หากเลือก reward tier ให้ลด quota ลง 1
            if ($reward_id) {
                (new RewardTier())->decQuota($reward_id);
            }

            // commit เมื่อทุกอย่างสำเร็จ
            $pdo->commit();
        } catch (\Throwable $e) {
            // หากเกิดข้อผิดพลาดในระหว่าง transaction ให้ rollback
            $pdo->rollBack();
            // คืนข้อความ error แบบง่าย (ใน production ควร log และแสดงข้อความมิตรแทน)
            return "Error: " . $e->getMessage();
        }

        // นำผู้ใช้กลับไปยังหน้ารายละเอียดโปรเจกต์หลังสนับสนุนสำเร็จ
        header("Location: ?r=project&id=" . $project_id);
        exit;
    }

    /**
     * reject
     *
     * ฟังก์ชันช่วยเหลือเมื่อการสนับสนุนไม่ผ่านเงื่อนไข:
     * - บันทึก pledge ในสถานะ 'rejected' พร้อมสาเหตุ (rejection_reason)
     * - ตั้ง $_SESSION['flash'] เพื่อแสดงข้อความให้ผู้ใช้เห็น
     * - redirect กลับไปยังหน้ารายละเอียดโปรเจกต์
     *
     * พารามิเตอร์:
     * - $user_id : id ของผู้ใช้ (อาจเป็น 0 หาก guest แต่ในระบบนี้เรียกเมื่อล็อกอินแล้ว)
     * - $project_id, $reward_id, $amount : ข้อมูลที่พยายามส่ง
     * - $reason : รหัสเหตุผล (เช่น 'below_min_reward', 'no_quota' ฯลฯ)
     */
    private function reject($user_id, $project_id, $reward_id, $amount, $reason)
    {
        // บันทึก pledge เป็น rejected เพื่อเก็บประวัติการพยายามสนับสนุน
        (new Pledge())->create([
            'user_id'          => $user_id,
            'project_id'       => $project_id,
            'reward_tier_id'   => $reward_id,
            'amount'           => $amount,
            'status'           => 'rejected',
            'rejection_reason' => $reason
        ]);

        // เก็บ flash message ใน session (layout จะอ่านและแสดง)
        $_SESSION['flash'] = "ปฏิเสธการสนับสนุน: $reason";

        // กลับไปหน้าโปรเจกต์นั้น
        header("Location: ?r=project&id=" . $project_id);
        exit;
    }
}
