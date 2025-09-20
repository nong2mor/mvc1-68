# Crowdfund MVC — README

สรุปสั้น ๆ: โปรเจกต์ PHP แบบเล็ก (no framework) สำหรับตัวอย่างระบบระดมทุน มี routing, models, controllers, views และฐานข้อมูลตัวอย่าง

ไฟล์สำคัญ
- Entry: [Public/index.php](Public/index.php)
- Router: [App/Core/Router.php](App/Core/Router.php)
- Database helper: [App/Core/Database.php](App/Core/Database.php)
- Controllers:  
  - [App/Controllers/AuthController.php](App/Controllers/AuthController.php)  
  - [App/Controllers/ProjectController.php](App/Controllers/ProjectController.php)  
  - [App/Controllers/PledgeController.php](App/Controllers/PledgeController.php)  
  - [App/Controllers/StatsController.php](App/Controllers/StatsController.php)
- Models:  
  - [App/Models/BaseModel.php](App/Models/BaseModel.php)  
  - [App/Models/User.php](App/Models/User.php)  
  - [App/Models/Project.php](App/Models/Project.php)  
  - [App/Models/RewardTier.php](App/Models/RewardTier.php)  
  - [App/Models/Pledge.php](App/Models/Pledge.php)  
  - [App/Models/Category.php](App/Models/Category.php)
- Views: [App/Views/layout.php](App/Views/layout.php), [App/Views/project/*.php](App/Views/project/)
- DB: [Database/schema.sql](Database/schema.sql), [Database/seed.sql](Database/seed.sql)
- Config: [config.php](config.php)

ติดตั้ง / รัน (XAMPP)
1. เปิด XAMPP และ start Apache + MySQL.
2. สร้างฐานข้อมูลและใส่ตัวอย่าง:
   - ใน Terminal (Mac):
     - mysql -u root < Database/schema.sql
     - mysql -u root < Database/seed.sql
   - หรือใช้ phpMyAdmin แล้ว import ทั้งสองไฟล์
3. ตรวจสอบ config.php ให้ตรงกับการตั้งค่าฐานข้อมูล (ค่าเริ่มต้นใช้ root / ไม่มีรหัส)
4. เปิดเบราว์เซอร์ที่: http://localhost/mvc/public/  
   (หรือปรับค่า <base> ใน [App/Views/layout.php](App/Views/layout.php) ให้ตรงถ้าวางใน path อื่น)

รันด้วย PHP built-in (ทางเลือก)
- จากโฟลเดอร์ project root:
  - php -S localhost:8000 -t Public
  - เปิด http://localhost:8000/

ข้อมูลทดลอง / เข้าสู่ระบบ
- Seed มีผู้ใช้ตัวอย่าง: username = alice, bob, ... (รหัสผ่านจาก seed เป็น `1234` แบบ plain text)
- หมายเหตุ: รหัสผ่านใน seed เก็บเป็น plain text เพื่อความเรียบง่าย — เปลี่ยนเป็น password_hash() สำหรับ production

คำแนะนำสั้น ๆ / Troubleshoot
- ถ้าหน้าขาว: ตรวจสอบ error display (Public/index.php เปิด display_errors) และตรวจ log ของ Apache/PHP
- ถ้าเชื่อม DB ไม่ได้: ตรวจ config.php และว่า MySQL รันอยู่
- หาก route ไม่เจอ ตรวจ .htaccess (ใน Public/) และ base href ใน layout

ข้อควรระวังด้านความปลอดภัย
- เปลี่ยนการเก็บรหัสผ่านเป็น password_hash()
- ใช้ HTTPS, prepared statements (โมเดลใช้ PDO prepared statements แล้วส่วนใหญ่)
- อย่าใช้ seed ใน production

ต้องการแพตช์ไฟล์หรือเพิ่มตัวอย่างคำสั่ง SQL/ขั้นตอนการทดสอบอื่น ๆ บอกได้ผมจะเตรียมให้เร็ว ๆ นี้.
```// filepath: /Applications/XAMPP/xamppfiles/htdocs/mvc/README.md
# Crowdfund MVC — README

สรุปสั้น ๆ: โปรเจกต์ PHP แบบเล็ก (no framework) สำหรับตัวอย่างระบบระดมทุน มี routing, models, controllers, views และฐานข้อมูลตัวอย่าง

ไฟล์สำคัญ
- Entry: [Public/index.php](Public/index.php)
- Router: [App/Core/Router.php](App/Core/Router.php)
- Database helper: [App/Core/Database.php](App/Core/Database.php)
- Controllers:  
  - [App/Controllers/AuthController.php](App/Controllers/AuthController.php)  
  - [App/Controllers/ProjectController.php](App/Controllers/ProjectController.php)  
  - [App/Controllers/PledgeController.php](App/Controllers/PledgeController.php)  
  - [App/Controllers/StatsController.php](App/Controllers/StatsController.php)
- Models:  
  - [App/Models/BaseModel.php](App/Models/BaseModel.php)  
  - [App/Models/User.php](App/Models/User.php)  
  - [App/Models/Project.php](App/Models/Project.php)  
  - [App/Models/RewardTier.php](App/Models/RewardTier.php)  
  - [App/Models/Pledge.php](App/Models/Pledge.php)  
  - [App/Models/Category.php](App/Models/Category.php)
- Views: [App/Views/layout.php](App/Views/layout.php), [App/Views/project/*.php](App/Views/project/)
- DB: [Database/schema.sql](Database/schema.sql), [Database/seed.sql](Database/seed.sql)
- Config: [config.php](config.php)

ติดตั้ง / รัน (XAMPP)
1. เปิด XAMPP และ start Apache + MySQL.
2. สร้างฐานข้อมูลและใส่ตัวอย่าง:
   - ใน Terminal (Mac):
     - mysql -u root < Database/schema.sql
     - mysql -u root < Database/seed.sql
   - หรือใช้ phpMyAdmin แล้ว import ทั้งสองไฟล์
3. ตรวจสอบ config.php ให้ตรงกับการตั้งค่าฐานข้อมูล (ค่าเริ่มต้นใช้ root / ไม่มีรหัส)
4. เปิดเบราว์เซอร์ที่: http://localhost/mvc/public/  
   (หรือปรับค่า <base> ใน [App/Views/layout.php](App/Views/layout.php) ให้ตรงถ้าวางใน path อื่น)

รันด้วย PHP built-in (ทางเลือก)
- จากโฟลเดอร์ project root:
  - php -S localhost:8000 -t Public
  - เปิด http://localhost:8000/

ข้อมูลทดลอง / เข้าสู่ระบบ
- Seed มีผู้ใช้ตัวอย่าง: username = alice, bob, ... (รหัสผ่านจาก seed เป็น `1234` แบบ plain text)
- หมายเหตุ: รหัสผ่านใน seed เก็บเป็น plain text เพื่อความเรียบง่าย — เปลี่ยนเป็น password_hash() สำหรับ production

คำแนะนำสั้น ๆ / Troubleshoot
- ถ้าหน้าขาว: ตรวจสอบ error display (Public/index.php เปิด display_errors) และตรวจ log ของ Apache/PHP
- ถ้าเชื่อม DB ไม่ได้: ตรวจ config.php และว่า MySQL รันอยู่
- หาก route ไม่เจอ ตรวจ .htaccess (ใน Public/) และ base href ใน layout

ข้อควรระวังด้านความปลอดภัย
- เปลี่ยนการเก็บรหัสผ่านเป็น password_hash()
- ใช้ HTTPS, prepared statements (โมเดลใช้ PDO prepared statements แล้วส่วนใหญ่)
- อย่าใช้ seed ใน production

ต้องการแพตช์ไฟล์หรือเพิ่มตัวอย่างคำสั่ง SQL/ขั้นตอนการทดสอบอื่น ๆ บอกได้ผมจะเตรียมให้เร็ว ๆ นี้.