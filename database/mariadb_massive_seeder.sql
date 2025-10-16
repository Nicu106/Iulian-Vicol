-- ============================================================================
-- AUTO E-COMMERCE - MASSIVE SEED DATA (1000+ records across all tables)
-- ============================================================================
-- Run this to populate database with extensive realistic data
-- Database: auto_ecommerce_mariadb

SET NAMES utf8mb4;
USE `auto_ecommerce_mariadb`;

-- Disable foreign key checks temporarily for faster insertion
SET FOREIGN_KEY_CHECKS=0;

-- ============================================================================
-- MASSIVE SEED 1: MORE VEHICLES (50+ vehicles)
-- ============================================================================
INSERT INTO `vehicles` (`slug`, `brand`, `model`, `title`, `year`, `price`, `status`, `mileage`, `fuel`, `engine`, `power`, `drivetrain`, `color`, `vin`, `condition`, `description`, `featured`, `priority`, `cover_image`, `created_at`, `updated_at`) VALUES
('vw-golf-8-2021', 'Volkswagen', 'Golf 8', 'Volkswagen Golf 8 Style 2021', 2021, 16990.00, 'available', 42000, 'Benzina', '1.5 TSI', '130 CP', 'FWD', 'Alb', 'WVWZZZ1KZ3W000021', 'used', 'Golf 8 generație nouă cu Digital Cockpit. Stare impecabilă, garanție extinsă.', 1, 10, 'https://picsum.photos/seed/golf8/900/600', NOW(), NOW()),
('bmw-320d-2017', 'BMW', '320d', 'BMW 320d Touring 2017', 2017, 18490.00, 'available', 135000, 'Diesel', '2.0d', '190 CP', 'RWD', 'Gri Mineral', 'WBA8E9108H7000022', 'used', 'BMW Seria 3 Touring diesel. Motor puternic, consum redus. Full LED, navi professional.', 1, 9, 'https://picsum.photos/seed/bmw320d/900/600', NOW(), NOW()),
('audi-a6-2018', 'Audi', 'A6', 'Audi A6 Ultra 2018', 2018, 23990.00, 'available', 96000, 'Diesel', '2.0 TDI', '190 CP', 'AWD', 'Negru Mythos', 'WAUZZZ4G2JN000023', 'used', 'Audi A6 Quattro cu MMI Touch. Executive sedan cu dotări premium. Matrix LED.', 1, 8, 'https://picsum.photos/seed/audia6/900/600', NOW(), NOW()),
('mercedes-c220-2019', 'Mercedes-Benz', 'C220', 'Mercedes-Benz C220d AMG 2019', 2019, 27990.00, 'available', 78000, 'Diesel', '2.0d', '194 CP', 'RWD', 'Alb Polar', 'WDD2050201R000024', 'used', 'Mercedes C-Class AMG Line. MBUX, Burmester, scaune AMG. Premium absolut.', 1, 10, 'https://picsum.photos/seed/mercc220/900/600', NOW(), NOW()),
('dacia-logan-2022', 'Dacia', 'Logan', 'Dacia Logan Stepway 2022', 2022, 9990.00, 'available', 28000, 'Benzina', '1.0 SCe', '65 CP', 'FWD', 'Verde Safari', 'UU1LSDAB0NL000025', 'used', 'Logan Stepway nou, sub garanție fabrică. Ideal familie sau flotă.', 0, 5, 'https://picsum.photos/seed/logan22/900/600', NOW(), NOW()),
('ford-focus-2020', 'Ford', 'Focus', 'Ford Focus ST-Line 2020', 2020, 13990.00, 'available', 67000, 'Diesel', '1.5 EcoBlue', '120 CP', 'FWD', 'Roșu Racing', '1FA6P8TH8L5000026', 'used', 'Focus ST-Line cu pachet sport. B&O Sound, keyless, Ford SYNC 3.', 0, 6, 'https://picsum.photos/seed/focus20/900/600', NOW(), NOW()),
('toyota-corolla-2020', 'Toyota', 'Corolla', 'Toyota Corolla Hybrid 2020', 2020, 15990.00, 'available', 54000, 'Hybrid', '1.8 Hybrid', '122 CP', 'FWD', 'Gri Argintiu', 'JTDBR3EU70L000027', 'used', 'Corolla Hybrid cu consum 4.2L/100km. Toyota Safety Sense, JBL Premium Sound.', 1, 7, 'https://picsum.photos/seed/corolla20/900/600', NOW(), NOW()),
('skoda-superb-2019', 'Skoda', 'Superb', 'Skoda Superb L&K 2019', 2019, 19990.00, 'available', 89000, 'Diesel', '2.0 TDI', '190 CP', 'AWD', 'Negru Magic', 'TMBCZ6NU4K8000028', 'used', 'Superb Laurin&Klement 4x4. Dotări top: Canton, masaj, adaptive chassis.', 1, 8, 'https://picsum.photos/seed/superb19/900/600', NOW(), NOW()),
('renault-megane-2021', 'Renault', 'Megane', 'Renault Megane RS Line 2021', 2021, 14490.00, 'available', 39000, 'Hybrid', '1.6 E-Tech', '160 CP', 'FWD', 'Albastru Iron', 'VF1BFB00064000029', 'used', 'Megane E-Tech Hybrid plug-in. Autonomie 50km electric. Tehnologie avansată.', 1, 7, 'https://picsum.photos/seed/megane21/900/600', NOW(), NOW()),
('opel-insignia-2018', 'Opel', 'Insignia', 'Opel Insignia Grand Sport 2018', 2018, 11990.00, 'available', 118000, 'Diesel', '2.0 CDTI', '170 CP', 'AWD', 'Gri Sovereign', 'W0LSH8EM8J8000030', 'used', 'Insignia 4x4 cu IntelliLux LED. Spațioasă, confortabilă, economică.', 0, 5, 'https://picsum.photos/seed/insignia18/900/600', NOW(), NOW()),
('peugeot-508-2020', 'Peugeot', '508', 'Peugeot 508 GT 2020', 2020, 21990.00, 'available', 62000, 'Hybrid', '1.6 Hybrid', '225 CP', 'FWD', 'Gri Selenium', 'VF3R7HY20LS000031', 'used', 'Peugeot 508 GT Hybrid4 cu 225CP. Design coupe, i-Cockpit 3D, Focal HiFi.', 1, 9, 'https://picsum.photos/seed/508gt/900/600', NOW(), NOW()),
('hyundai-i30-2021', 'Hyundai', 'i30', 'Hyundai i30 N-Line 2021', 2021, 13490.00, 'available', 41000, 'Benzina', '1.0 T-GDI', '120 CP', 'FWD', 'Roșu Passion', 'KMHDC81BBMU000032', 'used', 'i30 N-Line cu look sportiv. Garanție 5 ani activă. Smart Cruise Control.', 0, 6, 'https://picsum.photos/seed/i30nline/900/600', NOW(), NOW()),
('mazda-6-2019', 'Mazda', '6', 'Mazda 6 Revolution Top 2019', 2019, 16990.00, 'available', 73000, 'Diesel', '2.2 Skyactiv-D', '184 CP', 'FWD', 'Roșu Soul Crystal', 'JM1GL1UM7K1000033', 'used', 'Mazda 6 top echipare. Bose, head-up display, scaune Nappa. Design Kodo.', 1, 7, 'https://picsum.photos/seed/mazda6/900/600', NOW(), NOW()),
('volvo-v60-2018', 'Volvo', 'V60', 'Volvo V60 D4 Inscription 2018', 2018, 19490.00, 'available', 102000, 'Diesel', '2.0 D4', '190 CP', 'AWD', 'Alb Crystal', 'YV1A22SK7J2000034', 'used', 'V60 Inscription AWD. Pilot Assist II, scaune ventilate, Harman Kardon.', 1, 8, 'https://picsum.photos/seed/v60/900/600', NOW(), NOW()),
('nissan-leaf-2020', 'Nissan', 'Leaf', 'Nissan Leaf N-Connecta 2020', 2020, 18990.00, 'available', 35000, 'Electric', 'Electric', '150 CP', 'FWD', 'Albastru Metalic', 'SJNDAAEE0U2000035', '100% electric', 'Leaf 100% electric cu 40kWh. Autonomie 270km. ProPilot, Around View.', 1, 8, 'https://picsum.photos/seed/leaf20/900/600', NOW(), NOW()),
('kia-sportage-2021', 'Kia', 'Sportage', 'Kia Sportage GT-Line 2021', 2021, 17990.00, 'available', 48000, 'Hybrid', '1.6 T-GDI Hybrid', '230 CP', 'AWD', 'Gri Graphite', 'KNDPM3AC5M7000036', 'used', 'Sportage Hybrid 4WD. Garanție 7 ani. JBL Premium, panoramic, LED Matrix.', 1, 9, 'https://picsum.photos/seed/sportage21/900/600', NOW(), NOW()),
('seat-leon-2020', 'Seat', 'Leon', 'Seat Leon FR 2020', 2020, 13990.00, 'available', 56000, 'Benzina', '1.5 TSI', '150 CP', 'FWD', 'Roșu Desire', 'VSSZZZ5F2LR000037', 'used', 'Leon FR cu design sportiv. Digital Cockpit, Beat audio, Full Link.', 0, 6, 'https://picsum.photos/seed/leon20/900/600', NOW(), NOW()),
('honda-civic-2019', 'Honda', 'Civic', 'Honda Civic Executive 2019', 2019, 15490.00, 'available', 68000, 'Diesel', '1.6 i-DTEC', '120 CP', 'FWD', 'Alb Orchid', 'SHHFK7808KU000038', 'used', 'Civic sedan executive. Honda Sensing, scaune piele, climatronic 2 zone.', 0, 6, 'https://picsum.photos/seed/civic19/900/600', NOW(), NOW()),
('subaru-outback-2019', 'Subaru', 'Outback', 'Subaru Outback 2.5i 2019', 2019, 19990.00, 'available', 71000, 'Benzina', '2.5 Boxer', '175 CP', 'AWD', 'Verde Forest', 'JF2BSAGC0KH000039', 'used', 'Outback AWD permanent. Harman Kardon, EyeSight, X-Mode. Off-road capable.', 1, 7, 'https://picsum.photos/seed/outback19/900/600', NOW(), NOW()),
('lexus-nx-2018', 'Lexus', 'NX', 'Lexus NX 300h F-Sport 2018', 2018, 26990.00, 'available', 82000, 'Hybrid', '2.5 Hybrid', '197 CP', 'AWD', 'Alb Premium', 'JTHBJABH8J2000040', 'used', 'Lexus NX Hybrid AWD. Fiabilitate legendară, Mark Levinson, HUD.', 1, 10, 'https://picsum.photos/seed/nx18/900/600', NOW(), NOW()),
('vw-t-roc-2021', 'Volkswagen', 'T-Roc', 'Volkswagen T-Roc R-Line 2021', 2021, 18490.00, 'available', 38000, 'Benzina', '1.5 TSI', '150 CP', 'FWD', 'Portocaliu Energetic', 'WVGZZZ1TZ3W000041', 'used', 'T-Roc R-Line compact SUV. Beats Audio, Active Info Display, Travel Assist.', 1, 8, 'https://picsum.photos/seed/troc21/900/600', NOW(), NOW()),
('bmw-x3-2019', 'BMW', 'X3', 'BMW X3 xDrive20d 2019', 2019, 29990.00, 'available', 86000, 'Diesel', '2.0d', '190 CP', 'AWD', 'Albastru Phytonic', 'WBAXG7108K5000042', 'used', 'X3 xDrive cu M Sport Package. Gestiune adaptivă, Harman Kardon, panoramic.', 1, 10, 'https://picsum.photos/seed/x3-19/900/600', NOW(), NOW()),
('mercedes-gla-2021', 'Mercedes-Benz', 'GLA', 'Mercedes-Benz GLA 250e 2021', 2021, 28990.00, 'available', 34000, 'Hybrid', '1.3 Turbo Hybrid', '218 CP', 'AWD', 'Roșu Patagonia', 'WDD2430422N000043', 'used', 'GLA 250e plug-in hybrid. MBUX AR, Multibeam LED, AMG Line. Premium.', 1, 10, 'https://picsum.photos/seed/gla21/900/600', NOW(), NOW()),
('audi-q5-2019', 'Audi', 'Q5', 'Audi Q5 S-Line 2019', 2019, 27990.00, 'available', 79000, 'Diesel', '2.0 TDI', '190 CP', 'AWD', 'Gri Daytona', 'WAUZZZ8R6KA000044', 'used', 'Q5 Quattro S-Line. Virtual Cockpit Plus, B&O 3D, Matrix LED, Air suspension.', 1, 9, 'https://picsum.photos/seed/q5-19/900/600', NOW(), NOW()),
('dacia-sandero-2023', 'Dacia', 'Sandero', 'Dacia Sandero Stepway 2023', 2023, 11990.00, 'available', 12000, 'Benzina', '1.0 TCe', '90 CP', 'FWD', 'Portocaliu Arizona', 'UU1SSDAG0PN000045', 'used', 'Sandero Stepway nou model. Media Display 8", clima, ESC. Sub garanție.', 0, 5, 'https://picsum.photos/seed/sandero23/900/600', NOW(), NOW()),
('ford-mustang-2018', 'Ford', 'Mustang', 'Ford Mustang GT 5.0 2018', 2018, 34990.00, 'available', 42000, 'Benzina', '5.0 V8', '450 CP', 'RWD', 'Roșu Race', '1FA6P8CF8J5000046', 'used', 'Mustang GT V8 450CP! Shaker audio, suspensie MagneRide. American muscle!', 1, 10, 'https://picsum.photos/seed/mustang18/900/600', NOW(), NOW()),
('toyota-camry-2020', 'Toyota', 'Camry', 'Toyota Camry Hybrid Executive 2020', 2020, 22990.00, 'available', 58000, 'Hybrid', '2.5 Hybrid', '218 CP', 'FWD', 'Gri Celestial', 'JTNBHADA3L3000047', 'used', 'Camry Hybrid executive. JBL, HUD, scaune ventilate. Confort și economie.', 1, 8, 'https://picsum.photos/seed/camry20/900/600', NOW(), NOW()),
('skoda-karoq-2021', 'Skoda', 'Karoq', 'Skoda Karoq Sportline 2021', 2021, 17490.00, 'available', 44000, 'Benzina', '1.5 TSI', '150 CP', 'FWD', 'Gri Quartz', 'TMBJZ7NE2M6000048', 'used', 'Karoq Sportline cu design dinamic. Virtual Cockpit, Travel Assist, LED.', 1, 7, 'https://picsum.photos/seed/karoq21/900/600', NOW(), NOW()),
('renault-captur-2022', 'Renault', 'Captur', 'Renault Captur Techno 2022', 2022, 16490.00, 'available', 26000, 'Hybrid', '1.6 E-Tech', '145 CP', 'FWD', 'Portocaliu Valencia', 'VF1RJB00068000049', 'used', 'Captur E-Tech hybrid. Easy Link 9.3", climate auto, garanție extinsă.', 1, 7, 'https://picsum.photos/seed/captur22/900/600', NOW(), NOW()),
('opel-corsa-2021', 'Opel', 'Corsa', 'Opel Corsa-e Edition 2021', 2021, 17990.00, 'available', 31000, 'Electric', 'Electric', '136 CP', 'FWD', 'Alb Summit', 'W0VSX8HZ8M8000050', '100% electric', 'Corsa-e 100% electric. Autonomie 330km. Matrici IntelliLux LED.', 1, 8, 'https://picsum.photos/seed/corsae21/900/600', NOW(), NOW()),
('peugeot-2008-2021', 'Peugeot', '2008', 'Peugeot 2008 GT 2021', 2021, 16990.00, 'available', 37000, 'Benzina', '1.2 PureTech', '130 CP', 'FWD', 'Negru Perla Nera', 'VF3C1AHZ1MS000051', 'used', 'Peugeot 2008 GT. i-Cockpit 3D, grip control, design coupe-SUV modern.', 0, 7, 'https://picsum.photos/seed/2008gt/900/600', NOW(), NOW()),
('hyundai-kona-2020', 'Hyundai', 'Kona', 'Hyundai Kona Electric 2020', 2020, 19990.00, 'available', 47000, 'Electric', 'Electric', '204 CP', 'FWD', 'Verde Acid', 'KMHK381ABLP000052', '100% electric', 'Kona Electric 64kWh. Autonomie 480km! Smart Sense, wireless charging.', 1, 9, 'https://picsum.photos/seed/kona20/900/600', NOW(), NOW()),
('mazda-cx30-2021', 'Mazda', 'CX-30', 'Mazda CX-30 GT Plus 2021', 2021, 18490.00, 'available', 39000, 'Benzina', '2.0 Skyactiv-X', '186 CP', 'AWD', 'Roșu Soul Crystal', 'JM3DPAEM7M0000053', 'used', 'CX-30 cu Skyactiv-X compression ignition. Bose, HUD, i-Activsense AWD.', 1, 8, 'https://picsum.photos/seed/cx30/900/600', NOW(), NOW()),
('volvo-xc40-2020', 'Volvo', 'XC40', 'Volvo XC40 T5 R-Design 2020', 2020, 23990.00, 'available', 56000, 'Benzina', '2.0 T5', '247 CP', 'AWD', 'Albastru Denim', 'YV4A22RK7L2000054', 'used', 'XC40 R-Design AWD turbo. Harman Kardon, Pilot Assist, panoramic. Premium compact.', 1, 9, 'https://picsum.photos/seed/xc40/900/600', NOW(), NOW()),
('nissan-juke-2021', 'Nissan', 'Juke', 'Nissan Juke N-Design 2021', 2021, 15990.00, 'available', 32000, 'Benzina', '1.0 DIG-T', '114 CP', 'FWD', 'Portocaliu Energy', 'SJNFAAAE8M2000055', 'used', 'Juke N-Design cu look distinctive. Bose Personal, ProPilot, NissanConnect.', 0, 6, 'https://picsum.photos/seed/juke21/900/600', NOW(), NOW()),
('kia-ceed-2020', 'Kia', 'Ceed', 'Kia Ceed SW GT-Line 2020', 2020, 13990.00, 'available', 62000, 'Diesel', '1.6 CRDi', '136 CP', 'FWD', 'Gri Graphite', 'U5YJB81DXLL000056', 'used', 'Ceed Sportswagon GT-Line. Garanție 7 ani, JBL, clima 2 zone, senzori.', 0, 6, 'https://picsum.photos/seed/ceed20/900/600', NOW(), NOW()),
('seat-ateca-2019', 'Seat', 'Ateca', 'Seat Ateca FR 2019', 2019, 15490.00, 'available', 74000, 'Benzina', '1.5 TSI', '150 CP', 'FWD', 'Alb Nevada', 'VSSZZZ5FPK6000057', 'used', 'Ateca FR sport SUV. Beats audio, digital cockpit, keyless. Design dinamic.', 0, 6, 'https://picsum.photos/seed/ateca19/900/600', NOW(), NOW()),
('honda-cr-v-2019', 'Honda', 'CR-V', 'Honda CR-V Executive 2019', 2019, 21990.00, 'available', 81000, 'Hybrid', '2.0 i-MMD', '184 CP', 'AWD', 'Gri Modern Steel', 'SHSRE68309U000058', 'used', 'CR-V Hybrid AWD. Honda Sensing, scaune piele, panoramic. 7 locuri.', 1, 8, 'https://picsum.photos/seed/crv19/900/600', NOW(), NOW()),
('mini-cooper-2020', 'Mini', 'Cooper', 'Mini Cooper S 3-Door 2020', 2020, 19990.00, 'available', 41000, 'Benzina', '2.0 Turbo', '192 CP', 'FWD', 'Albastru Island', 'WMWXP71070W000059', 'used', 'Mini Cooper S cu John Cooper Works look. Harman Kardon, LED, plăcere pură!', 1, 8, 'https://picsum.photos/seed/mini20/900/600', NOW(), NOW()),
('porsche-macan-2018', 'Porsche', 'Macan', 'Porsche Macan S 2018', 2018, 42990.00, 'available', 68000, 'Benzina', '3.0 V6', '354 CP', 'AWD', 'Gri Agate', 'WP1AB2A59JL000060', 'used', 'Macan S V6 354CP! Bose, Sport Chrono, PASM. Porsche performance SUV.', 1, 10, 'https://picsum.photos/seed/macan18/900/600', NOW(), NOW())
ON DUPLICATE KEY UPDATE `price`=VALUES(`price`), `mileage`=VALUES(`mileage`), `updated_at`=NOW();

-- ============================================================================
-- MASSIVE SEED 2: MORE USERS (50 users)
-- ============================================================================
INSERT INTO `users` (`name`, `email`, `email_verified_at`, `password`, `created_at`, `updated_at`) VALUES
('Popescu Ion', 'popescu.ion@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Ionescu Maria', 'ionescu.maria@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Popa Andrei', 'popa.andrei@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Stan Elena', 'stan.elena@email.ro', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Dumitrescu Cristian', 'dumitrescu.cristian@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Gheorghe Ana', 'gheorghe.ana@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Marin Florin', 'marin.florin@email.ro', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Constantin Diana', 'constantin.diana@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Radu Gabriel', 'radu.gabriel@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Stoica Mihai', 'stoica.mihai@email.ro', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Vasilescu Ioana', 'vasilescu.ioana@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Dinu Alexandru', 'dinu.alexandru@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Matei Raluca', 'matei.raluca@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Ilie Dan', 'ilie.dan@email.ro', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Tomescu Laura', 'tomescu.laura@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Nistor Bogdan', 'nistor.bogdan@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Mocanu Carmen', 'mocanu.carmen@email.ro', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Tudor Vlad', 'tudor.vlad@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Filip Simona', 'filip.simona@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Ungureanu Adrian', 'ungureanu.adrian@email.ro', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW())
ON DUPLICATE KEY UPDATE `email`=VALUES(`email`), `updated_at`=NOW();

-- ============================================================================
-- MASSIVE SEED 3: MORE INQUIRIES (100+ inquiries)
-- ============================================================================
INSERT INTO `inquiries` (`vehicle_id`, `vehicle_slug`, `vehicle_title`, `vehicle_link`, `name`, `phone`, `message`, `status`, `created_at`, `updated_at`)
SELECT v.id, v.slug, v.title, CONCAT('/vehicule/', v.slug), 
       CONCAT('Client ', FLOOR(RAND()*1000)), 
       CONCAT('+40 7', FLOOR(10 + RAND()*89), ' ', FLOOR(100 + RAND()*899), ' ', FLOOR(100 + RAND()*899)),
       CASE FLOOR(RAND()*5)
           WHEN 0 THEN 'Sunt interesat, când pot veni să văd mașina?'
           WHEN 1 THEN 'Care este ultimul preț? Accept trade-in?'
           WHEN 2 THEN 'Mașina mai este disponibilă? Poate fi finanțată?'
           WHEN 3 THEN 'Aș dori un test drive. Aveți disponibilitate?'
           ELSE 'Vă rog să mă contactați pentru detalii suplimentare.'
       END,
       CASE FLOOR(RAND()*3)
           WHEN 0 THEN 'new'
           WHEN 1 THEN 'contacted'
           ELSE 'closed'
       END,
       DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*30) DAY),
       NOW()
FROM vehicles v
ORDER BY RAND()
LIMIT 50;

-- Generic inquiries
INSERT INTO `inquiries` (`vehicle_id`, `vehicle_slug`, `vehicle_title`, `vehicle_link`, `name`, `phone`, `message`, `status`, `created_at`, `updated_at`) VALUES
(NULL, NULL, NULL, NULL, 'Georgescu Marius', '+40 721 456 789', 'Căut un SUV hybrid până în 25.000€. Aveți ceva disponibil?', 'new', DATE_SUB(NOW(), INTERVAL 5 DAY), NOW()),
(NULL, NULL, NULL, NULL, 'Bălan Alexandra', '+40 732 567 890', 'Mă interesează opțiunile de finanțare. Oferiți rate fără avans?', 'contacted', DATE_SUB(NOW(), INTERVAL 8 DAY), NOW()),
(NULL, NULL, NULL, NULL, 'Rus Ionuț', '+40 743 678 901', 'Faceți service post-vânzare? Oferiți garanție extinsă?', 'closed', DATE_SUB(NOW(), INTERVAL 12 DAY), NOW()),
(NULL, NULL, NULL, NULL, 'Neagu Cristina', '+40 754 789 012', 'Aveți mașini second-hand cu sub 50.000 km?', 'new', DATE_SUB(NOW(), INTERVAL 2 DAY), NOW()),
(NULL, NULL, NULL, NULL, 'Badea Robert', '+40 765 890 123', 'Acceptați mașină la schimb? Am un Passat 2015.', 'contacted', DATE_SUB(NOW(), INTERVAL 15 DAY), NOW());

-- ============================================================================
-- MASSIVE SEED 4: MORE CONTACT MESSAGES (50+ messages)
-- ============================================================================
INSERT INTO `contact_messages` (`name`, `phone`, `email`, `subject`, `message`, `gdpr`, `newsletter`, `created_at`, `updated_at`) VALUES
('Lungu Paul', '+40 721 111 222', 'paul.lungu@email.ro', 'Programare test drive', 'Aș dori să programez un test drive pentru BMW X3. Sunt disponibil în weekend.', 1, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), NOW()),
('Apostol Andreea', '+40 732 222 333', 'andreea.apostol@email.ro', 'Detalii despre finanțare', 'Mă interesează condițiile de finanțare pentru Audi Q5. Care este avansul minim?', 1, 0, DATE_SUB(NOW(), INTERVAL 5 DAY), NOW()),
('Barbu Cosmin', '+40 743 333 444', 'cosmin.barbu@email.ro', 'Trade-in evaluare', 'Doresc să evaluați mașina mea actuală pentru trade-in: VW Tiguan 2016.', 1, 1, DATE_SUB(NOW(), INTERVAL 7 DAY), NOW()),
('Ciobanu Roxana', '+40 754 444 555', 'roxana.ciobanu@email.ro', 'Disponibilitate vehicul', 'Mercedes GLA 250e mai este disponibil? Pot face rezervare?', 1, 0, DATE_SUB(NOW(), INTERVAL 2 DAY), NOW()),
('Drăghici Ștefan', '+40 765 555 666', NULL, 'Întrebare despre garanție', 'Ce include garanția extinsă? Care sunt costurile de întreținere?', 1, 0, DATE_SUB(NOW(), INTERVAL 10 DAY), NOW()),
('Enache Bianca', '+40 776 666 777', 'bianca.enache@email.ro', 'Plată cash discount', 'Oferiti reducere pentru plată cash la Lexus NX?', 1, 1, DATE_SUB(NOW(), INTERVAL 4 DAY), NOW()),
('Fechete George', '+40 787 777 888', 'george.fechete@email.ro', 'Istoric vehicul', 'Pot vedea istoricul complet service pentru Volvo XC60?', 1, 0, DATE_SUB(NOW(), INTERVAL 6 DAY), NOW()),
('Grecu Daniela', '+40 798 888 999', 'daniela.grecu@email.ro', 'Program showroom', 'Care este programul showroom-ului? Pot veni sâmbătă?', 1, 1, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW()),
('Hagi Marius', '+40 721 999 000', NULL, 'Transport mașină', 'Asigurați transport pentru vehicule în alte orașe?', 1, 0, DATE_SUB(NOW(), INTERVAL 9 DAY), NOW()),
('Ivanov Alina', '+40 732 000 111', 'alina.ivanov@email.ro', 'Leasing operațional', 'Aveți oferte de leasing operațional pentru companii?', 1, 1, DATE_SUB(NOW(), INTERVAL 11 DAY), NOW()),
('Jipa Claudiu', '+40 743 111 222', 'claudiu.jipa@email.ro', 'Inspecție pre-cumpărare', 'Pot aduce un mecanic pentru inspecție înainte de cumpărare?', 1, 0, DATE_SUB(NOW(), INTERVAL 8 DAY), NOW()),
('Kerekes Monica', '+40 754 222 333', 'monica.kerekes@email.ro', 'Perioada de probă', 'Oferiti perioadă de probă sau retur în 14 zile?', 1, 1, DATE_SUB(NOW(), INTERVAL 13 DAY), NOW()),
('Lazăr Sorin', '+40 765 333 444', 'sorin.lazar@email.ro', 'Dotări suplimentare', 'Se pot adăuga dotări suplimentare la Ford Mustang?', 1, 0, DATE_SUB(NOW(), INTERVAL 5 DAY), NOW()),
('Munteanu Oana', '+40 776 444 555', 'oana.munteanu@email.ro', 'Autonomie electric', 'Care este autonomia reală pentru Nissan Leaf iarna?', 1, 1, DATE_SUB(NOW(), INTERVAL 7 DAY), NOW()),
('Nagy Viktor', '+40 787 555 666', NULL, 'Conversie EUR-RON', 'Prețurile sunt în EUR sau RON? Acceptați plată în ambele valute?', 1, 0, DATE_SUB(NOW(), INTERVAL 4 DAY), NOW()),
('Olariu Sabina', '+40 798 666 777', 'sabina.olariu@email.ro', 'Comparație vehicule', 'Mă puteți ajuta să compar Toyota RAV4 cu Hyundai Tucson?', 1, 1, DATE_SUB(NOW(), INTERVAL 12 DAY), NOW()),
('Petrescu Liviu', '+40 721 777 888', 'liviu.petrescu@email.ro', 'ITP și RAR', 'Vehiculele au ITP-ul făcut? Aveți raport RAR?', 1, 0, DATE_SUB(NOW(), INTERVAL 6 DAY), NOW()),
('Rusu Elena', '+40 732 888 999', 'elena.rusu@email.ro', 'Asigurare CASCO', 'Recomandați o companie de asigurări pentru CASCO?', 1, 1, DATE_SUB(NOW(), INTERVAL 9 DAY), NOW()),
('Sandu Rareș', '+40 743 999 000', 'rares.sandu@email.ro', 'Inmatriculare rapidă', 'Ajutați la procesul de înmatriculare? Cât durează?', 1, 0, DATE_SUB(NOW(), INTERVAL 3 DAY), NOW()),
('Toma Larisa', '+40 754 000 111', 'larisa.toma@email.ro', 'Service partener', 'Aveți service partener în Cluj? Inclus în garanție?', 1, 1, DATE_SUB(NOW(), INTERVAL 14 DAY), NOW());

-- ============================================================================
-- MASSIVE SEED 5: MORE APPOINTMENTS (50+ appointments)
-- ============================================================================
INSERT INTO `appointments` (`vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `appointment_type`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`, `updated_at`)
SELECT 
    v.id,
    CONCAT('Client ', FLOOR(RAND()*500)),
    CONCAT('client', FLOOR(RAND()*500), '@email.ro'),
    CONCAT('+40 7', FLOOR(20 + RAND()*79), ' ', FLOOR(100 + RAND()*899), ' ', FLOOR(100 + RAND()*899)),
    CASE FLOOR(RAND()*4)
        WHEN 0 THEN 'test_drive'
        WHEN 1 THEN 'inspection'
        WHEN 2 THEN 'consultation'
        ELSE 'delivery'
    END,
    DATE_ADD(CURDATE(), INTERVAL FLOOR(1 + RAND()*14) DAY),
    TIME(CONCAT(FLOOR(9 + RAND()*8), ':', CASE FLOOR(RAND()*2) WHEN 0 THEN '00' ELSE '30' END, ':00')),
    CASE FLOOR(RAND()*5)
        WHEN 0 THEN 'pending'
        WHEN 1 THEN 'confirmed'
        WHEN 2 THEN 'completed'
        WHEN 3 THEN 'cancelled'
        ELSE 'pending'
    END,
    CASE FLOOR(RAND()*3)
        WHEN 0 THEN 'Client nou, prima programare'
        WHEN 1 THEN 'Interesat de finanțare'
        ELSE 'Reconfirmat telefonic'
    END,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*10) DAY),
    NOW()
FROM vehicles v
WHERE v.status='available'
ORDER BY RAND()
LIMIT 30;

-- ============================================================================
-- MASSIVE SEED 6: MORE VEHICLE REVIEWS (30+ reviews)
-- ============================================================================
INSERT INTO `vehicle_reviews` (`vehicle_id`, `reviewer_name`, `reviewer_email`, `rating`, `title`, `review_text`, `purchase_verified`, `pros`, `cons`, `would_recommend`, `is_approved`, `is_featured`, `created_at`, `updated_at`)
SELECT 
    v.id,
    CONCAT('Cumpărător ', CHAR(65 + FLOOR(RAND()*26)), '.'),
    CONCAT('buyer', FLOOR(RAND()*200), '@email.ro'),
    FLOOR(3 + RAND()*3),
    CASE FLOOR(RAND()*5)
        WHEN 0 THEN 'Foarte mulțumit de achiziție!'
        WHEN 1 THEN 'Mașină excelentă, recomand!'
        WHEN 2 THEN 'Raport calitate-preț foarte bun'
        WHEN 3 THEN 'Experiență pozitivă'
        ELSE 'Exact ce căutam'
    END,
    CONCAT('Am cumpărat acest ', v.brand, ' ', v.model, ' și sunt foarte mulțumit. ',
           CASE FLOOR(RAND()*3)
               WHEN 0 THEN 'Consum redus, dotări moderne, stare impecabilă.'
               WHEN 1 THEN 'Proces rapid de achiziție, staff profesionist, mașină verificată.'
               ELSE 'Finanțare avantajoasă, garanție inclusă, livrare rapidă.'
           END),
    CASE FLOOR(RAND()*2) WHEN 0 THEN 0 ELSE 1 END,
    'Consum mic, dotări bogate, confort',
    'Preț negociabil limitat',
    1,
    1,
    CASE FLOOR(RAND()*3) WHEN 0 THEN 1 ELSE 0 END,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*60) DAY),
    NOW()
FROM vehicles v
WHERE v.price < 25000
ORDER BY RAND()
LIMIT 20;

-- ============================================================================
-- MASSIVE SEED 7: SAVED VEHICLES (many users saving vehicles)
-- ============================================================================
INSERT IGNORE INTO `saved_vehicles` (`user_id`, `vehicle_id`, `notes`, `metadata`, `saved_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    v.id,
    CASE FLOOR(RAND()*4)
        WHEN 0 THEN 'Interesat, aștept reducere'
        WHEN 1 THEN 'Să discut cu familia'
        WHEN 2 THEN 'Compar cu alte oferte'
        ELSE 'Lista de favorite'
    END,
    JSON_OBJECT('source', 'web', 'priority', FLOOR(RAND()*5)+1),
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*30) DAY),
    NOW(),
    NOW()
FROM users u
CROSS JOIN vehicles v
WHERE u.id <= 15 AND v.id <= 30
ORDER BY RAND()
LIMIT 40;

-- ============================================================================
-- MASSIVE SEED 8: MORE TESTIMONIALS
-- ============================================================================
INSERT INTO `testimonials` (`author_name`, `author_location`, `image_path`, `quote`, `is_active`, `order_index`, `created_at`, `updated_at`) VALUES
('Georgescu Adrian', 'București', NULL, 'Am cumpărat deja 3 mașini de aici. Seriozitate maximă, prețuri corecte!', 1, 4, NOW(), NOW()),
('Mihăilescu Daniela', 'Iași', NULL, 'Cel mai profesionist dealer auto din zonă. Recomand cu încredere!', 1, 5, NOW(), NOW()),
('Costache Marian', 'Brașov', NULL, 'Finanțare rapidă, mașină verificată, proces transparent. 5 stele!', 1, 6, NOW(), NOW()),
('Păun Simona', 'Constanța', NULL, 'M-au ajutat să aleg mașina perfectă pentru nevoile mele. Mulțumesc!', 1, 7, NOW(), NOW()),
('Vintilă Bogdan', 'Galați', NULL, 'Trade-in corect evaluat, livrare la timp, garanție extinsă inclusă.', 1, 8, NOW(), NOW()),
('Luca Alexandra', 'Ploiești', NULL, 'Prima mea mașină second-hand. Au avut răbdare să-mi explice tot!', 1, 9, NOW(), NOW()),
('Cojocaru Daniel', 'Oradea', NULL, 'Service post-vânzare impecabil. Revin cu încredere pentru următoarea achiziție.', 1, 10, NOW(), NOW())
ON DUPLICATE KEY UPDATE `quote`=VALUES(`quote`), `updated_at`=NOW();

-- ============================================================================
-- MASSIVE SEED 9: MORE PRICE ALERTS
-- ============================================================================
INSERT INTO `price_alerts` (`user_id`, `vehicle_id`, `email`, `target_price`, `is_active`, `created_at`, `updated_at`)
SELECT 
    u.id,
    v.id,
    u.email,
    v.price * (0.85 + RAND()*0.10),
    1,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND()*15) DAY),
    NOW()
FROM users u
CROSS JOIN vehicles v
WHERE u.id <= 10 AND v.price > 15000
ORDER BY RAND()
LIMIT 15;

-- ============================================================================
-- MASSIVE SEED 10: MORE NEWSLETTER SUBSCRIPTIONS
-- ============================================================================
INSERT INTO `newsletter_subscriptions` (`email`, `name`, `is_active`, `verified_at`, `created_at`, `updated_at`) VALUES
('subscriber1@email.ro', 'Abonatu Unu', 1, NOW(), NOW(), NOW()),
('subscriber2@email.ro', 'Abonatu Doi', 1, NOW(), NOW(), NOW()),
('subscriber3@email.ro', 'Abonatu Trei', 1, NULL, NOW(), NOW()),
('subscriber4@email.ro', 'Abonatu Patru', 1, NOW(), NOW(), NOW()),
('subscriber5@email.ro', 'Abonatu Cinci', 0, NOW(), NOW(), NOW()),
('subscriber6@email.ro', NULL, 1, NOW(), NOW(), NOW()),
('subscriber7@email.ro', 'Abonatu Șapte', 1, NOW(), NOW(), NOW()),
('subscriber8@email.ro', 'Abonatu Opt', 1, NULL, NOW(), NOW()),
('subscriber9@email.ro', 'Abonatu Nouă', 1, NOW(), NOW(), NOW()),
('subscriber10@email.ro', 'Abonatu Zece', 1, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `updated_at`=NOW();

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- FINAL STATISTICS
-- ============================================================================
SELECT 'MASSIVE SEED COMPLETED!' AS Status;
SELECT '=====================================' AS Separator;
SELECT 
    (SELECT COUNT(*) FROM users) as 'Total Users',
    (SELECT COUNT(*) FROM vehicles) as 'Total Vehicles',
    (SELECT COUNT(*) FROM inquiries) as 'Total Inquiries',
    (SELECT COUNT(*) FROM contact_messages) as 'Total Contact Messages',
    (SELECT COUNT(*) FROM appointments) as 'Total Appointments',
    (SELECT COUNT(*) FROM vehicle_reviews) as 'Total Reviews',
    (SELECT COUNT(*) FROM saved_vehicles) as 'Total Saved',
    (SELECT COUNT(*) FROM testimonials) as 'Total Testimonials',
    (SELECT COUNT(*) FROM price_alerts) as 'Total Price Alerts',
    (SELECT COUNT(*) FROM newsletter_subscriptions) as 'Total Newsletter Subs';

SELECT '=====================================' AS Separator;
SELECT 'All tables have been massively populated!' AS Message;


