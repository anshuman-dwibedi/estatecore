-- ============================================================
-- Real Estate Property Listing Platform — database.sql
-- DevCore Portfolio Suite
-- ============================================================

-- ── 1. CREATE & SELECT DATABASE ──────────────────────────────
CREATE DATABASE IF NOT EXISTS estate_core
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE estate_core;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS property_images;
DROP TABLE IF EXISTS inquiries;
DROP TABLE IF EXISTS properties;
DROP TABLE IF EXISTS admins;
SET FOREIGN_KEY_CHECKS = 1;

-- ─── ADMINS ─────────────────────────────────────────────────
CREATE TABLE admins (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    role       VARCHAR(50)  NOT NULL DEFAULT 'admin',
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- password: admin123 (bcrypt cost 12)
INSERT INTO admins (name, email, role, password, created_at) VALUES
('Admin User', 'admin@realestate.com', 'admin',
 '$2a$12$ZNzRQvODic4uFOoRa2EA.eTsw9FhqMtVukxqSOEablxjkrMP4QSLW',
 NOW() - INTERVAL 60 DAY);

-- ─── PROPERTIES ─────────────────────────────────────────────
CREATE TABLE properties (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200)  NOT NULL,
    description TEXT          NOT NULL,
    type        ENUM('house','apartment','villa','land') NOT NULL DEFAULT 'house',
    price       DECIMAL(12,2) NOT NULL,
    bedrooms    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    bathrooms   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    area_sqft   INT UNSIGNED  NOT NULL DEFAULT 0,
    address     VARCHAR(255)  NOT NULL,
    city        VARCHAR(100)  NOT NULL,
    image_url   VARCHAR(500)  NOT NULL DEFAULT '',
    status      ENUM('available','under_offer','sold') NOT NULL DEFAULT 'available',
    views       INT UNSIGNED  NOT NULL DEFAULT 0,
    scan_count  INT UNSIGNED  NOT NULL DEFAULT 0,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO properties
    (title, description, type, price, bedrooms, bathrooms, area_sqft, address, city, image_url, status, views, scan_count, created_at)
VALUES
-- Houses (available)
('Charming Colonial on Maple Street',
 'This beautifully maintained colonial home sits on a quiet tree-lined street. Features original hardwood floors throughout, an updated kitchen with stainless appliances, a cozy fireplace in the living room, and a spacious fenced backyard perfect for entertaining. Detached two-car garage. Walk to top-rated schools and local parks.',
 'house', 485000.00, 4, 2, 2250,
 '142 Maple Street', 'Austin', 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800&q=80',
 'available', 634, 18, NOW() - INTERVAL 45 DAY),

('Modern Craftsman with Mountain Views',
 'Stunning craftsman-style home with sweeping mountain views from every room. Open-concept floor plan with vaulted ceilings, chef''s kitchen featuring quartz counters and a 10-foot island, primary suite with spa bath, and a wraparound deck. Solar panels included, reducing energy costs dramatically.',
 'house', 875000.00, 5, 3, 3100,
 '88 Ridgeline Drive', 'Denver', 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800&q=80',
 'available', 512, 24, NOW() - INTERVAL 38 DAY),

('Cozy Ranch in Quiet Cul-de-Sac',
 'Single-story ranch home perfect for downsizers or first-time buyers. Freshly painted interior, new roof (2022), updated bathrooms, and a large covered patio overlooking the private backyard. Low-maintenance landscaping. Attached two-car garage with extra storage.',
 'house', 312000.00, 3, 2, 1650,
 '7 Pinewood Court', 'Phoenix', 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
 'available', 389, 9, NOW() - INTERVAL 52 DAY),

('Contemporary New Build — Corner Lot',
 'Brand new construction completed in 2024. This corner-lot home boasts a bold architectural design with floor-to-ceiling windows, polished concrete floors on the main level, and a rooftop terrace. Smart home technology throughout: smart locks, lighting, thermostats, and security cameras.',
 'house', 1150000.00, 4, 3, 2870,
 '301 Avant Avenue', 'Seattle', 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&q=80',
 'available', 721, 31, NOW() - INTERVAL 15 DAY),

('Classic Victorian with Original Details',
 'A lovingly restored 1890s Victorian that blends historic character with modern comforts. Original crown moldings, bay windows, and decorative woodwork throughout. Fully updated kitchen, primary bath with clawfoot tub, and a beautifully landscaped garden. Walking distance to downtown dining.',
 'house', 695000.00, 4, 2, 2400,
 '55 Heritage Lane', 'San Francisco', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&q=80',
 'available', 467, 12, NOW() - INTERVAL 29 DAY),

-- Apartments (available)
('Downtown Loft — Exposed Brick & Beams',
 'Stunning industrial loft in the heart of the arts district. 14-foot ceilings, exposed brick walls, original timber beams, and polished concrete floors. Gourmet kitchen with professional-grade appliances. Building amenities include a rooftop lounge, gym, and concierge. Walk score: 98.',
 'apartment', 520000.00, 1, 1, 980,
 '200 Arts District Blvd, Unit 4F', 'Chicago', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80',
 'available', 298, 7, NOW() - INTERVAL 22 DAY),

('Luxury High-Rise with Skyline Views',
 'Impeccably designed 30th-floor apartment offering panoramic city skyline views. Floor-to-ceiling windows, designer finishes, and a chef''s kitchen with waterfall island. Full-service building with 24-hour doorman, indoor pool, and private parking. Corner unit — natural light all day.',
 'apartment', 1250000.00, 3, 2, 1680,
 '1 Pinnacle Tower, Unit 3002', 'New York', 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&q=80',
 'available', 805, 43, NOW() - INTERVAL 8 DAY),

('Bright Garden-Level Unit in Tree-Lined Block',
 'This rare garden-level apartment opens directly onto a private patio surrounded by mature trees. Tastefully renovated with new kitchen, updated baths, and in-unit laundry. Bonus den can serve as a home office. Pet-friendly building. One parking space included.',
 'apartment', 285000.00, 2, 1, 890,
 '74 Elm Row, Unit G1', 'Portland', 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80',
 'available', 215, 5, NOW() - INTERVAL 41 DAY),

('Modern Studio — Perfect for Investors',
 'High-yield investment opportunity in a sought-after neighborhood. Smart floor plan maximises every square foot. Currently rented at $1,850/month. New appliances, quartz counters, and fresh finishes throughout. Strong rental demand in this ZIP code. Condo fees include water and trash.',
 'apartment', 185000.00, 0, 1, 520,
 '900 Investor Row, Unit 12B', 'Atlanta', 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800&q=80',
 'available', 176, 3, NOW() - INTERVAL 33 DAY),

-- Villas (available)
('Mediterranean Villa with Private Pool',
 'Exquisite Mediterranean-inspired villa set on a half-acre lot. Grand entrance with travertine floors, soaring ceilings, and a sweeping staircase. The resort-style backyard features a lagoon pool, outdoor kitchen, and covered loggia. Gourmet kitchen, wine cellar, and home theatre included.',
 'villa', 2250000.00, 6, 5, 5800,
 '1 Villa Rosa Court', 'Miami', 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&q=80',
 'available', 743, 52, NOW() - INTERVAL 12 DAY),

('Tuscan-Style Villa on Golf Course',
 'Situated on the 5th fairway of a premier private golf course, this Tuscan villa offers breathtaking views and luxury living. Hand-painted ceilings, imported stone floors, and custom woodwork throughout. Chef''s kitchen, 3-car garage, and a guest casita. Membership to the country club included.',
 'villa', 1875000.00, 5, 4, 4900,
 '22 Fairway Vista', 'Scottsdale', 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&q=80',
 'available', 534, 27, NOW() - INTERVAL 19 DAY),

('Beachfront Villa — Direct Ocean Access',
 'Once-in-a-generation oceanfront opportunity. This stunning villa sits directly on a private stretch of beach. The fully retractable glass walls on the ocean side blur the line between indoor and outdoor living. Infinity pool, private dock for a boat, and a rooftop sun deck.',
 'villa', 2500000.00, 5, 4, 5200,
 '1 Ocean Bluff Drive', 'Malibu', 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?w=800&q=80',
 'available', 688, 48, NOW() - INTERVAL 6 DAY),

-- Land (available)
('Prime 2-Acre Development Parcel',
 'Rectangular 2-acre parcel in a fast-growing suburb, fully approved for a 12-unit residential development. All utilities at the street. Close to major employers, shopping, and the freeway. Zoning: R-3 multi-family. Survey and soil test available upon request.',
 'land', 450000.00, 0, 0, 87120,
 'Lot 7 Commerce Boulevard', 'Nashville', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&q=80',
 'available', 312, 14, NOW() - INTERVAL 55 DAY),

('Wooded Retreat — 5 Acres with Creek',
 'Escape to nature on this stunning 5-acre wooded lot with a spring-fed creek running through the southern boundary. Ideal for a custom estate, hunting cabin, or investment hold. Power and well water available at the road. Minutes from state park and hiking trails.',
 'land', 195000.00, 0, 0, 217800,
 '0 Creekside Trail', 'Asheville', 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&q=80',
 'available', 198, 6, NOW() - INTERVAL 48 DAY),

('Hilltop Lot with Panoramic Valley Views',
 'Build your dream home on this elevated hilltop lot boasting 360-degree valley views. Gentle slope suitable for a walkout basement design. HOA community with paved roads, underground utilities, and shared amenity center. Only 2 lots remaining in this sought-after enclave.',
 'land', 275000.00, 0, 0, 43560,
 'Lot 14 Summit Ridge', 'Bozeman', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80',
 'available', 243, 11, NOW() - INTERVAL 27 DAY),

-- Under Offer
('Renovated Bungalow — Heart of Midtown',
 'This picture-perfect bungalow was fully renovated in 2023. Open kitchen with shaker cabinets and marble counters, spa-style primary bath, refinished hardwood floors. The landscaped front yard and deep covered porch give fantastic curb appeal. Multiple offers received — currently under offer.',
 'house', 565000.00, 3, 2, 1800,
 '404 Blossom Street', 'Charlotte', 'https://images.unsplash.com/photo-1523217582562-09d0def993a6?w=800&q=80',
 'under_offer', 589, 22, NOW() - INTERVAL 35 DAY),

('Upscale City Apartment — Penthouse Level',
 'Top-floor penthouse with private terrace and 360-degree views. Two-level layout with glass staircase, smart home system, and bespoke designer finishes. Building offers valet parking, spa, and residents'' lounge. Currently under offer — enquire for back-up position.',
 'apartment', 980000.00, 2, 2, 1450,
 '5 Skyline Plaza, PH2', 'Boston', 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800&q=80',
 'under_offer', 677, 35, NOW() - INTERVAL 10 DAY),

('Executive Villa — Gated Community',
 'Impressive executive villa in the prestigious Oakmont Estates gated community. Grand entry, formal dining and living rooms, a gourmet kitchen, and a bonus room. The resort backyard features a solar-heated pool, spa, and outdoor kitchen. Currently under offer.',
 'villa', 1650000.00, 5, 4, 4600,
 '8 Oakmont Drive', 'Houston', 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&q=80',
 'under_offer', 492, 19, NOW() - INTERVAL 21 DAY),

-- Sold
('Lakeside Cabin — Sold',
 'Charming waterfront cabin on a pristine private lake. Sold after just 4 days on market. Wraparound porch, wood-burning fireplace, private dock, and two-car garage. This property sold above asking price.',
 'house', 420000.00, 3, 2, 1550,
 '12 Lakeview Road', 'Minneapolis', 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800&q=80',
 'sold', 445, 16, NOW() - INTERVAL 60 DAY),

('City Centre Studio — Sold',
 'High-demand studio in the most walkable block downtown. Sold within 48 hours. Updated kitchen, new HVAC, and building rooftop access. Excellent rental history for investors.',
 'apartment', 152000.00, 0, 1, 480,
 '300 Main Street, Unit 2A', 'Detroit', 'https://images.unsplash.com/photo-1555636222-cae831e670b3?w=800&q=80',
 'sold', 321, 8, NOW() - INTERVAL 50 DAY);

-- ─── PROPERTY IMAGES (gallery thumbnails) ───────────────────
CREATE TABLE property_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    image_url   VARCHAR(500) NOT NULL,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO property_images (property_id, image_url, sort_order) VALUES
(1, 'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=400&q=70', 1),
(1, 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&q=70', 2),
(1, 'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=400&q=70', 3),
(2, 'https://images.unsplash.com/photo-1588880331179-bc9b93a8cb5e?w=400&q=70', 1),
(2, 'https://images.unsplash.com/photo-1507089947368-19c1da9775ae?w=400&q=70', 2),
(3, 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&q=70', 1),
(4, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=400&q=70', 1),
(4, 'https://images.unsplash.com/photo-1600573472556-e636c2acda88?w=400&q=70', 2),
(5, 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=400&q=70', 1),
(6, 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?w=400&q=70', 1),
(7, 'https://images.unsplash.com/photo-1567225557594-88d73e55f2cb?w=400&q=70', 1),
(7, 'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?w=400&q=70', 2),
(10, 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?w=400&q=70', 1),
(10, 'https://images.unsplash.com/photo-1565183928294-7063f23ce0f8?w=400&q=70', 2),
(10, 'https://images.unsplash.com/photo-1571055107559-3e67626fa8be?w=400&q=70', 3),
(11, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&q=70', 1),
(12, 'https://images.unsplash.com/photo-1526755094997-2e9c58b4f4e3?w=400&q=70', 1);

-- ─── INQUIRIES ───────────────────────────────────────────────
CREATE TABLE inquiries (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id INT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  NOT NULL DEFAULT '',
    message     TEXT         NOT NULL,
    status      ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO inquiries (property_id, name, email, phone, message, status, created_at) VALUES
(1,  'Sarah Mitchell',   'sarah.m@email.com',    '512-555-0142', 'I would love to schedule a viewing this weekend. Is Saturday afternoon available?', 'contacted', NOW() - INTERVAL 28 DAY),
(2,  'James O''Brien',   'jobrien@outlook.com',  '720-555-0198', 'Very interested in the mountain views. Can you send more photos of the backyard?', 'new',       NOW() - INTERVAL 26 DAY),
(7,  'Emily Chen',       'echen.nyc@gmail.com',  '212-555-0311', 'Is this unit available from the 1st of next month? We are relocating from San Jose.', 'contacted', NOW() - INTERVAL 25 DAY),
(10, 'Roberto Valdes',   'rvaldes@corp.net',     '305-555-0477', 'Interested in the villa. Please confirm if the pool equipment is recent. We fly in Fri.', 'new',       NOW() - INTERVAL 24 DAY),
(4,  'Priya Sharma',     'priya.s@techco.io',    '206-555-0219', 'Love the smart home features. What''s the HOA fee? Any special assessments pending?', 'new',       NOW() - INTERVAL 22 DAY),
(12, 'Derek Weston',     'dweston@gmail.com',    '310-555-0555', 'The beachfront property looks incredible. Is there direct beach access year-round?', 'new',       NOW() - INTERVAL 21 DAY),
(5,  'Linda Park',       'lpark@homebuyer.net',  '415-555-0391', 'I love the Victorian character. Has the plumbing been updated? Looking for a forever home.', 'contacted', NOW() - INTERVAL 20 DAY),
(1,  'Tom Hargreaves',   'thargreaves@isp.com',  '512-555-0601', 'Saw the signboard on Maple Street! QR code led me right here. Can I do a drive-by today?', 'new',       NOW() - INTERVAL 19 DAY),
(3,  'Nadia Kowalski',   'nkowalski@web.com',    '602-555-0723', 'We are first-time buyers with a pre-approval for $350k. Does the seller have any flexibility?', 'contacted', NOW() - INTERVAL 18 DAY),
(6,  'Marcus Freeman',   'mfreeman@loft.co',     '312-555-0814', 'Exactly the kind of space we''ve been searching for. What does the parking situation look like?', 'new',       NOW() - INTERVAL 17 DAY),
(11, 'Amanda Torres',    'atorres@design.com',   '480-555-0932', 'Golf course views are spectacular. When does the country club membership start? Keen to view ASAP.', 'new',       NOW() - INTERVAL 15 DAY),
(2,  'Kevin Nguyen',     'knguyen@finance.org',  '720-555-1011', 'Solar panels are a big draw. Can you provide the last 12 months of utility bills?', 'contacted', NOW() - INTERVAL 14 DAY),
(8,  'Fiona Campbell',   'fiona.c@realtor.com',  '503-555-1122', 'Representing a buyer relocating from LA. Is the garden truly private? Would love a same-week showing.', 'new',       NOW() - INTERVAL 13 DAY),
(13, 'Stuart Billings',  'sbillings@dev.net',    '615-555-1243', 'The 2-acre parcel interests our development group. Is the zoning variance already granted?', 'contacted', NOW() - INTERVAL 12 DAY),
(4,  'Yuki Tanaka',      'yukitanaka@jp.net',    '206-555-1356', 'I am an international buyer. Do you work with buyers who finance internationally? Interested.', 'new',       NOW() - INTERVAL 11 DAY),
(10, 'Bianca Ross',      'bross@luxe.co',        '305-555-1467', 'Second inquiry — still very interested in the Mediterranean villa. Any price reduction possible?', 'contacted', NOW() - INTERVAL 10 DAY),
(16, 'Chris Daniels',    'cdaniels@invest.com',  '704-555-1578', 'This renovated bungalow looks great! We are ready to move quickly. Is the seller still entertaining offers?', 'new',       NOW() - INTERVAL 9 DAY),
(9,  'Rachel Stone',     'rstone@gmail.com',     '404-555-1689', 'I''m an investor looking for rental properties. What are current rents in this building?', 'closed',    NOW() - INTERVAL 8 DAY),
(5,  'George Adeyemi',   'gadeyemi@bank.io',     '415-555-1700', 'Coming from London, we love Victorian homes. Is this in a flood zone? Pre-approved for $800k.', 'new',       NOW() - INTERVAL 7 DAY),
(7,  'Chloe Martineau',  'chloe.m@finance.fr',   '212-555-1811', 'Interested in the penthouse as a pied-à-terre. Do you allow subletting? Short-term rental rules?', 'contacted', NOW() - INTERVAL 6 DAY),
(14, 'Ivan Petrova',     'ivanp@rural.org',      '828-555-1922', 'We are looking for a homestead property. Is a well already permitted on the wooded lot?', 'new',       NOW() - INTERVAL 5 DAY),
(12, 'Maya Fitzgerald',  'maya.f@wealth.com',    '310-555-2033', 'Scanned the QR code on the signboard at the beach! Can the boat dock accommodate a 28ft vessel?', 'new',       NOW() - INTERVAL 5 DAY),
(2,  'Dale Hoffman',     'dhoffman@corp.net',     '720-555-2144', 'Third time visiting the listing — our agent says it''s the best value in Denver. Ready to offer.', 'contacted', NOW() - INTERVAL 4 DAY),
(3,  'Susan Yamamoto',   'syamamoto@home.jp',    '602-555-2255', 'This is exactly what we need. Single story is a must for us. Can we view next Tuesday?', 'new',       NOW() - INTERVAL 3 DAY),
(15, 'Patrick Lloyd',    'plloyd@builder.net',   '406-555-2366', 'HOA community sounds appealing for a custom build. What are the architectural guidelines?', 'new',       NOW() - INTERVAL 3 DAY),
(11, 'Grace Kim',        'gracekim@pr.com',      '480-555-2477', 'The guest casita would be perfect for my in-laws. Is the golf membership transferable at sale?', 'contacted', NOW() - INTERVAL 2 DAY),
(6,  'Theo Blackwell',   'tblackwell@art.com',   '312-555-2588', 'This loft has the exact vibe for my art studio / live space. How quickly can we do a virtual tour?', 'new',       NOW() - INTERVAL 2 DAY),
(4,  'Angela Russo',     'arusso@tech.io',        '206-555-2699', 'Smart home tech is a dealbreaker for me — and this has everything. What''s the closing timeline?', 'new',       NOW() - INTERVAL 1 DAY),
(10, 'Hiroshi Nakamura', 'hnakamura@global.jp',  '305-555-2700', 'International buyer in Miami next week. Absolutely must see the villa in person. Please confirm.', 'new',       NOW() - INTERVAL 1 DAY),
(1,  'Olivia Barnes',    'obarnes@local.com',     '512-555-2811', 'Scanned from the signboard on my morning walk! Beautiful curb appeal. Available this weekend?', 'new',       NOW());
