-- สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS crowdfund CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crowdfund;

-- ผู้ใช้ระบบ (ง่าย ๆ ไม่เข้ารหัส เพื่อให้ตรงโจทย์ที่ไม่ซีเรียสความปลอดภัย)
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50) UNIQUE NOT NULL,
  password      VARCHAR(100) NOT NULL,
  display_name  VARCHAR(100) NOT NULL
);

-- หมวดหมู่โครงการ
CREATE TABLE categories (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- โครงการ
CREATE TABLE projects (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  code             CHAR(8) NOT NULL UNIQUE,            -- 8 หลัก ตัวแรกห้ามเป็น 0 (ตรวจในโค้ด)
  name             VARCHAR(200) NOT NULL,
  category_id      INT NOT NULL,
  goal_amount      DECIMAL(12,2) NOT NULL CHECK (goal_amount > 0),
  deadline         DATETIME NOT NULL,                  -- ต้อง > NOW() ตอนสร้าง/แก้
  raised_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- ระดับรางวัล
CREATE TABLE reward_tiers (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  project_id     INT NOT NULL,
  name           VARCHAR(200) NOT NULL,
  min_amount     DECIMAL(12,2) NOT NULL,              -- ขั้นต่ำของรางวัล
  quota          INT NOT NULL,                        -- โควตาคงเหลือ
  FOREIGN KEY (project_id) REFERENCES projects(id)
);

-- การสนับสนุน (รวมทั้งที่ถูกปฏิเสธ)
CREATE TABLE pledges (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  user_id          INT NOT NULL,
  project_id       INT NOT NULL,
  reward_tier_id   INT NULL,                          -- เลือกหรือไม่เลือกก็ได้
  amount           DECIMAL(12,2) NOT NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status           ENUM('success','rejected') NOT NULL,
  rejection_reason VARCHAR(255) NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (reward_tier_id) REFERENCES reward_tiers(id)
);

-- index ให้ค้นหาเร็วขึ้น
CREATE INDEX idx_projects_category ON projects(category_id);
CREATE INDEX idx_pledges_project ON pledges(project_id);
CREATE INDEX idx_pledges_status ON pledges(status);
