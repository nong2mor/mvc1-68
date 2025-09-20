USE crowdfund;

INSERT INTO users (username, password, display_name) VALUES
('alice','1234','Alice'),
('bob','1234','Bob'),
('carol','1234','Carol'),
('dave','1234','Dave'),
('erin','1234','Erin'),
('frank','1234','Frank'),
('grace','1234','Grace'),
('heidi','1234','Heidi'),
('ivan','1234','Ivan'),
('judy','1234','Judy');

INSERT INTO categories (name) VALUES
('เทคโนโลยี'), ('งานศิลป์/ดีไซน์'), ('สังคม/การกุศล');

-- โครงการ 8 รายการ (deadline ตั้งอนาคต)
INSERT INTO projects (code,name,category_id,goal_amount,deadline,raised_amount) VALUES
('12345678','Smart Farm Kit v2',1, 150000,'2025-12-31 23:59:59',0),
('20001234','AI Note Summarizer',1, 250000,'2025-11-30 23:59:59',0),
('34567890','Indie Art Book',2, 80000,'2025-12-15 23:59:59',0),
('45678901','Community Library Renovation',3, 300000,'2026-01-31 23:59:59',0),
('56789012','Open Hardware Synth',1, 120000,'2025-12-10 23:59:59',0),
('67890123','Charity Meal Box',3, 100000,'2025-10-31 23:59:59',0),
('78901234','Minimal Poster Series',2, 50000,'2025-11-15 23:59:59',0),
('89012345','STEM Camp for Kids',3, 220000,'2025-12-20 23:59:59',0);

-- Reward tiers (2–3 ต่อโปรเจกต์)
INSERT INTO reward_tiers (project_id,name,min_amount,quota) VALUES
(1,'Sticker Pack',200,100),(1,'T-shirt',800,50),(1,'Kit Early Bird',2500,20),
(2,'Beta Access',300,200),(2,'Pro Lifetime',2500,30),
(3,'PDF Book',150,300),(3,'Signed Print',700,60),
(4,'Thank You Wall',200,500),(4,'Donor Plaque',3000,20),
(5,'DIY Panel',400,120),(5,'Full Synth Kit',3500,15),
(6,'Meal x2',120,400),(6,'Meal x10',500,100),
(7,'One Poster',200,150),(7,'Set of 3',500,80),
(8,'Supporter',250,200),(8,'Mentor Pass',2000,25);

-- ตัวอย่าง Pledges ผสมทั้งสำเร็จ/ปฏิเสธ
-- บางรายการถูกปฏิเสธเพราะน้อยกว่าขั้นต่ำของรางวัล หรือ deadline หมด (สมมุติสถานการณ์อื่น ๆ)
INSERT INTO pledges (user_id,project_id,reward_tier_id,amount,status,rejection_reason,created_at) VALUES
(1,1,1,200,'success',NULL,NOW()),
(2,1,2,500,'rejected','below_min_reward',NOW()),
(3,1,2,800,'success',NULL,NOW()),
(4,2,5,2000,'rejected','below_min_reward',NOW()),
(5,2,4,300,'success',NULL,NOW()),
(6,3,6,150,'success',NULL,NOW()),
(7,3,7,600,'rejected','below_min_reward',NOW()),
(8,4,9,3500,'success',NULL,NOW()),
(9,6,12,500,'success',NULL,NOW()),
(10,7,14,200,'success',NULL,NOW()),
(1,8,16,250,'success',NULL,NOW()),
(2,8,17,1500,'rejected','below_min_reward',NOW());
