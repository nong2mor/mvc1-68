<?php

namespace App\Models;

/**
 * โมเดล User แบบเรียบง่าย
 * - ใช้สำหรับค้นหาผู้ใช้ตาม username
 */
class User extends BaseModel
{
    // คืนข้อมูลผู้ใช้แบบ associative array หรือ false หากไม่พบ
    public function findByUsername($u)
    {
        $st = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $st->execute([$u]);
        return $st->fetch();
    }
}
