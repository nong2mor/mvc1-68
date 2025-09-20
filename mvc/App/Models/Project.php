<?php

namespace App\Models;

/**
 * โมเดล Project
 * จัดการการดึงข้อมูลโปรเจกต์ (list, find) และอัปเดตยอดที่ได้รับ
 */
class Project extends BaseModel
{
    // คืนรายการโปรเจกต์ (รองรับค้นหา, กรองหมวดหมู่, เรียง)
    public function list(?string $q = null, ?int $category_id = null, string $sort = 'newest'): array
    {
        $sql = "SELECT
                  p.id, p.code, p.name, p.category_id, p.goal_amount, p.deadline, p.created_at,
                  c.name AS category_name,
                  (
                    SELECT IFNULL(SUM(pl.amount),0)
                    FROM pledges pl
                    WHERE pl.project_id = p.id AND pl.status = 'success'
                  ) AS raised_sum
                FROM projects p
                JOIN categories c ON c.id = p.category_id
                WHERE 1=1";
        $params = [];

        // กรองด้วยคำค้น (name หรือ code)
        if ($q) {
            $sql .= " AND (p.name LIKE :q OR p.code LIKE :q)";
            $params[':q'] = "%{$q}%";
        }
        // กรองด้วยหมวดหมู่
        if ($category_id) {
            $sql .= " AND p.category_id = :cid";
            $params[':cid'] = $category_id;
        }

        // เลือกการเรียงตามพารามิเตอร์
        if ($sort === 'ending_soon') {
            $sql .= " ORDER BY p.deadline ASC";
        } elseif ($sort === 'most_funded') {
            $sql .= " ORDER BY raised_sum DESC";
        } else {
            $sql .= " ORDER BY p.created_at DESC";
        }

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    // หาโปรเจกต์ตาม id หรือคืน null
    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    // อัปเดตยอดที่รับแล้ว (เพิ่ม delta) — ใช้เมื่อบันทึก pledge สำเร็จ
    public function updateRaised(int $project_id, float $delta): bool
    {
        $st = $this->pdo->prepare("UPDATE projects SET raised_amount = raised_amount + ? WHERE id = ?");
        return $st->execute([$delta, $project_id]);
    }
}
