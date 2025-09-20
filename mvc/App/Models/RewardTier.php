<?php

namespace App\Models;

/**
 * โมเดล RewardTier
 * จัดการระดับรางวัลของโปรเจกต์
 */
class RewardTier extends BaseModel
{
    // ดึงรายการรางวัลของโปรเจกต์
    public function byProject(int $project_id): array
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM reward_tiers WHERE project_id = ? ORDER BY min_amount ASC"
        );
        $st->execute([$project_id]);
        return $st->fetchAll();
    }

    // หา reward tier ตาม id หรือคืน null
    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM reward_tiers WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    // ลดโควตา 1 ถ้ายังมีเหลือ (คืน true หากลดสำเร็จ)
    public function decQuota(int $id): bool
    {
        $st = $this->pdo->prepare("UPDATE reward_tiers SET quota = quota - 1 WHERE id = ? AND quota > 0");
        $st->execute([$id]);
        return $st->rowCount() > 0;
    }
}
