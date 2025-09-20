<?php

namespace App\Core;

// ตัวจัดการเส้นทางขนาดเล็ก (simple router)
class Router
{
    private $routes = [];

    // ลงทะเบียนเส้นทางแบบ GET / POST
    public function get($path, $handler)
    {
        $this->routes['GET'][$path]  = $handler;
    }
    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch()
    {
        // วิธีการร้องขอ (GET/POST)
        $method = $_SERVER['REQUEST_METHOD'];

        // อ่านพารามิเตอร์ r เพื่อกำหนดเส้นทาง (default = '/')
        $uri = $_GET['r'] ?? '/';
        if ($uri === '') $uri = '/';
        if ($uri[0] !== '/') $uri = '/' . $uri;

        // หา handler ที่ตรงกับ method และ uri
        $handler = $this->routes[$method][$uri] ?? null;
        if (!$handler) {
            // หากไม่มี route ที่ตรง ให้ส่ง 404
            http_response_code(404);
            echo "Not found: " . htmlspecialchars($uri);
            return;
        }

        // เรียก handler แล้วพิมพ์ผลลัพธ์
        echo call_user_func($handler, $_REQUEST);
    }
}
