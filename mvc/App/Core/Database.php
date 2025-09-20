<?php

namespace App\Core;

/**
 * คืนค่า PDO เดียวของแอป (singleton)
 * สั้น ๆ: สร้าง PDO จาก config และเก็บไว้ใช้ซ้ำ
 */

class Database
{
    // เก็บ PDO instance เดียวของระบบ
    private static $pdo = null;

    /**
     * คืนค่า PDO instance (สร้างเมื่อเรียกครั้งแรก)
     */
    public static function pdo()
    {
        if (!self::$pdo) {
            // โหลดการตั้งค่าจาก config.php
            $cfg = include __DIR__ . '/../../config.php';
            self::$pdo = new \PDO($cfg['db']['dsn'], $cfg['db']['user'], $cfg['db']['pass'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        }
        return self::$pdo;
    }
}
