<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Crowdfund MVC</title>

    <!-- base href ช่วยให้ลิงก์/ฟอร์มใช้ path ที่ถูกต้อง -->
    <base href="/mvc/public/">

    <!-- สไตล์เรียบง่ายของแอป -->
    <style>
        body {
            font-family: sans-serif;
            max-width: 900px;
            margin: 20px auto
        }

        header a {
            margin-right: 10px
        }

        .progress {
            background: #eee;
            border-radius: 6px
        }

        .bar {
            height: 12px;
            background: #4caf50;
            border-radius: 6px
        }

        .flash {
            background: #ffefc2;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #f0d48a
        }
    </style>
</head>

<body>
    <header>
        <?php // เริ่ม session เพื่อใช้ $_SESSION (user, flash) 
        ?>
        <?php session_start(); ?>
        <!-- เมนูหลัก -->
        <a href="?r=">โครงการ</a>
        <a href="?r=stats">สรุปสถิติ</a>

        <?php if (!empty($_SESSION['user'])): ?>
            <?php // แสดงชื่อผู้ใช้เมื่อล็อกอินแล้ว 
            ?>
            <span>สวัสดี, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
            <a href="?r=logout">ออกจากระบบ</a>
        <?php else: ?>
            <?php // ลิงก์ไปหน้าล็อกอินถ้ายังไม่ล็อกอิน 
            ?>
            <a href="?r=login">เข้าสู่ระบบ</a>
        <?php endif; ?>
    </header>

    <?php
    // แสดงข้อความชั่วคราว (flash) แล้วลบออกจาก session
    if (!empty($_SESSION['flash'])) {
        echo '<div class="flash">' . htmlspecialchars($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
    ?>

    <main>
        <?= $content ?? '' ?> <!-- เนื้อหาจาก view -->
    </main>
</body>

</html>