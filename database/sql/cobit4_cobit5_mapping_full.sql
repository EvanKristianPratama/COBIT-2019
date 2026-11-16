-- COBIT 4.1 to COBIT 5 mapping data (additive and repeatable; preserves unrelated rows).
-- Creates the two mapping tables if needed, then upserts all source and relation rows.
-- Create the mapping tables if they are not present yet (matches the Laravel migration).
CREATE TABLE IF NOT EXISTS `mst_cobit4_cobit5_mapping_sources` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(20) NOT NULL,
    `domain` VARCHAR(10) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `mapping_status` VARCHAR(20) NOT NULL DEFAULT 'mapped',
    `mapping_note` TEXT NULL,
    `review_note` TEXT NULL,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `mst_cobit4_cobit5_mapping_sources_code_unique` (`code`),
    KEY `mst_cobit4_cobit5_mapping_sources_domain_index` (`domain`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `trs_cobit4_cobit5_mappings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `source_id` BIGINT UNSIGNED NOT NULL,
    `target_code` VARCHAR(20) NOT NULL,
    `target_level` VARCHAR(20) NOT NULL,
    `order_no` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `trs_cobit4_cobit5_mappings_source_id_target_code_unique` (`source_id`, `target_code`),
    KEY `trs_cobit4_cobit5_mappings_target_code_index` (`target_code`),
    CONSTRAINT `trs_cobit4_cobit5_mappings_source_id_foreign`
        FOREIGN KEY (`source_id`) REFERENCES `mst_cobit4_cobit5_mapping_sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

START TRANSACTION;

INSERT INTO `mst_cobit4_cobit5_mapping_sources`
    (`code`, `domain`, `title`, `mapping_status`, `mapping_note`, `review_note`, `order_no`, `created_at`, `updated_at`)
VALUES
    ('AC1', 'AC', 'Source Data Preparation and Authorisation', 'mapped', NULL, NULL, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC2', 'AC', 'Source Data Collection and Entry', 'mapped', NULL, NULL, 2, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC3', 'AC', 'Accuracy, Completeness and Authenticity Checks', 'mapped', NULL, NULL, 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC4', 'AC', 'Processing Integrity and Validity', 'mapped', NULL, NULL, 4, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC5', 'AC', 'Output Review, Reconciliation and Error Handling', 'mapped', NULL, NULL, 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AC6', 'AC', 'Transaction Authentication and Integrity', 'mapped', NULL, NULL, 6, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.1', 'PO', 'IT Value Management', 'mapped', NULL, NULL, 7, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.2', 'PO', 'Business-IT Alignment', 'mapped', NULL, NULL, 8, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.3', 'PO', 'Assessment of Current Capability and Performance', 'mapped', NULL, NULL, 9, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.4', 'PO', 'IT Strategic Plan', 'mapped', NULL, NULL, 10, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.5', 'PO', 'IT Tactical Plans', 'mapped', NULL, NULL, 11, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO1.6', 'PO', 'IT Portfolio Management', 'mapped', NULL, NULL, 12, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.1', 'PO', 'Enterprise Information Architecture Model', 'mapped', NULL, NULL, 13, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.2', 'PO', 'Enterprise Data Dictionary and Data Syntax Rules', 'mapped', NULL, NULL, 14, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.3', 'PO', 'Data Classification Scheme', 'mapped', NULL, NULL, 15, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO2.4', 'PO', 'Integrity Management', 'mapped', NULL, NULL, 16, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.1', 'PO', 'Technological Direction Planning', 'mapped', NULL, NULL, 17, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.2', 'PO', 'Technical Infrastructure Plan', 'mapped', NULL, NULL, 18, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.3', 'PO', 'Monitor Future Trends and Regulations', 'mapped', NULL, NULL, 19, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.4', 'PO', 'Technology Standards', 'mapped', NULL, NULL, 20, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO3.5', 'PO', 'IT Architecture Board', 'mapped', NULL, NULL, 21, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.1', 'PO', 'IT Process Framework', 'mapped', NULL, NULL, 22, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.2', 'PO', 'IT Strategy Committee', 'mapped', NULL, NULL, 23, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.3', 'PO', 'IT Steering Committee', 'mapped', NULL, NULL, 24, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.4', 'PO', 'Organisational Placement of the IT Function', 'mapped', NULL, NULL, 25, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.5', 'PO', 'IT Organisational Structure', 'mapped', NULL, NULL, 26, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.6', 'PO', 'Establishment of Roles and Responsibilities', 'mapped', NULL, NULL, 27, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.7', 'PO', 'Responsibility for IT Quality Assurance', 'mapped', NULL, NULL, 28, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.8', 'PO', 'Responsibility for Risk, Security and Compliance', 'deleted', 'These specific roles are no longer explicitly specified as a practice.', NULL, 29, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.9', 'PO', 'Data and System Ownership', 'mapped', NULL, NULL, 30, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.10', 'PO', 'Supervision', 'mapped', NULL, NULL, 31, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.11', 'PO', 'Segregation of Duties', 'mapped', NULL, 'DSS08.02 is retained as shown in the supplied mapping image. Verify this code: the COBIT 5 process reference model lists DSS01–DSS06, not DSS08.', 32, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.12', 'PO', 'IT Staffing', 'mapped', NULL, NULL, 33, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.13', 'PO', 'Key IT Personnel', 'mapped', NULL, NULL, 34, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.14', 'PO', 'Contracted Staff Policies and Procedures', 'mapped', NULL, NULL, 35, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO4.15', 'PO', 'Relationships', 'mapped', NULL, NULL, 36, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.1', 'PO', 'Financial Management Framework', 'mapped', NULL, NULL, 37, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.2', 'PO', 'Prioritisation Within IT Budget', 'mapped', NULL, NULL, 38, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.3', 'PO', 'IT Budgeting', 'mapped', NULL, NULL, 39, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.4', 'PO', 'Cost Management', 'mapped', NULL, NULL, 40, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO5.5', 'PO', 'Benefit Management', 'mapped', NULL, NULL, 41, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.1', 'PO', 'IT Policy and Control Environment', 'mapped', NULL, NULL, 42, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.2', 'PO', 'Enterprise IT Risk and Control Framework', 'mapped', NULL, NULL, 43, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.3', 'PO', 'IT Policies Management', 'mapped', NULL, NULL, 44, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.4', 'PO', 'Policy, Standards and Procedures Rollout', 'mapped', NULL, NULL, 45, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO6.5', 'PO', 'Communication of IT Objectives and Direction', 'mapped', NULL, NULL, 46, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.1', 'PO', 'Personnel Recruitment and Retention', 'mapped', NULL, 'Figure 14 maps both practices to PO7.1; the supplied image labels the second row PO7.2.', 47, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.2', 'PO', 'Personnel Competencies', 'mapped', NULL, NULL, 48, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.3', 'PO', 'Staffing of Roles', 'mapped', NULL, 'Figure 14 includes APO07.01 as well as APO01.02.', 49, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.4', 'PO', 'Personnel Training', 'mapped', NULL, NULL, 50, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.5', 'PO', 'Dependence Upon Individuals', 'mapped', NULL, NULL, 51, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.6', 'PO', 'Personnel Clearance Procedures', 'mapped', NULL, NULL, 52, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.7', 'PO', 'Employee Job Performance Evaluation', 'mapped', NULL, NULL, 53, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO7.8', 'PO', 'Job Change and Termination', 'mapped', NULL, NULL, 54, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.1', 'PO', 'Quality Management System', 'mapped', NULL, NULL, 55, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.2', 'PO', 'IT Standards and Quality Practices', 'mapped', NULL, NULL, 56, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.3', 'PO', 'Development and Acquisition Standards', 'mapped', NULL, NULL, 57, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.4', 'PO', 'Customer Focus', 'mapped', NULL, NULL, 58, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.5', 'PO', 'Continuous Improvement', 'mapped', NULL, NULL, 59, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO8.6', 'PO', 'Quality Measurement, Monitoring and Review', 'mapped', NULL, NULL, 60, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.1', 'PO', 'IT Risk Management Framework', 'mapped', NULL, NULL, 61, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.2', 'PO', 'Establishment of Risk Context', 'mapped', NULL, NULL, 62, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.3', 'PO', 'Event Identification', 'mapped', NULL, NULL, 63, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.4', 'PO', 'Risk Assessment', 'mapped', NULL, NULL, 64, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.5', 'PO', 'Risk Response', 'mapped', NULL, NULL, 65, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO9.6', 'PO', 'Maintenance and Monitoring of a Risk Action Plan', 'mapped', NULL, NULL, 66, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.1', 'PO', 'Programme Management Framework', 'mapped', NULL, NULL, 67, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.2', 'PO', 'Project Management Framework', 'mapped', NULL, NULL, 68, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.3', 'PO', 'Project Management Approach', 'mapped', NULL, NULL, 69, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.4', 'PO', 'Stakeholder Commitment', 'mapped', NULL, NULL, 70, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.5', 'PO', 'Project Scope Statement', 'mapped', NULL, NULL, 71, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.6', 'PO', 'Project Phase Initiation', 'mapped', NULL, NULL, 72, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.7', 'PO', 'Integrated Project Plan', 'mapped', NULL, NULL, 73, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.8', 'PO', 'Project Resources', 'mapped', NULL, NULL, 74, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.9', 'PO', 'Project Risk Management', 'mapped', NULL, NULL, 75, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.10', 'PO', 'Project Quality Plan', 'mapped', NULL, NULL, 76, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.11', 'PO', 'Project Change Control', 'mapped', NULL, NULL, 77, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.12', 'PO', 'Project Planning of Assurance Methods', 'mapped', NULL, NULL, 78, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.13', 'PO', 'Project Performance Measurement, Reporting and Monitoring', 'mapped', NULL, NULL, 79, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('PO10.14', 'PO', 'Project Closure', 'mapped', NULL, NULL, 80, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.1', 'AI', 'Definition and Maintenance of Business Functional and Technical Requirements', 'mapped', NULL, NULL, 81, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.2', 'AI', 'Risk Analysis Report', 'mapped', NULL, NULL, 82, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.3', 'AI', 'Feasibility Study and Formulation of Alternative Courses of Action', 'mapped', NULL, NULL, 83, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI1.4', 'AI', 'Requirements and Feasibility Decision and Approval', 'mapped', NULL, NULL, 84, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.1', 'AI', 'High-level Design', 'mapped', NULL, NULL, 85, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.2', 'AI', 'Detailed Design', 'mapped', NULL, NULL, 86, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.3', 'AI', 'Application Control and Auditability', 'mapped', NULL, NULL, 87, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.4', 'AI', 'Application Security and Availability', 'mapped', NULL, NULL, 88, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.5', 'AI', 'Configuration and Implementation of Acquired Application Software', 'mapped', NULL, NULL, 89, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.6', 'AI', 'Major Upgrades to Existing Systems', 'mapped', NULL, NULL, 90, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.7', 'AI', 'Development of Application Software', 'mapped', NULL, NULL, 91, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.8', 'AI', 'Software Quality Assurance', 'mapped', NULL, NULL, 92, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.9', 'AI', 'Applications Requirements Management', 'mapped', NULL, NULL, 93, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI2.10', 'AI', 'Application Software Maintenance', 'mapped', NULL, NULL, 94, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.1', 'AI', 'Technological Infrastructure Acquisition Plan', 'mapped', NULL, NULL, 95, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.2', 'AI', 'Infrastructure Resource Protection and Availability', 'mapped', NULL, NULL, 96, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.3', 'AI', 'Infrastructure Maintenance', 'mapped', NULL, NULL, 97, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI3.4', 'AI', 'Feasibility Test Environment', 'mapped', NULL, NULL, 98, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.1', 'AI', 'Planning for Operational Solutions', 'mapped', NULL, NULL, 99, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.2', 'AI', 'Knowledge Transfer to Business Management', 'mapped', NULL, NULL, 100, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.3', 'AI', 'Knowledge Transfer to End Users', 'mapped', NULL, NULL, 101, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI4.4', 'AI', 'Knowledge Transfer to Operations and Support Staff', 'mapped', NULL, NULL, 102, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.1', 'AI', 'Procurement Control', 'mapped', NULL, NULL, 103, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.2', 'AI', 'Supplier Contract Management', 'mapped', NULL, NULL, 104, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.3', 'AI', 'Supplier Selection', 'mapped', NULL, NULL, 105, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI5.4', 'AI', 'IT Resources Acquisition', 'mapped', NULL, NULL, 106, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.1', 'AI', 'Change Standards and Procedures', 'mapped', NULL, NULL, 107, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.2', 'AI', 'Impact Assessment, Prioritisation and Authorisation', 'mapped', NULL, NULL, 108, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.3', 'AI', 'Emergency Changes', 'mapped', NULL, NULL, 109, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.4', 'AI', 'Change Status Tracking and Reporting', 'mapped', NULL, NULL, 110, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI6.5', 'AI', 'Change Closure and Documentation', 'mapped', NULL, NULL, 111, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.1', 'AI', 'Training', 'mapped', NULL, NULL, 112, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.2', 'AI', 'Test Plan', 'mapped', NULL, NULL, 113, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.3', 'AI', 'Implementation Plan', 'mapped', NULL, NULL, 114, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.4', 'AI', 'Test Environment', 'mapped', NULL, NULL, 115, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.5', 'AI', 'System and Data Conversion', 'mapped', NULL, NULL, 116, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.6', 'AI', 'Testing of Changes', 'mapped', NULL, NULL, 117, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.7', 'AI', 'Final Acceptance Test', 'mapped', NULL, NULL, 118, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.8', 'AI', 'Promotion to Production', 'mapped', NULL, NULL, 119, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('AI7.9', 'AI', 'Post-implementation Review', 'mapped', NULL, NULL, 120, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.1', 'DS', 'Service Level Management Framework', 'mapped', NULL, NULL, 121, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.2', 'DS', 'Definition of Services', 'mapped', NULL, 'The supplied reverse table shows APO09.01 for DS1.2; repeated identical rows are represented once.', 122, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.3', 'DS', 'Service Level Agreements', 'mapped', NULL, NULL, 123, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.4', 'DS', 'Operating Level Agreements', 'mapped', NULL, NULL, 124, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.5', 'DS', 'Monitoring and Reporting of Service Level Achievements', 'mapped', NULL, NULL, 125, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS1.6', 'DS', 'Review of Service Level Agreements and Contracts', 'mapped', NULL, NULL, 126, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.1', 'DS', 'Identification of All Supplier Relationships', 'mapped', NULL, NULL, 127, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.2', 'DS', 'Supplier Relationship Management', 'mapped', NULL, NULL, 128, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.3', 'DS', 'Supplier Risk Management', 'mapped', NULL, NULL, 129, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS2.4', 'DS', 'Supplier Performance Monitoring', 'mapped', NULL, NULL, 130, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.1', 'DS', 'Performance and Capacity Planning', 'mapped', NULL, NULL, 131, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.2', 'DS', 'Current Performance and Capacity', 'mapped', NULL, NULL, 132, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.3', 'DS', 'Future Performance and Capacity', 'mapped', NULL, NULL, 133, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.4', 'DS', 'IT Resources Availability', 'mapped', NULL, NULL, 134, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS3.5', 'DS', 'Monitoring and Reporting', 'mapped', NULL, NULL, 135, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.1', 'DS', 'IT Continuity Framework', 'mapped', NULL, NULL, 136, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.2', 'DS', 'IT Continuity Plans', 'mapped', NULL, NULL, 137, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.3', 'DS', 'Critical IT Resources', 'mapped', NULL, NULL, 138, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.4', 'DS', 'Maintenance of the IT Continuity Plan', 'mapped', NULL, NULL, 139, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.5', 'DS', 'Testing of the IT Continuity Plan', 'mapped', NULL, NULL, 140, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.6', 'DS', 'IT Continuity Plan Training', 'mapped', NULL, NULL, 141, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.7', 'DS', 'Distribution of the IT Continuity Plan', 'mapped', NULL, NULL, 142, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.8', 'DS', 'IT Services Recovery and Resumption', 'mapped', NULL, NULL, 143, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.9', 'DS', 'Offsite Backup Storage', 'mapped', NULL, NULL, 144, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS4.10', 'DS', 'Post-resumption Review', 'mapped', NULL, NULL, 145, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.1', 'DS', 'Management of IT Security', 'mapped', NULL, NULL, 146, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.2', 'DS', 'IT Security Plan', 'mapped', NULL, NULL, 147, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.3', 'DS', 'Identity Management', 'mapped', NULL, NULL, 148, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.4', 'DS', 'User Account Management', 'mapped', NULL, NULL, 149, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.5', 'DS', 'Security Testing, Surveillance and Monitoring', 'mapped', NULL, NULL, 150, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.6', 'DS', 'Security Incident Definition', 'mapped', NULL, NULL, 151, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.7', 'DS', 'Protection of Security Technology', 'mapped', NULL, NULL, 152, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.8', 'DS', 'Cryptographic Key Management', 'mapped', NULL, NULL, 153, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.9', 'DS', 'Malicious Software Prevention, Detection and Correction', 'mapped', NULL, NULL, 154, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.10', 'DS', 'Network Security', 'mapped', NULL, NULL, 155, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS5.11', 'DS', 'Exchange of Sensitive Data', 'mapped', NULL, NULL, 156, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.1', 'DS', 'Definition of Services', 'mapped', NULL, NULL, 157, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.2', 'DS', 'IT Accounting', 'mapped', NULL, NULL, 158, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.3', 'DS', 'Cost Modelling and Charging', 'mapped', NULL, NULL, 159, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS6.4', 'DS', 'Cost Model Maintenance', 'mapped', NULL, NULL, 160, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS7.1', 'DS', 'Identification of Education and Training Needs', 'mapped', NULL, NULL, 161, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS7.2', 'DS', 'Delivery of Training and Education', 'mapped', NULL, NULL, 162, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS7.3', 'DS', 'Evaluation of Training Received', 'mapped', NULL, NULL, 163, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.1', 'DS', 'Service Desk', 'deleted', 'ITIL 3 does not refer to Service Desk as a process.', NULL, 164, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.2', 'DS', 'Registration of Customer Queries', 'mapped', NULL, NULL, 165, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.3', 'DS', 'Incident Escalation', 'mapped', NULL, NULL, 166, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.4', 'DS', 'Incident Closure', 'mapped', NULL, NULL, 167, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS8.5', 'DS', 'Reporting and Trend Analysis', 'mapped', NULL, NULL, 168, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS9.1', 'DS', 'Configuration Repository and Baseline', 'mapped', NULL, NULL, 169, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS9.2', 'DS', 'Identification and Maintenance of Configuration Items', 'mapped', NULL, NULL, 170, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS9.3', 'DS', 'Configuration Integrity Review', 'mapped', NULL, NULL, 171, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.1', 'DS', 'Identification and Classification of Problems', 'mapped', NULL, NULL, 172, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.2', 'DS', 'Problem Tracking and Resolution', 'mapped', NULL, NULL, 173, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.3', 'DS', 'Problem Closure', 'mapped', NULL, NULL, 174, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS10.4', 'DS', 'Integration of Configuration, Incident and Problem Management', 'mapped', NULL, NULL, 175, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.1', 'DS', 'Business Requirements for Data Management', 'mapped', NULL, NULL, 176, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.2', 'DS', 'Storage and Retention Arrangements', 'mapped', NULL, NULL, 177, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.3', 'DS', 'Media Library Management System', 'mapped', NULL, NULL, 178, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.4', 'DS', 'Disposal', 'mapped', NULL, NULL, 179, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.5', 'DS', 'Backup and Restoration', 'mapped', NULL, NULL, 180, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS11.6', 'DS', 'Security Requirements for Data Management', 'mapped', NULL, NULL, 181, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.1', 'DS', 'Site Selection and Layout', 'mapped', NULL, NULL, 182, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.2', 'DS', 'Physical Security Measures', 'mapped', NULL, NULL, 183, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.3', 'DS', 'Physical Access', 'mapped', NULL, NULL, 184, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.4', 'DS', 'Protection Against Environmental Factors', 'mapped', NULL, NULL, 185, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS12.5', 'DS', 'Physical Facilities Management', 'mapped', NULL, NULL, 186, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.1', 'DS', 'Operations Procedures and Instructions', 'mapped', NULL, NULL, 187, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.2', 'DS', 'Job Scheduling', 'mapped', NULL, NULL, 188, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.3', 'DS', 'IT Infrastructure Monitoring', 'mapped', NULL, NULL, 189, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.4', 'DS', 'Sensitive Documents and Output Devices', 'mapped', NULL, NULL, 190, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('DS13.5', 'DS', 'Preventive Maintenance for Hardware', 'mapped', NULL, NULL, 191, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.1', 'ME', 'Monitoring Approach', 'mapped', NULL, NULL, 192, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.2', 'ME', 'Definition and Collection of Monitoring Data', 'mapped', NULL, NULL, 193, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.3', 'ME', 'Monitoring Method', 'mapped', NULL, NULL, 194, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.4', 'ME', 'Performance Assessment', 'mapped', NULL, NULL, 195, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.5', 'ME', 'Board and Executive Reporting', 'mapped', NULL, NULL, 196, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME1.6', 'ME', 'Remedial Actions', 'mapped', NULL, NULL, 197, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.1', 'ME', 'Monitoring of Internal Control Framework', 'mapped', NULL, NULL, 198, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.2', 'ME', 'Supervisory Review', 'mapped', NULL, NULL, 199, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.3', 'ME', 'Control Exceptions', 'mapped', NULL, NULL, 200, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.4', 'ME', 'Control Self-assessment', 'mapped', NULL, NULL, 201, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.5', 'ME', 'Assurance of Internal Control', 'mapped', NULL, NULL, 202, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.6', 'ME', 'Internal Control at Third Parties', 'mapped', NULL, NULL, 203, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME2.7', 'ME', 'Remedial Actions', 'mapped', NULL, NULL, 204, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.1', 'ME', 'Identification of External Legal, Regulatory and Contractual Compliance Requirements', 'mapped', NULL, NULL, 205, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.2', 'ME', 'Optimisation of Response to External Requirements', 'mapped', NULL, NULL, 206, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.3', 'ME', 'Evaluation of Compliance With External Requirements', 'mapped', NULL, NULL, 207, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.4', 'ME', 'Positive Assurance of Compliance', 'mapped', NULL, NULL, 208, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME3.5', 'ME', 'Integrated Reporting', 'mapped', NULL, NULL, 209, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.1', 'ME', 'Establishment of an IT Governance Framework', 'mapped', NULL, NULL, 210, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.2', 'ME', 'Strategic Alignment', 'deleted', 'In COBIT 5, alignment is considered the result of all governance and management activities.', NULL, 211, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.3', 'ME', 'Value Delivery', 'mapped', NULL, NULL, 212, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.4', 'ME', 'Resource Management', 'mapped', NULL, NULL, 213, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.5', 'ME', 'Risk Management', 'mapped', NULL, NULL, 214, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.6', 'ME', 'Performance Measurement', 'mapped', NULL, NULL, 215, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
    ('ME4.7', 'ME', 'Independent Assurance', 'mapped', NULL, 'The supplied image renders the last code as MEA02-08; Figure 14 gives the MEA02.08 practice.', 216, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
    `domain` = VALUES(`domain`),
    `title` = VALUES(`title`),
    `mapping_status` = VALUES(`mapping_status`),
    `mapping_note` = VALUES(`mapping_note`),
    `review_note` = VALUES(`review_note`),
    `order_no` = VALUES(`order_no`),
    `updated_at` = CURRENT_TIMESTAMP;

INSERT INTO `trs_cobit4_cobit5_mappings`
    (`source_id`, `target_code`, `target_level`, `order_no`, `created_at`, `updated_at`)
SELECT
    `source`.`id`,
    `mapping_rows`.`target_code`,
    `mapping_rows`.`target_level`,
    `mapping_rows`.`order_no`,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
FROM `mst_cobit4_cobit5_mapping_sources` AS `source`
INNER JOIN (
    SELECT 'AC1' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'DSS06.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.02' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 5 AS `order_no`
    UNION ALL SELECT 'AC1' AS `source_code`, 'BAI03.07' AS `target_code`, 'practice' AS `target_level`, 6 AS `order_no`
    UNION ALL SELECT 'AC2' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC3' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC4' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC5' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AC6' AS `source_code`, 'DSS06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.1' AS `source_code`, 'EDM02' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.2' AS `source_code`, 'APO02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.3' AS `source_code`, 'APO02.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.4' AS `source_code`, 'APO02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.4' AS `source_code`, 'APO02.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO1.4' AS `source_code`, 'APO02.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'PO1.5' AS `source_code`, 'APO02.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO1.6' AS `source_code`, 'APO05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.1' AS `source_code`, 'APO03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.2' AS `source_code`, 'APO03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.3' AS `source_code`, 'APO03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO2.4' AS `source_code`, 'APO01.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.1' AS `source_code`, 'APO02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.1' AS `source_code`, 'APO04.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO02.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO02.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO04.03' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO04.04' AS `target_code`, 'practice' AS `target_level`, 5 AS `order_no`
    UNION ALL SELECT 'PO3.2' AS `source_code`, 'APO04.05' AS `target_code`, 'practice' AS `target_level`, 6 AS `order_no`
    UNION ALL SELECT 'PO3.3' AS `source_code`, 'EDM01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.3' AS `source_code`, 'APO04.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO3.4' AS `source_code`, 'APO03.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO3.5' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.1' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.1' AS `source_code`, 'APO01.07' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO4.2' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.3' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.4' AS `source_code`, 'APO01.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.5' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.6' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.7' AS `source_code`, 'APO11.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.9' AS `source_code`, 'APO01.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.10' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.11' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.11' AS `source_code`, 'DSS08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO4.12' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.13' AS `source_code`, 'APO07.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.14' AS `source_code`, 'APO07.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO4.15' AS `source_code`, 'APO01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.1' AS `source_code`, 'APO06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.2' AS `source_code`, 'APO06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.3' AS `source_code`, 'APO06.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.4' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO5.4' AS `source_code`, 'APO06.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO5.5' AS `source_code`, 'APO05.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.1' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.2' AS `source_code`, 'EDM03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.2' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO6.3' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.3' AS `source_code`, 'APO01.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO6.4' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO6.4' AS `source_code`, 'APO01.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO6.5' AS `source_code`, 'APO01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.1' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.1' AS `source_code`, 'APO07.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO7.2' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.3' AS `source_code`, 'APO01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.3' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO7.4' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.5' AS `source_code`, 'APO07.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.6' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.6' AS `source_code`, 'APO07.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO7.7' AS `source_code`, 'APO07.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO7.8' AS `source_code`, 'APO07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.1' AS `source_code`, 'APO11.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.2' AS `source_code`, 'APO11.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.3' AS `source_code`, 'APO11.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.3' AS `source_code`, 'APO11.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO8.4' AS `source_code`, 'APO11.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.5' AS `source_code`, 'APO11.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO8.6' AS `source_code`, 'APO11.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.1' AS `source_code`, 'EDM03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.1' AS `source_code`, 'APO01.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO9.2' AS `source_code`, 'APO12.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.3' AS `source_code`, 'APO12.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.3' AS `source_code`, 'APO12.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO9.4' AS `source_code`, 'APO12.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.4' AS `source_code`, 'APO12.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO9.5' AS `source_code`, 'APO12.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.6' AS `source_code`, 'APO12.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO9.6' AS `source_code`, 'APO12.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO10.1' AS `source_code`, 'BAI01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.2' AS `source_code`, 'BAI01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.3' AS `source_code`, 'BAI01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.4' AS `source_code`, 'BAI01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.5' AS `source_code`, 'BAI01.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.6' AS `source_code`, 'BAI01.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.7' AS `source_code`, 'BAI01.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.8' AS `source_code`, 'BAI01.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.9' AS `source_code`, 'BAI01.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.10' AS `source_code`, 'BAI01.09' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.11' AS `source_code`, 'BAI01.11' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.12' AS `source_code`, 'BAI01.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.13' AS `source_code`, 'BAI01.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'PO10.13' AS `source_code`, 'BAI01.11' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'PO10.14' AS `source_code`, 'BAI01.13' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.1' AS `source_code`, 'BAI02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.2' AS `source_code`, 'BAI02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.3' AS `source_code`, 'BAI02.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI1.4' AS `source_code`, 'BAI02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.1' AS `source_code`, 'BAI03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.2' AS `source_code`, 'BAI03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.3' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI2.4' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI2.5' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.5' AS `source_code`, 'BAI03.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI2.6' AS `source_code`, 'BAI03.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.7' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.7' AS `source_code`, 'BAI03.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI2.8' AS `source_code`, 'BAI03.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.9' AS `source_code`, 'BAI03.09' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI2.10' AS `source_code`, 'BAI03.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.1' AS `source_code`, 'BAI03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.2' AS `source_code`, 'BAI03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.2' AS `source_code`, 'DSS02.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI3.3' AS `source_code`, 'BAI03.10' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.4' AS `source_code`, 'BAI03.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI3.4' AS `source_code`, 'BAI03.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.1' AS `source_code`, 'BAI05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI4.2' AS `source_code`, 'BAI08.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI4.3' AS `source_code`, 'BAI08.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI4.4' AS `source_code`, 'BAI08.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI5.1' AS `source_code`, 'BAI03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI5.2' AS `source_code`, 'APO10.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI5.2' AS `source_code`, 'APO10.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI5.3' AS `source_code`, 'APO10.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI5.4' AS `source_code`, 'APO10.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'AI6.1' AS `source_code`, 'BAI06.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'AI6.2' AS `source_code`, 'BAI06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.3' AS `source_code`, 'BAI06.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.4' AS `source_code`, 'BAI06.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI6.5' AS `source_code`, 'BAI06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.1' AS `source_code`, 'BAI05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.2' AS `source_code`, 'BAI07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.2' AS `source_code`, 'BAI07.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'AI7.3' AS `source_code`, 'BAI07.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.4' AS `source_code`, 'BAI07.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.5' AS `source_code`, 'BAI07.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.6' AS `source_code`, 'BAI07.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.7' AS `source_code`, 'BAI07.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.8' AS `source_code`, 'BAI07.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'AI7.9' AS `source_code`, 'BAI07.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.04' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.05' AS `target_code`, 'practice' AS `target_level`, 5 AS `order_no`
    UNION ALL SELECT 'DS1.1' AS `source_code`, 'APO09.06' AS `target_code`, 'practice' AS `target_level`, 6 AS `order_no`
    UNION ALL SELECT 'DS1.2' AS `source_code`, 'APO09.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.3' AS `source_code`, 'APO09.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.4' AS `source_code`, 'APO09.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.5' AS `source_code`, 'APO09.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS1.6' AS `source_code`, 'APO09.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.1' AS `source_code`, 'APO10.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.2' AS `source_code`, 'APO10.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.3' AS `source_code`, 'APO10.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS2.4' AS `source_code`, 'APO10.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.1' AS `source_code`, 'BAI04.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.2' AS `source_code`, 'BAI04.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.2' AS `source_code`, 'BAI04.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS3.3' AS `source_code`, 'BAI04.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.4' AS `source_code`, 'BAI04.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS3.5' AS `source_code`, 'BAI04.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.1' AS `source_code`, 'DSS04.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.1' AS `source_code`, 'DSS04.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS4.2' AS `source_code`, 'DSS04.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.3' AS `source_code`, 'DSS04.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.4' AS `source_code`, 'DSS04.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.4' AS `source_code`, 'DSS04.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS4.5' AS `source_code`, 'DSS04.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.6' AS `source_code`, 'DSS04.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.7' AS `source_code`, 'DSS04.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.8' AS `source_code`, 'DSS04.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.9' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS4.10' AS `source_code`, 'DSS04.09' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.1' AS `source_code`, 'APO13.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.1' AS `source_code`, 'APO13.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS5.2' AS `source_code`, 'APO13.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.3' AS `source_code`, 'DSS05.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.4' AS `source_code`, 'DSS05.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.5' AS `source_code`, 'DSS05.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.6' AS `source_code`, 'DSS02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.7' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.8' AS `source_code`, 'DSS05.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.9' AS `source_code`, 'DSS05.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.10' AS `source_code`, 'DSS05.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS5.11' AS `source_code`, 'DSS05.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.1' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.2' AS `source_code`, 'APO06.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.3' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS6.4' AS `source_code`, 'APO06.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS7.1' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS7.2' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS7.3' AS `source_code`, 'APO07.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.2' AS `source_code`, 'DSS02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.2' AS `source_code`, 'DSS02.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS8.2' AS `source_code`, 'DSS02.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS8.3' AS `source_code`, 'DSS02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.4' AS `source_code`, 'DSS02.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS8.4' AS `source_code`, 'DSS02.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS8.5' AS `source_code`, 'DSS02.07' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'BAI10.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'BAI10.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'BAI10.04' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS9.1' AS `source_code`, 'DSS02.01' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'DS9.2' AS `source_code`, 'BAI10.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.3' AS `source_code`, 'BAI10.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS9.3' AS `source_code`, 'BAI10.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS9.3' AS `source_code`, 'DSS02.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS10.1' AS `source_code`, 'DSS03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS10.2' AS `source_code`, 'DSS03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS10.3' AS `source_code`, 'DSS03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS10.3' AS `source_code`, 'DSS03.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS10.4' AS `source_code`, 'DSS03.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.1' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.2' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.2' AS `source_code`, 'DSS06.04' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS11.3' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.4' AS `source_code`, 'DSS05.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.5' AS `source_code`, 'DSS04.08' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.6' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS11.6' AS `source_code`, 'DSS05.08' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS11.6' AS `source_code`, 'DSS06.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS12.1' AS `source_code`, 'DSS01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.1' AS `source_code`, 'DSS01.05' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'DS12.1' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'DS12.2' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.3' AS `source_code`, 'DSS05.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.4' AS `source_code`, 'DSS01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS12.5' AS `source_code`, 'DSS01.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.1' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.2' AS `source_code`, 'DSS01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.3' AS `source_code`, 'DSS01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.4' AS `source_code`, 'DSS05.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'DS13.5' AS `source_code`, 'BAI09.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.1' AS `source_code`, 'MEA01.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.2' AS `source_code`, 'MEA01.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.2' AS `source_code`, 'MEA01.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME1.3' AS `source_code`, 'MEA01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.4' AS `source_code`, 'MEA01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.5' AS `source_code`, 'MEA01.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME1.6' AS `source_code`, 'MEA01.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.1' AS `source_code`, 'MEA02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.1' AS `source_code`, 'MEA02.02' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME2.2' AS `source_code`, 'MEA02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.3' AS `source_code`, 'MEA02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.4' AS `source_code`, 'MEA02.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.5' AS `source_code`, 'MEA02.06' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.5' AS `source_code`, 'MEA02.07' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME2.5' AS `source_code`, 'MEA02.08' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'ME2.6' AS `source_code`, 'MEA02.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME2.7' AS `source_code`, 'MEA02.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.1' AS `source_code`, 'MEA03.01' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.2' AS `source_code`, 'MEA03.02' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.3' AS `source_code`, 'MEA03.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.4' AS `source_code`, 'MEA03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME3.5' AS `source_code`, 'MEA03.04' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.1' AS `source_code`, 'EDM01' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.3' AS `source_code`, 'EDM02' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.4' AS `source_code`, 'EDM04' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.5' AS `source_code`, 'EDM03' AS `target_code`, 'process' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM01.03' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM02.03' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM03.03' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'ME4.6' AS `source_code`, 'EDM04.03' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.05' AS `target_code`, 'practice' AS `target_level`, 1 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.06' AS `target_code`, 'practice' AS `target_level`, 2 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.07' AS `target_code`, 'practice' AS `target_level`, 3 AS `order_no`
    UNION ALL SELECT 'ME4.7' AS `source_code`, 'MEA02.08' AS `target_code`, 'practice' AS `target_level`, 4 AS `order_no`
) AS `mapping_rows` ON `mapping_rows`.`source_code` = `source`.`code`
ON DUPLICATE KEY UPDATE
    `target_level` = VALUES(`target_level`),
    `order_no` = VALUES(`order_no`),
    `updated_at` = CURRENT_TIMESTAMP;

COMMIT;

-- Register the migration so 'php artisan migrate' does not run it again (it would re-seed from config)
INSERT INTO `migrations` (`migration`, `batch`)
SELECT m.name, 99 FROM (SELECT '2026_11_06_000001_create_cobit4_cobit5_mapping_tables' AS name) m
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = m.name);
