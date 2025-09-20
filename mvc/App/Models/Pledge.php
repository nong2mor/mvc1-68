<?php

namespace App\Models;

/**
 * โมเดล Pledge
 * - บันทึกการสนับสนุน (pledge)
 * - ให้สถิติเบื้องต้นและผลรวมยอดที่สำเร็จตามโปรเจกต์
 */
class Pledge extends BaseModel
{
    /**
     * สร้าง pledges ใหม่
     * พารามิเตอร์: array $data (user_id, project_id, reward_tier_id?, amount, status, rejection_reason?)
     * คืนค่า: id ของแถวใหม่
     */
    public function create(array $data)
    {
        $st = $this->pdo->prepare("INSERT INTO pledges (user_id,project_id,reward_tier_id,amount,status,rejection_reason) 
      VALUES (:user_id,:project_id,:reward_tier_id,:amount,:status,:reason)");
        $st->execute([
            ':user_id' => $data['user_id'],
            ':project_id' => $data['project_id'],
            ':reward_tier_id' => $data['reward_tier_id'] ?? null,
            ':amount' => $data['amount'],
            ':status' => $data['status'],
            ':reason' => $data['rejection_reason'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    /**
     * คืนสถิตินับจำนวน pledges แยกตามสถานะ
     * คืนค่า: array rows ที่มี keys: status, cnt
     */
    public function stats()
    {
        $sql = "SELECT status, COUNT(*) cnt FROM pledges GROUP BY status";
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * ผลรวมยอดที่สำเร็จ (status='success') ของโปรเจกต์หนึ่ง
     * คืนค่า: float ยอดรวม (0 ถ้าไม่มี)
     */
    public function sumSuccessByProject($project_id)
    {
        $st = $this->pdo->prepare("SELECT IFNULL(SUM(amount),0) s FROM pledges WHERE project_id=? AND status='success'");
        $st->execute([$project_id]);
        return (float)$st->fetch()['s'];
    }
}
