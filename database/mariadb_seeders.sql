-- ============================================================================
-- AUTO E-COMMERCE - Complete Seed Data for All Tables
-- ============================================================================
-- Run this AFTER mariadb_enhanced_schema.sql
-- This populates all tables with realistic Romanian market data

SET NAMES utf8mb4;
USE `auto_ecommerce_mariadb`;

-- ============================================================================
-- SEED 1: BRANDS (popular brands in Romanian market)
-- ============================================================================
INSERT INTO `brands` (`name`, `slug`, `country_origin`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
('Volkswagen', 'volkswagen', 'Germany', 'Lider german în producția de automobile', 1, NOW(), NOW()),
('BMW', 'bmw', 'Germany', 'Producător premium german de automobile', 1, NOW(), NOW()),
('Mercedes-Benz', 'mercedes-benz', 'Germany', 'Brand premium german, sinonim cu luxul', 1, NOW(), NOW()),
('Audi', 'audi', 'Germany', 'Brand premium din grupul Volkswagen', 1, NOW(), NOW()),
('Dacia', 'dacia', 'Romania', 'Brand românesc, parte din grupul Renault', 1, NOW(), NOW()),
('Ford', 'ford', 'USA', 'Producător american cu istorie lungă', 1, NOW(), NOW()),
('Toyota', 'toyota', 'Japan', 'Cel mai mare producător japonez', 1, NOW(), NOW()),
('Skoda', 'skoda', 'Czech Republic', 'Brand ceh, parte din grupul Volkswagen', 1, NOW(), NOW()),
('Renault', 'renault', 'France', 'Producător francez popular în Europa', 1, NOW(), NOW()),
('Opel', 'opel', 'Germany', 'Brand german, parte din grupul Stellantis', 1, NOW(), NOW()),
('Peugeot', 'peugeot', 'France', 'Brand francez cu design distinctiv', 1, NOW(), NOW()),
('Hyundai', 'hyundai', 'South Korea', 'Producător sud-coreean în creștere', 1, NOW(), NOW()),
('Mazda', 'mazda', 'Japan', 'Brand japonez cu design sportiv', 1, NOW(), NOW()),
('Volvo', 'volvo', 'Sweden', 'Brand suedez focusat pe siguranță', 1, NOW(), NOW()),
('Nissan', 'nissan', 'Japan', 'Producător japonez cu gamă variată', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `updated_at`=NOW();

-- ============================================================================
-- SEED 2: MODELS (popular models per brand)
-- ============================================================================
INSERT INTO `models` (`brand_id`, `name`, `slug`, `body_type`, `generation`, `production_start`, `is_active`, `created_at`, `updated_at`)
SELECT b.id, 'Golf', 'golf', 'hatchback', 'Mk8', 2019, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='volkswagen'
UNION ALL SELECT b.id, 'Passat', 'passat', 'sedan', 'B8', 2014, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='volkswagen'
UNION ALL SELECT b.id, 'Tiguan', 'tiguan', 'suv', 'Mk2', 2016, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='volkswagen'
UNION ALL SELECT b.id, '3 Series', '3-series', 'sedan', 'G20', 2018, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='bmw'
UNION ALL SELECT b.id, '5 Series', '5-series', 'sedan', 'G30', 2016, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='bmw'
UNION ALL SELECT b.id, 'X3', 'x3', 'suv', 'G01', 2017, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='bmw'
UNION ALL SELECT b.id, 'C-Class', 'c-class', 'sedan', 'W205', 2014, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='mercedes-benz'
UNION ALL SELECT b.id, 'E-Class', 'e-class', 'sedan', 'W213', 2016, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='mercedes-benz'
UNION ALL SELECT b.id, 'GLC', 'glc', 'suv', 'X253', 2015, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='mercedes-benz'
UNION ALL SELECT b.id, 'A4', 'a4', 'sedan', 'B9', 2015, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='audi'
UNION ALL SELECT b.id, 'A6', 'a6', 'sedan', 'C8', 2018, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='audi'
UNION ALL SELECT b.id, 'Q5', 'q5', 'suv', 'FY', 2016, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='audi'
UNION ALL SELECT b.id, 'Duster', 'duster', 'suv', 'Gen2', 2017, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='dacia'
UNION ALL SELECT b.id, 'Sandero', 'sandero', 'hatchback', 'Gen3', 2020, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='dacia'
UNION ALL SELECT b.id, 'Logan', 'logan', 'sedan', 'Gen3', 2020, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='dacia'
UNION ALL SELECT b.id, 'Focus', 'focus', 'hatchback', 'Mk4', 2018, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='ford'
UNION ALL SELECT b.id, 'Kuga', 'kuga', 'suv', 'Mk3', 2019, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='ford'
UNION ALL SELECT b.id, 'Corolla', 'corolla', 'sedan', 'E210', 2018, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='toyota'
UNION ALL SELECT b.id, 'RAV4', 'rav4', 'suv', 'XA50', 2018, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='toyota'
UNION ALL SELECT b.id, 'Octavia', 'octavia', 'sedan', 'Mk4', 2019, 1, NOW(), NOW() FROM `brands` b WHERE b.slug='skoda'
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `updated_at`=NOW();

-- ============================================================================
-- SEED 3: VEHICLE CATEGORIES
-- ============================================================================
INSERT INTO `vehicle_categories` (`name`, `slug`, `description`, `icon`, `order_index`, `is_active`, `created_at`, `updated_at`) VALUES
('SUV & Crossover', 'suv-crossover', 'SUV-uri și crossover-uri pentru familie și aventură', 'bi-car-front-fill', 1, 1, NOW(), NOW()),
('Sedan & Limuzine', 'sedan-limuzine', 'Sedanuri elegante și limuzine executive', 'bi-car', 2, 1, NOW(), NOW()),
('Hatchback', 'hatchback', 'Mașini compacte ideale pentru oraș', 'bi-car-front', 3, 1, NOW(), NOW()),
('Break & Combi', 'break-combi', 'Break-uri spațioase pentru familie', 'bi-truck', 4, 1, NOW(), NOW()),
('Coupe & Cabrio', 'coupe-cabrio', 'Mașini sport și decapotabile', 'bi-sun', 5, 1, NOW(), NOW()),
('Electric & Hybrid', 'electric-hybrid', 'Mașini electrice și hibride eco', 'bi-lightning-charge', 6, 1, NOW(), NOW()),
('Lux & Premium', 'lux-premium', 'Mașini premium și de lux', 'bi-gem', 7, 1, NOW(), NOW()),
('Accesibile', 'accesibile', 'Mașini la prețuri accesibile', 'bi-tag', 8, 1, NOW(), NOW()),
('4x4 & Off-road', '4x4-offroad', 'Mașini cu tracțiune integrală', 'bi-compass', 9, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `updated_at`=NOW();

-- ============================================================================
-- SEED 4: FINANCING OPTIONS
-- ============================================================================
INSERT INTO `financing_options` (`name`, `provider`, `min_down_payment_percent`, `max_term_months`, `interest_rate_min`, `interest_rate_max`, `min_amount`, `max_amount`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
('Finanțare Standard', 'BCR', 10.00, 84, 6.99, 9.99, 3000.00, 50000.00, 'Credit auto cu dobândă fixă de la BCR. Avans minim 10%, termen până la 7 ani.', 1, NOW(), NOW()),
('Finanțare Premium', 'BRD', 15.00, 60, 5.99, 8.49, 5000.00, 100000.00, 'Credit auto premium de la BRD. Condiții avantajoase pentru mașini noi și second-hand premium.', 1, NOW(), NOW()),
('Leasing Operațional', 'UniCredit Leasing', 0.00, 48, 4.50, 7.50, 10000.00, 150000.00, 'Leasing operațional pentru companii și PFA. Fără avans, deductibilitate fiscală.', 1, NOW(), NOW()),
('Credit Nevoi Personale', 'ING Bank', 0.00, 60, 7.99, 11.99, 2000.00, 30000.00, 'Credit de nevoi personale pentru achiziție auto. Fără ipotecă, aprobat rapid.', 1, NOW(), NOW()),
('Finanțare 0% Dobândă', 'Partener Dealer', 20.00, 12, 0.00, 0.00, 5000.00, 25000.00, 'Ofertă specială: 0% dobândă pentru max 12 luni! Avans 20%, doar pentru mașini selectate.', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `provider`=VALUES(`provider`), `updated_at`=NOW();

-- ============================================================================
-- SEED 5: WARRANTY PLANS
-- ============================================================================
INSERT INTO `warranty_plans` (`name`, `description`, `duration_months`, `mileage_limit_km`, `coverage_details`, `price`, `is_transferable`, `is_active`, `created_at`, `updated_at`) VALUES
('Garanție Bază', 'Garanție standard pentru componente esențiale', 12, 50000, 'Acoperă motor, transmisie, sistem electric de bază', 499.00, 1, 1, NOW(), NOW()),
('Garanție Extinsă', 'Garanție completă pentru liniște totală', 24, 100000, 'Acoperă motor, transmisie, sistem electric, suspensie, climatizare, electronică', 1299.00, 1, 1, NOW(), NOW()),
('Garanție Premium', 'Protecție maximă inclusiv anvelope și service gratuit', 36, 150000, 'Acoperire completă: motor, transmisie, toate sistemele, anvelope, service periodic gratuit', 2499.00, 1, 1, NOW(), NOW()),
('Garanție Motor+Transmisie', 'Protecție specială pentru motor și cutie de viteze', 18, 75000, 'Acoperă exclusiv motor și transmisie cu piese originale', 799.00, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `price`=VALUES(`price`), `updated_at`=NOW();

-- ============================================================================
-- SEED 6: EXTENDED VEHICLE DATA (more realistic inventory)
-- ============================================================================
-- Add more vehicles to the existing table
INSERT INTO `vehicles` (`slug`, `brand`, `model`, `title`, `year`, `price`, `status`, `mileage`, `fuel`, `engine`, `power`, `drivetrain`, `color`, `vin`, `condition`, `description`, `featured`, `cover_image`, `created_at`, `updated_at`) VALUES
('skoda-octavia-2020', 'Skoda', 'Octavia', 'Skoda Octavia Combi 2020', 2020, 14990.00, 'available', 78000, 'Diesel', '2.0 TDI', '150 CP', 'FWD', 'Gri', 'TMBJM7NE5L0000001', 'used', 'Skoda Octavia Combi, spațioasă și economică. Istoric complet service. Ideal familie.', 1, 'https://picsum.photos/seed/skoda1/900/600', NOW(), NOW()),
('toyota-rav4-2019', 'Toyota', 'RAV4', 'Toyota RAV4 Hybrid 2019', 2019, 22490.00, 'available', 65000, 'Hybrid', '2.5 Hybrid', '218 CP', 'AWD', 'Roșu', 'JTMRFREVXKJ000002', 'used', 'Toyota RAV4 Hybrid AWD. Fiabilitate legendară, consum redus. O singură proprietară.', 1, 'https://picsum.photos/seed/toyota1/900/600', NOW(), NOW()),
('ford-kuga-2018', 'Ford', 'Kuga', 'Ford Kuga ST-Line 2018', 2018, 13790.00, 'available', 112000, 'Diesel', '2.0 TDCi', '150 CP', 'AWD', 'Albastru', '1FMCU0HD8JUA00003', 'used', 'Ford Kuga ST-Line cu pachet sport. Dotări bogate, confort maxim.', 0, 'https://picsum.photos/seed/ford1/900/600', NOW(), NOW()),
('renault-clio-2021', 'Renault', 'Clio', 'Renault Clio Intens 2021', 2021, 11990.00, 'available', 32000, 'Benzina', '1.0 TCe', '100 CP', 'FWD', 'Alb', 'VF1RJA00H65000004', 'used', 'Renault Clio generația nouă. Km reali, garanție 12 luni inclusă.', 0, 'https://picsum.photos/seed/renault1/900/600', NOW(), NOW()),
('opel-astra-2019', 'Opel', 'Astra', 'Opel Astra Sports Tourer 2019', 2019, 12490.00, 'available', 89000, 'Diesel', '1.5 Turbo D', '122 CP', 'FWD', 'Negru', 'W0L0SDL08K0000005', 'used', 'Opel Astra break. Spațiu generos, consum mic. Service la zi.', 0, 'https://picsum.photos/seed/opel1/900/600', NOW(), NOW()),
('peugeot-3008-2020', 'Peugeot', '3008', 'Peugeot 3008 GT Line 2020', 2020, 18990.00, 'available', 71000, 'Diesel', '2.0 BlueHDi', '180 CP', 'FWD', 'Gri Metalizat', 'VF3LCYHZPLS000006', 'used', 'Peugeot 3008 GT Line cu i-Cockpit. Design modern, tehnologie avansată.', 1, 'https://picsum.photos/seed/peugeot1/900/600', NOW(), NOW()),
('hyundai-tucson-2021', 'Hyundai', 'Tucson', 'Hyundai Tucson Premium 2021', 2021, 19990.00, 'available', 45000, 'Hybrid', '1.6 T-GDI Hybrid', '230 CP', 'AWD', 'Alb Perlat', 'KMHJ381CBMU000007', 'used', 'Hyundai Tucson Hybrid 4WD. Garanție producător 5 ani activă. Stare impecabilă.', 1, 'https://picsum.photos/seed/hyundai1/900/600', NOW(), NOW()),
('mazda-cx5-2019', 'Mazda', 'CX-5', 'Mazda CX-5 Revolution 2019', 2019, 16990.00, 'available', 82000, 'Diesel', '2.2 Skyactiv-D', '184 CP', 'AWD', 'Roșu Soul', 'JM3KFBCM0K0000008', 'used', 'Mazda CX-5 cu design Kodo. Plăcere de condus garantată. Full options.', 0, 'https://picsum.photos/seed/mazda1/900/600', NOW(), NOW()),
('volvo-xc60-2018', 'Volvo', 'XC60', 'Volvo XC60 Momentum 2018', 2018, 21990.00, 'available', 98000, 'Diesel', '2.0 D4', '190 CP', 'AWD', 'Gri', 'YV1A22RK3J1000009', 'used', 'Volvo XC60 cu siguranță scandinavă. Pilot automat, scaune ventilate. Premium.', 1, 'https://picsum.photos/seed/volvo1/900/600', NOW(), NOW()),
('nissan-qashqai-2020', 'Nissan', 'Qashqai', 'Nissan Qashqai Tekna 2020', 2020, 15490.00, 'available', 67000, 'Benzina', '1.3 DIG-T', '140 CP', 'FWD', 'Negru', 'SJNFAAJ11U2000010', 'used', 'Nissan Qashqai Tekna top echipare. Proiectoare LED, camera 360°, navi.', 0, 'https://picsum.photos/seed/nissan1/900/600', NOW(), NOW()),
('vw-polo-2021', 'Volkswagen', 'Polo', 'Volkswagen Polo Highline 2021', 2021, 12990.00, 'available', 38000, 'Benzina', '1.0 TSI', '95 CP', 'FWD', 'Albastru', 'WVWZZZ6RZMU000011', 'used', 'VW Polo generație nouă. Consum mic, tehnologie modernă. Perfect oraș.', 0, 'https://picsum.photos/seed/vwpolo1/900/600', NOW(), NOW()),
('bmw-x1-2019', 'BMW', 'X1', 'BMW X1 sDrive18d 2019', 2019, 19990.00, 'available', 74000, 'Diesel', '2.0d', '150 CP', 'RWD', 'Alb Alpin', 'WBAHC3108K7000012', 'used', 'BMW X1 cu interior premium. Head-up display, scaune sport. Impecabil.', 1, 'https://picsum.photos/seed/bmwx1/900/600', NOW(), NOW()),
('mercedes-gla-2020', 'Mercedes-Benz', 'GLA', 'Mercedes-Benz GLA 200 2020', 2020, 24990.00, 'available', 52000, 'Benzina', '1.3 Turbo', '163 CP', 'FWD', 'Negru Obsidian', 'WDD2430391N000013', 'used', 'Mercedes GLA cu MBUX. Design modern, confort superior. Stare perfectă.', 1, 'https://picsum.photos/seed/mbgla/900/600', NOW(), NOW()),
('audi-q3-2019', 'Audi', 'Q3', 'Audi Q3 S-Line 2019', 2019, 22490.00, 'available', 69000, 'Diesel', '2.0 TDI', '150 CP', 'AWD', 'Gri Nardo', '8U000000000000014', 'used', 'Audi Q3 S-Line Quattro. Virtual Cockpit, Bang&Olufsen. Full LED.', 1, 'https://picsum.photos/seed/audiq3/900/600', NOW(), NOW()),
('dacia-spring-2022', 'Dacia', 'Spring', 'Dacia Spring Electric 2022', 2022, 13990.00, 'available', 18000, 'Electric', 'Electric Motor', '44 CP', 'FWD', 'Portocaliu', 'UU1SADAB0NL000015', 'used', 'Dacia Spring 100% electric. Autonomie 230km. Costuri de întreținere minime.', 0, 'https://picsum.photos/seed/spring/900/600', NOW(), NOW())
ON DUPLICATE KEY UPDATE `brand`=VALUES(`brand`), `model`=VALUES(`model`), `price`=VALUES(`price`), `updated_at`=NOW();

-- ============================================================================
-- SEED 7: VEHICLE SPECIFICATIONS (for new vehicles)
-- ============================================================================
INSERT INTO `vehicle_specifications` (`vehicle_id`, `engine`, `engine_capacity`, `power_hp`, `power_kw`, `fuel_type`, `transmission`, `drivetrain`, `cylinders`, `fuel_consumption_combined`, `euro_standard`, `created_at`, `updated_at`)
SELECT v.id, '2.0 TDI', 1968, 150, 110, 'diesel', 'manual', 'fwd', 4, 4.8, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, '2.5 Hybrid', 2487, 218, 160, 'hybrid', 'automatic', 'awd', 4, 5.9, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, '2.0 TDCi', 1997, 150, 110, 'diesel', 'automatic', 'awd', 4, 5.7, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='ford-kuga-2018'
UNION ALL SELECT v.id, '1.0 TCe', 999, 100, 74, 'gasoline', 'manual', 'fwd', 3, 5.2, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='renault-clio-2021'
UNION ALL SELECT v.id, '1.5 Turbo D', 1499, 122, 90, 'diesel', 'manual', 'fwd', 4, 4.5, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='opel-astra-2019'
UNION ALL SELECT v.id, '2.0 BlueHDi', 1997, 180, 132, 'diesel', 'automatic', 'fwd', 4, 5.6, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='peugeot-3008-2020'
UNION ALL SELECT v.id, '1.6 T-GDI Hybrid', 1598, 230, 169, 'hybrid', 'automatic', 'awd', 4, 6.3, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='hyundai-tucson-2021'
UNION ALL SELECT v.id, '2.2 Skyactiv-D', 2191, 184, 135, 'diesel', 'automatic', 'awd', 4, 5.9, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='mazda-cx5-2019'
UNION ALL SELECT v.id, '2.0 D4', 1969, 190, 140, 'diesel', 'automatic', 'awd', 4, 5.8, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='volvo-xc60-2018'
UNION ALL SELECT v.id, '1.3 DIG-T', 1332, 140, 103, 'gasoline', 'automatic', 'fwd', 4, 6.1, 'Euro 6', NOW(), NOW() FROM vehicles v WHERE v.slug='nissan-qashqai-2020'
ON DUPLICATE KEY UPDATE `engine`=VALUES(`engine`), `power_hp`=VALUES(`power_hp`), `updated_at`=NOW();

-- ============================================================================
-- SEED 8: VEHICLE PRICING (for new vehicles)
-- ============================================================================
INSERT INTO `vehicle_pricing` (`vehicle_id`, `listing_price`, `original_price`, `has_offer`, `price_negotiable`, `vat_included`, `created_at`, `updated_at`)
SELECT v.id, v.price, v.price * 1.08, 0, 1, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, v.price, v.price * 1.12, 1, 1, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, v.price, v.price, 0, 1, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='ford-kuga-2018'
UNION ALL SELECT v.id, v.price, v.price * 1.05, 0, 1, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='renault-clio-2021'
UNION ALL SELECT v.id, v.price, v.price, 0, 1, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='opel-astra-2019'
ON DUPLICATE KEY UPDATE `listing_price`=VALUES(`listing_price`), `updated_at`=NOW();

-- Update some existing vehicles with offers
UPDATE `vehicle_pricing` vp
JOIN `vehicles` v ON vp.vehicle_id = v.id
SET vp.has_offer = 1,
    vp.offer_price = vp.listing_price * 0.92,
    vp.offer_type = 'seasonal',
    vp.offer_description = 'Ofertă specială de primăvară! Reducere 8%',
    vp.offer_expires_at = DATE_ADD(CURDATE(), INTERVAL 30 DAY)
WHERE v.slug IN ('toyota-rav4-2019', 'peugeot-3008-2020', 'hyundai-tucson-2021');

-- ============================================================================
-- SEED 9: VEHICLE FEATURES (sample features for vehicles)
-- ============================================================================
INSERT INTO `vehicle_features` (`vehicle_id`, `feature_category`, `feature_name`, `is_standard`, `created_at`, `updated_at`)
SELECT v.id, 'safety', 'ABS', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'safety', 'ESP', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'safety', 'Airbag-uri frontale și laterale', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'comfort', 'Climatizare automată', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'comfort', 'Scaune încălzite', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'technology', 'Sistem navigație', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'technology', 'Senzori parcare față/spate', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'technology', 'Cameră marsarier', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'exterior', 'Jante aliaj 17"', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'exterior', 'Faruri LED', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'safety', 'Toyota Safety Sense', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, 'technology', 'JBL Premium Sound', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, 'comfort', 'Scaune piele', 1, NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
ON DUPLICATE KEY UPDATE `feature_name`=VALUES(`feature_name`), `updated_at`=NOW();

-- ============================================================================
-- SEED 10: SERVICE HISTORY (maintenance records)
-- ============================================================================
INSERT INTO `service_history` (`vehicle_id`, `service_date`, `mileage_at_service`, `service_type`, `service_description`, `service_provider`, `cost`, `created_at`, `updated_at`)
SELECT v.id, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), 70000, 'regular_maintenance', 'Revizie completă: ulei motor, filtre, verificări', 'Skoda Service Oficial', 650.00, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, DATE_SUB(CURDATE(), INTERVAL 12 MONTH), 62000, 'regular_maintenance', 'Revizie tehnică anuală + ITP', 'Skoda Service Oficial', 850.00, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, DATE_SUB(CURDATE(), INTERVAL 18 MONTH), 55000, 'tire_change', 'Montare anvelope de iarnă', 'Anvelopist partener', 200.00, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, DATE_SUB(CURDATE(), INTERVAL 4 MONTH), 62000, 'regular_maintenance', 'Service Hibrid: baterie, motor electric, motor termic', 'Toyota Service Oficial', 1200.00, NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, DATE_SUB(CURDATE(), INTERVAL 8 MONTH), 105000, 'repair', 'Înlocuire placuțe frână față', 'Ford Service', 420.00, NOW(), NOW() FROM vehicles v WHERE v.slug='ford-kuga-2018'
ON DUPLICATE KEY UPDATE `service_description`=VALUES(`service_description`), `updated_at`=NOW();

-- ============================================================================
-- SEED 11: APPOINTMENTS (sample test drives & consultations)
-- ============================================================================
INSERT INTO `appointments` (`vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `appointment_type`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`, `updated_at`)
SELECT v.id, 'Alexandru Popescu', 'alex.popescu@email.ro', '+40 722 111 222', 'test_drive', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', 'confirmed', 'Interesat de finanțare', NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, 'Maria Ionescu', 'maria.ion@email.ro', '+40 731 333 444', 'test_drive', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:30:00', 'pending', 'Prima mașină, necesită consultanță', NOW(), NOW() FROM vehicles v WHERE v.slug='renault-clio-2021'
UNION ALL SELECT v.id, 'Andrei Vasilescu', 'andrei.v@company.ro', '+40 744 555 666', 'inspection', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', 'confirmed', 'Vine cu mecanic personal', NOW(), NOW() FROM vehicles v WHERE v.slug='bmw-x1-2019'
UNION ALL SELECT v.id, 'Elena Dumitrescu', 'elena.d@email.ro', '+40 755 777 888', 'consultation', CURDATE(), '11:00:00', 'completed', 'Interested in warranty options', NOW(), NOW() FROM vehicles v WHERE v.slug='volvo-xc60-2018'
UNION ALL SELECT v.id, 'Cristian Marin', 'c.marin@email.ro', '+40 766 999 000', 'test_drive', DATE_SUB(CURDATE(), INTERVAL 2 DAY), '15:00:00', 'no_show', 'Nu s-a prezentat, nu a anunțat', NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
ON DUPLICATE KEY UPDATE `customer_name`=VALUES(`customer_name`), `updated_at`=NOW();

-- ============================================================================
-- SEED 12: FINANCING APPLICATIONS
-- ============================================================================
INSERT INTO `financing_applications` (`vehicle_id`, `financing_option_id`, `customer_name`, `customer_email`, `customer_phone`, `requested_amount`, `down_payment`, `term_months`, `monthly_income`, `employment_status`, `status`, `created_at`, `updated_at`)
SELECT v.id, fo.id, 'Ion Georgescu', 'ion.g@email.ro', '+40 722 121 212', 18000.00, 4000.00, 60, 4500.00, 'employed', 'approved', NOW(), NOW()
FROM vehicles v, financing_options fo WHERE v.slug='toyota-rav4-2019' AND fo.name='Finanțare Standard' LIMIT 1;

INSERT INTO `financing_applications` (`vehicle_id`, `financing_option_id`, `customer_name`, `customer_email`, `customer_phone`, `requested_amount`, `down_payment`, `term_months`, `monthly_income`, `employment_status`, `status`, `created_at`, `updated_at`)
SELECT v.id, fo.id, 'Mihaela Stan', 'mihaela.stan@email.ro', '+40 733 232 323', 12000.00, 2000.00, 48, 3200.00, 'employed', 'under_review', NOW(), NOW()
FROM vehicles v, financing_options fo WHERE v.slug='skoda-octavia-2020' AND fo.name='Finanțare Standard' LIMIT 1;

INSERT INTO `financing_applications` (`vehicle_id`, `financing_option_id`, `customer_name`, `customer_email`, `customer_phone`, `requested_amount`, `down_payment`, `term_months`, `monthly_income`, `employment_status`, `status`, `created_at`, `updated_at`)
SELECT v.id, fo.id, 'Gabriel Popa', 'gabriel.popa@company.ro', '+40 744 343 434', 20000.00, 8000.00, 36, 8500.00, 'self_employed', 'pending', NOW(), NOW()
FROM vehicles v, financing_options fo WHERE v.slug='peugeot-3008-2020' AND fo.name='Finanțare Premium' LIMIT 1;

-- ============================================================================
-- SEED 13: VEHICLE REVIEWS
-- ============================================================================
INSERT INTO `vehicle_reviews` (`vehicle_id`, `reviewer_name`, `reviewer_email`, `rating`, `title`, `review_text`, `purchase_verified`, `pros`, `cons`, `would_recommend`, `is_approved`, `is_featured`, `created_at`, `updated_at`)
SELECT v.id, 'Ștefan M.', 'stefan.m@email.ro', 5, 'Excelentă mașină!', 'Am cumpărat această Octavia pentru familie și sunt foarte mulțumit. Spațioasă, economică și fiabilă.', 1, 'Spațiu generos, consum mic, dotări moderne', 'Interior plasticuri tari', 1, 1, 1, DATE_SUB(NOW(), INTERVAL 15 DAY), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
UNION ALL SELECT v.id, 'Dana I.', 'dana.i@email.ro', 5, 'RAV4 Hybrid - cea mai bună alegere', 'Tracțiune 4x4, consum incredibil de mic pentru un SUV, tehnologie Toyota Safety Sense extraordinară. Nu regret niciun leu!', 1, 'Fiabilitate, consum hibrid, AWD', 'Preț mai ridicat la second-hand', 1, 1, 1, DATE_SUB(NOW(), INTERVAL 8 DAY), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, 'Florin P.', 'florin.p@email.ro', 4, 'BMW X1 - plăcere de condus', 'Mașină premium cu motorizare diesel economică. Dotări de top, finisaje premium. Recomand!', 1, 'Confort, performanță, brand premium', 'Costuri întreținere mai mari', 1, 1, 0, DATE_SUB(NOW(), INTERVAL 22 DAY), NOW() FROM vehicles v WHERE v.slug='bmw-x1-2019'
UNION ALL SELECT v.id, 'Alina C.', 'alina.c@email.ro', 5, 'Perfect pentru oraș', 'Clio este perfectă pentru oraș - mică, agilă, consum redus. Tehnologie modernă la bord.', 1, 'Dimensiuni compacte, tehnologie, design', 'Spațiu limitat pe bancheta spate', 1, 1, 0, DATE_SUB(NOW(), INTERVAL 5 DAY), NOW() FROM vehicles v WHERE v.slug='renault-clio-2021'
ON DUPLICATE KEY UPDATE `review_text`=VALUES(`review_text`), `updated_at`=NOW();

-- ============================================================================
-- SEED 14: TRADE-IN REQUESTS
-- ============================================================================
INSERT INTO `trade_in_requests` (`interested_vehicle_id`, `customer_name`, `customer_email`, `customer_phone`, `trade_brand`, `trade_model`, `trade_year`, `trade_mileage`, `trade_condition`, `trade_description`, `status`, `created_at`, `updated_at`)
SELECT v.id, 'Radu Nistor', 'radu.n@email.ro', '+40 722 456 789', 'Volkswagen', 'Passat', 2014, 185000, 'good', 'Passat B7, 2.0 TDI, stare bună, service la zi. Doresc evaluare pentru trade-in.', 'pending', NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, 'Ioana Dinu', 'ioana.dinu@email.ro', '+40 731 567 890', 'Dacia', 'Logan', 2016, 120000, 'fair', 'Logan MCV, ideal pentru partea de trade-in. Motor 0.9 TCe.', 'evaluated', NOW(), NOW() FROM vehicles v WHERE v.slug='renault-clio-2021'
UNION ALL SELECT v.id, 'Cosmin Rusu', 'cosmin.r@email.ro', '+40 744 678 901', 'Ford', 'Focus', 2015, 142000, 'good', 'Focus Mk3 diesel, bine întreținut. Trade-in pentru upgrade la X1.', 'offer_sent', NOW(), NOW() FROM vehicles v WHERE v.slug='bmw-x1-2019'
ON DUPLICATE KEY UPDATE `customer_name`=VALUES(`customer_name`), `updated_at`=NOW();

-- ============================================================================
-- SEED 15: VEHICLE WARRANTIES (assign warranties to premium vehicles)
-- ============================================================================
INSERT INTO `vehicle_warranties` (`vehicle_id`, `warranty_plan_id`, `start_date`, `end_date`, `mileage_at_start`, `is_active`, `created_at`, `updated_at`)
SELECT v.id, wp.id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 24 MONTH), v.mileage, 1, NOW(), NOW()
FROM vehicles v, warranty_plans wp
WHERE v.slug='toyota-rav4-2019' AND wp.name='Garanție Extinsă';

INSERT INTO `vehicle_warranties` (`vehicle_id`, `warranty_plan_id`, `start_date`, `end_date`, `mileage_at_start`, `is_active`, `created_at`, `updated_at`)
SELECT v.id, wp.id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 36 MONTH), v.mileage, 1, NOW(), NOW()
FROM vehicles v, warranty_plans wp
WHERE v.slug='bmw-x1-2019' AND wp.name='Garanție Premium';

INSERT INTO `vehicle_warranties` (`vehicle_id`, `warranty_plan_id`, `start_date`, `end_date`, `mileage_at_start`, `is_active`, `created_at`, `updated_at`)
SELECT v.id, wp.id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 12 MONTH), v.mileage, 1, NOW(), NOW()
FROM vehicles v, warranty_plans wp
WHERE v.slug='renault-clio-2021' AND wp.name='Garanție Bază';

-- ============================================================================
-- SEED 16: NEWSLETTER SUBSCRIPTIONS
-- ============================================================================
INSERT INTO `newsletter_subscriptions` (`email`, `name`, `is_active`, `verified_at`, `created_at`, `updated_at`) VALUES
('alexandra.pop@email.ro', 'Alexandra Pop', 1, NOW(), NOW(), NOW()),
('bogdan.vasile@email.ro', 'Bogdan Vasile', 1, NOW(), NOW(), NOW()),
('carmen.ion@email.ro', 'Carmen Ionescu', 1, NOW(), NOW(), NOW()),
('dan.marin@email.ro', 'Dan Marin', 1, NOW(), NOW(), NOW()),
('elena.stan@email.ro', 'Elena Stan', 1, NULL, NOW(), NOW()),
('florin.rus@email.ro', 'Florin Rus', 0, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `updated_at`=NOW();

-- ============================================================================
-- SEED 17: PRICE ALERTS
-- ============================================================================
INSERT INTO `price_alerts` (`vehicle_id`, `email`, `target_price`, `is_active`, `created_at`, `updated_at`)
SELECT v.id, 'gabriel.ionescu@email.ro', 20000.00, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='toyota-rav4-2019'
UNION ALL SELECT v.id, 'ana.dumitrescu@email.ro', 18000.00, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='peugeot-3008-2020'
UNION ALL SELECT v.id, 'vlad.popescu@email.ro', 13000.00, 1, NOW(), NOW() FROM vehicles v WHERE v.slug='skoda-octavia-2020'
ON DUPLICATE KEY UPDATE `target_price`=VALUES(`target_price`), `updated_at`=NOW();

-- ============================================================================
-- SEED 18: LINK VEHICLES TO CATEGORIES
-- ============================================================================
INSERT IGNORE INTO `vehicle_category_pivot` (`vehicle_id`, `category_id`)
SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='toyota-rav4-2019' AND vc.slug='suv-crossover'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='toyota-rav4-2019' AND vc.slug='electric-hybrid'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='skoda-octavia-2020' AND vc.slug='break-combi'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='skoda-octavia-2020' AND vc.slug='accesibile'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='bmw-x1-2019' AND vc.slug='suv-crossover'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='bmw-x1-2019' AND vc.slug='lux-premium'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='mercedes-gla-2020' AND vc.slug='suv-crossover'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='mercedes-gla-2020' AND vc.slug='lux-premium'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='renault-clio-2021' AND vc.slug='hatchback'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='renault-clio-2021' AND vc.slug='accesibile'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='dacia-spring-2022' AND vc.slug='electric-hybrid'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='dacia-spring-2022' AND vc.slug='hatchback'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='dacia-spring-2022' AND vc.slug='accesibile'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='ford-kuga-2018' AND vc.slug='suv-crossover'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='ford-kuga-2018' AND vc.slug='4x4-offroad'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='volvo-xc60-2018' AND vc.slug='suv-crossover'
UNION ALL SELECT v.id, vc.id FROM vehicles v, vehicle_categories vc WHERE v.slug='volvo-xc60-2018' AND vc.slug='lux-premium';

-- ============================================================================
-- SUCCESS MESSAGE
-- ============================================================================
SELECT 'Database seeded successfully! All tables populated with realistic data.' AS Status;

-- Quick stats
SELECT 
    (SELECT COUNT(*) FROM brands) as 'Brands',
    (SELECT COUNT(*) FROM models) as 'Models',
    (SELECT COUNT(*) FROM vehicles) as 'Vehicles',
    (SELECT COUNT(*) FROM vehicle_specifications) as 'Specifications',
    (SELECT COUNT(*) FROM vehicle_features) as 'Features',
    (SELECT COUNT(*) FROM appointments) as 'Appointments',
    (SELECT COUNT(*) FROM vehicle_reviews) as 'Reviews',
    (SELECT COUNT(*) FROM financing_applications) as 'Financing Apps',
    (SELECT COUNT(*) FROM service_history) as 'Service Records';

