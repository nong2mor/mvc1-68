<?php

namespace App\Models;

use App\Core\Database;

/**
 * BaseModel
 * คลาสฐานสำหรับโมเดลอื่นๆ ใช้เชื่อมต่อ PDO ร่วมกัน
 * - สร้าง PDO เดียวจาก App\Core\Database::pdo()
 * - โมเดลย่อยสามารถใช้ $this->pdo ทำงานกับฐานข้อมูลได้
 */
abstract class BaseModel
{
    // PDO ที่ใช้ร่วมกันในโมเดลย่อย
    protected $pdo;

    // สร้างเมื่อมีการสร้างโมเดลและเก็บ PDO ไว้ใช้งาน
    public function __construct()
    {
        $this->pdo = Database::pdo();
    }
}
