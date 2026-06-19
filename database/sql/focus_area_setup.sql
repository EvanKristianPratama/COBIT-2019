-- ============================================================
-- COBIT 2019 Focus Area — Database Setup
-- Run this file manually in MySQL to create focus area tables
-- ============================================================

-- 1. Master table for focus areas
CREATE TABLE IF NOT EXISTS mst_focusarea (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL
);

-- 2. Pivot table: objective <-> focus area (Many-to-Many)
CREATE TABLE IF NOT EXISTS trs_objectivefocus (
    focusarea_id INTEGER NOT NULL,
    objective_id VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    FOREIGN KEY (focusarea_id) REFERENCES mst_focusarea(id) ON DELETE CASCADE,
    FOREIGN KEY (objective_id) REFERENCES mst_objective(objective_id) ON DELETE CASCADE,
    PRIMARY KEY (focusarea_id, objective_id)
);

-- 3. Seed initial focus areas
INSERT INTO mst_focusarea (code, name, description) VALUES
('SECURITY', 'Security', 'Focus on governance of enterprise security'),
('DIGITAL', 'Digital Transformation', 'Digital innovation and transformation'),
('COMPLIANCE', 'Regulatory Compliance', 'Compliance with regulatory requirements'),
('VALUE', 'Value Delivery', 'Focus on value creation and delivery'),
('RISK', 'Risk Management', 'IT-related risk management');
