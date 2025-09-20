<?php
// แสดงข้อผิดพลาดเพื่อดีบัก (หากหน้าขาว)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// โหลด Composer autoload ถ้ามี (dependencies)
$vendor = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendor)) {
    require_once $vendor;
}

// simple PSR-4-ish autoload สำหรับโฟลเดอร์ App/*
spl_autoload_register(function ($class) {
    $base = __DIR__ . '/../';
    $path = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) require $path;
});

// นำเข้า controller/router ที่ใช้
use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\ProjectController;
use App\Controllers\PledgeController;
use App\Controllers\StatsController;

// สร้าง router และลงทะเบียนเส้นทาง (routes)
$router = new Router();
$router->get('/',         [new ProjectController(), 'index']);        // หน้าแรก - รายการโปรเจกต์
$router->get('/project',  [new ProjectController(), 'show']);         // รายละเอียดโปรเจกต์
$router->get('/stats',    [new StatsController(),  'index']);        // สถิติ

// หน้าเข้าสู่ระบบ / ออกจากระบบ
$router->get('/login',    [new AuthController(), 'showLogin']);
$router->post('/login',   [new AuthController(), 'login']);
$router->get('/logout',   [new AuthController(), 'logout']);

// การสร้าง pledge
$router->post('/pledge',  [new PledgeController(), 'store']);
$router->get('/pledge-continue', [new PledgeController(), 'continuePledge']);

// ประมวลผล route ปัจจุบัน
$router->dispatch();
