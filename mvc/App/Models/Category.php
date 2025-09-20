<?php

namespace App\Models;

/**
 * โมเดลสำหรับหมวดหมู่ (categories)
 * ใช้สำหรับดึงรายการหมวดหมู่จากฐานข้อมูล
 */
class Category extends BaseModel
{
    // คืนรายการหมวดหมู่ทั้งหมด เรียงตามชื่อ (ใช้เมื่อแสดงตัวกรอง/เมนู)
    public function all(): array
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        return $this->pdo->query($sql)->fetchAll();
    }

    // หาหมวดหมู่ตาม id หรือคืน null ถ้าไม่พบ
    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }
}
