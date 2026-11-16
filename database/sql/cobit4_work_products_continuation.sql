-- COBIT 4.1 Figure 17 continuation: 123 supplied rows, PO2-WP4 through ME4-WP5.
-- Requires existing mst_wp_4.1, trs_wp_to_obj_4.1 and mst_cobit4_processes.
-- 15 overlapping rows update in place; with the original 24 rows, 108 are new (132 total).
-- PO9-WP43 and PO9-WP54 are kept exactly as printed in the supplied image.
-- Source: database/data/cobit4-work-products-continuation.json.
-- Spelling, punctuation, bullets, Scope note and repeated-source notes are preserved.
-- Layout-only line wrapping is joined with spaces. Existing rows not listed here are untouched.
-- Select the intended database before importing. Can be imported again without duplicate rows.
SET NAMES utf8mb4;

START TRANSACTION;

INSERT INTO `mst_wp_4.1` (`wp_id`, `wp`, `description`, `order_no`, `created_at`, `updated_at`)
VALUES
    ('PO2-WP4', 'Information architecture', 'A component of IT architecture (together with applications and technology)', 10, NOW(), NOW()),
    ('PO2-WP5', 'Assigned data classifications', 'An enterprise scheme for classifying data by factors such as criticality, sensitivity and ownership', 11, NOW(), NOW()),
    ('PO2-WP6', 'Classification procedures and tools', 'Procedures and tools to enable data to be classified, created in line with organisation policies', 12, NOW(), NOW()),
    ('PO3-WP1', 'Technology opportunities', 'Identification of technology opportunities for the business', 13, NOW(), NOW()),
    ('PO3-WP2', 'Technology standards', 'Organisational standards for infrastructure products and practices together with guidance on the selection of technology', 14, NOW(), NOW()),
    ('PO3-WP3', 'Regular ‘state of technology’ updates', 'An assessment of current and future technology trends and the potential opportunities and risks to the organisation', 15, NOW(), NOW()),
    ('PO3-WP4', 'Technology infrastructure plan', 'A plan for the technology, human resources and facilities that enables the current and future processing and use of applications', 16, NOW(), NOW()),
    ('PO3-WP5', 'Infrastructure requirements', 'The required set of hardware, software and facilities that integrates an organisation’s IT assets

Scope note:  Specifically, this includes the equipment (including servers, routers, switches and cabling), software, services and products used in storing, processing, transmitting and displaying all forms of information for the organisation’s users.', 17, NOW(), NOW()),
    ('PO4-WP1', 'IT process framework', 'A process framework that includes IT process structure and relationships (e.g., to manage process gaps and overlaps), ownership, maturity, performance measurement, improvement, compliance, quality targets, and plans to achieve them. It should provide integration amongst the processes that are specific to IT, enterprise portfolio management, business processes and business change processes. The IT process framework should be integrated into a quality management system (QMS) and the internal control framework.', 18, NOW(), NOW()),
    ('PO4-WP2', 'Documented system owners', 'An application system owner is the individual who has been assigned ultimate responsibility for a system.', 19, NOW(), NOW()),
    ('PO4-WP3', 'IT organisation and relationships', 'A specification of internal and external IT organisational structure and responsibilities', 20, NOW(), NOW()),
    ('PO4-WP4', 'Documented roles and responsibilities', 'Roles and responsibilities for IT personnel and end users that delineate between IT personnel and end-user authority, responsibilities and accountability for meeting the organisation’s needs', 21, NOW(), NOW()),
    ('PO5-WP1', 'Cost-benefit reports', 'A report weighing the total expected costs against the total expected benefits of one or more actions to choose the best or most profitable option', 22, NOW(), NOW()),
    ('PO5-WP2', 'IT budgets', 'A budget reflecting the priorities established by the enterprise’s portfolio of IT-enabled investment programmes, and including the ongoing costs of operating and maintaining the current infrastructure', 23, NOW(), NOW()),
    ('PO5-WP3', 'Updated IT service portfolio', 'A grouping of IT services managed and monitored to optimise business value', 24, NOW(), NOW()),
    ('PO5-WP4', 'Updated IT project portfolio', 'A grouping of IT projects managed and monitored to optimise business value', 25, NOW(), NOW()),
    ('PO6-WP1', 'Enterprise IT control framework', 'A set of fundamental controls that facilitates the discharge of business process owner responsibilities to prevent financial or information loss in an organisation', 26, NOW(), NOW()),
    ('PO6-WP2', 'IT policies, standardised procedures', 'High-level statements of management intent and direction', 27, NOW(), NOW()),
    ('PO7-WP1', 'IT HR policy and procedures', 'Policies and procedures for IT HR, including recruitment and selection, conditions of employment, performance learning and development, behaviour and code of conduct, administration and support', 28, NOW(), NOW()),
    ('PO7-WP2', 'IT skills matrix', 'Details of skills required by the IT organisation to meet business objectives', 29, NOW(), NOW()),
    ('PO7-WP3', 'Job descriptions', 'Outline of responsibilities of a position. It may often include to whom the position reports and specifications such as the qualifications or skills needed by the person in the job.', 30, NOW(), NOW()),
    ('PO7-WP4', 'Users’ skills and competencies, including individual training', 'Identification of IT knowledge or skill required to produce outputs in an organisation using IT. It should identify the individual competencies that need to be attained by a specific staff and the training required to achieve those competencies.', 31, NOW(), NOW()),
    ('PO7-WP5', 'Roles and responsibilities', 'Roles and responsibilities for IT personnel and end users that delineate between IT personnel and end-user authority, responsibilities and accountability for meeting the organisation’s needs', 32, NOW(), NOW()),
    ('PO8-WP1', 'Acquisition standards', 'Standards that define the process and activities required to acquire application software or an upgrade, including evaluation of the supplier’s financial stability, track record, resources and references from existing customers', 33, NOW(), NOW()),
    ('PO8-WP2', 'Development standards', 'Standards that define the process and activities necessary to produce quality software. It should include detailed procedures and practices for key IT development practices.', 34, NOW(), NOW()),
    ('PO8-WP3', 'Quality standards and metrics requirements', 'A system that outlines the policies and procedures necessary to improve and control the various processes that will ultimately lead to improved organisation performance', 35, NOW(), NOW()),
    ('PO8-WP4', 'Quality improvement actions', 'The quality improvement actions that are planned, who will be responsible and when they will be implemented', 36, NOW(), NOW()),
    ('PO9-WP1', 'Risk assessment', 'A process used to identify and evaluate risks and their potential effects

Scope note:  Risk assessment includes assessing the critical functions necessary for an organisation to continue business operations, defining the controls in place to reduce organisation exposure and evaluating the cost for such controls. Risk analysis often involves an evaluation of the probabilities of a particular event.', 37, NOW(), NOW()),
    ('PO9-WP2', 'Risk reporting', 'A report to senior management of high-risk actions, responsibilities and proposed actions', 38, NOW(), NOW()),
    ('PO9-WP43', 'IT-related risk management guidelines', 'The co-ordinated activities to direct and control an organisation with regard to risk [ISO/IEC Guide 73:2002]', 39, NOW(), NOW()),
    ('PO9-WP54', 'IT-related risk remedial action plans', 'The actions planned to address identified risks, who will be responsible and when they will be implemented', 40, NOW(), NOW()),
    ('PO10-WP1', 'Project performance reports', 'Reports on the status of projects against planned milestones and resource usage, highlighting any issues potentially impacting the achievement of planned outcomes', 41, NOW(), NOW()),
    ('PO10-WP2', 'Project risk management plan', 'Describes how risk management will be structured and performed on the project. It becomes a subset of the project management plan.', 42, NOW(), NOW()),
    ('PO10-WP3', 'Project management guidelines', 'Guidance on the approach to be adopted for defining the scope and boundaries of managing projects, as well as the method to be adopted and applied to each project undertaken', 43, NOW(), NOW()),
    ('PO10-WP4', 'Detailed project plans', 'A formal, approved document used to guide both project execution and project control. The primary uses of the project plan are to document planning assumptions and decisions, facilitate communication among stakeholders, and document approved scope, cost and schedule baselines. A project plan may be summarised or detailed.', 44, NOW(), NOW()),
    ('PO10-WP5', 'Updated IT project portfolio', 'A grouping of IT projects managed and monitored to optimise business value (see PO1)', 45, NOW(), NOW()),
    ('AI1-WP1', 'Business requirements feasibility study', 'A phase of a system development life cycle (SDLC) methodology that researches the feasibility and adequacy of resources for the development or acquisition of a system solution to a user need', 46, NOW(), NOW()),
    ('AI2-WP1', 'Application security controls specification', 'Controls that achieve the business objectives of timely, accurate and reliable information. They consist of the manual and automated activities that ensure that information conforms to certain criteria—what COBIT refers to as business requirements for information. Those criteria are effectiveness, efficiency, confidentiality, integrity, availability, compliance and reliability.', 47, NOW(), NOW()),
    ('AI2-WP2', 'Application and package software knowledge', 'Also known as an application or application system, it is computer software designed to help the user to perform singular or multiple related specific tasks. An application differs from an operating system (which runs a computer), a utility (which performs maintenance or general-purpose chores) and a programming language (with which computer programs are created).', 48, NOW(), NOW()),
    ('AI2-WP3', 'Procurement decisions', 'Documented specification (at a high level) of the software and services to be acquired. It is used as a basis for procurement action.', 49, NOW(), NOW()),
    ('AI2-WP4', 'Initial planned SLAs', 'An agreement, preferably documented, between a service provider and the customer(s)/user(s) that defines minimum performance targets for a service and how they will be measured', 50, NOW(), NOW()),
    ('AI2-WP5', 'Availability, continuity and recovery specification', 'Availability and service continuity requirements should be identified on the basis of business plans, SLAs and risk assessments. Requirements should include access rights and response times as well as end-to-end availability of system components. Requirements should include, but not be limited to, access rights, service response times and end-to-end availability of services.', 51, NOW(), NOW()),
    ('AI3-WP1', 'Procurement decisions', 'Acquired (or developed) system configured to meet business-defined requirements', 52, NOW(), NOW()),
    ('AI3-WP2', 'Configured system that is tested/installed', 'System configured to meet the business-defined requirements after testing and installation in the production environment', 53, NOW(), NOW()),
    ('AI3-WP3', 'Physical environment requirements', 'The physical environment required for IT assets. It includes:
• The physical sites for IT equipment
• Physical security measures—Definition and implementation of procedures to grant, limit and revoke access to premises, buildings and areas
• Protection against environmental factors
• Physical facilities management, including power and communications equipment', 54, NOW(), NOW()),
    ('AI3-WP4', 'Updates for technology standards', 'Changes to organisational standards for infrastructure products and practices based on decisions made during acquiring and maintaining technology infrastructure', 55, NOW(), NOW()),
    ('AI3-WP5', 'System monitoring requirements', 'The specification of the information required to be provided to enable effective monitoring of the application, system or infrastructure', 56, NOW(), NOW()),
    ('AI3-WP6', 'Infrastructure knowledge', 'Knowledge about available IT infrastructure, where IT infrastructure is the set of hardware, software and facilities that integrates an organisation’s IT assets (see PO3)', 57, NOW(), NOW()),
    ('AI3-WP7', 'Initial planned OLAs', 'An internal agreement covering the delivery of services that support the IT organisation in its delivery of services', 58, NOW(), NOW()),
    ('AI4-WP1', 'User, operational, support, technical and administration manuals', 'Technical communication documents intended to give assistance to people using, supporting or administering a particular system', 59, NOW(), NOW()),
    ('AI4-WP2', 'Knowledge transfer requirements for a solution’s implementation', 'The specification of the knowledge that needs to be transferred to business management, end users, and operations and support staff to enable effective management, use, operation and support of the part(s) of the application system or IT infrastructure', 60, NOW(), NOW()),
    ('AI4-WP3', 'Training materials', 'Material used to train business management, end users, and operations and support staff to enable effective management, use, operation and support of the part(s) of the application system or IT infrastructure', 61, NOW(), NOW()),
    ('AI5-WP1', 'Third-party relationship management requirements', 'The specification of the requirements for managing customer-supplier relationships. It includes clearly defined roles, responsibilities and expectations in third-party agreements as well as monitoring processes.', 62, NOW(), NOW()),
    ('AI5-WP2', 'Procured items', 'Hardware or software procured as part of the acquisition process', 63, NOW(), NOW()),
    ('AI5-WP3', 'Contractual arrangements', 'A formal agreement regarding expectations and responsibilities between the parties', 64, NOW(), NOW()),
    ('AI6-WP1', 'Change process description', 'A documented procedure to record, classify, assess and approve RFCs', 65, NOW(), NOW()),
    ('AI6-WP2', 'Change process procedures', 'A documented procedure to apply changes to the production environment', 66, NOW(), NOW()),
    ('AI6-WP3', 'Change status reports', 'A log of all change requests and the status in terms of assessment, approval (or rejection) and implementation', 67, NOW(), NOW()),
    ('AI6-WP4', 'Change authorisation', 'A record of the approval (or authorisation) for a change', 68, NOW(), NOW()),
    ('AI7-WP1', 'Released configuration items (CIs)', 'Components of an infrastructure—or an item, such as a request for change, associated with an infrastructure—that is (or is to be) under the control of configuration management

Scope note:  CIs may vary widely in complexity, size and type, from an entire system (including all hardware, software and documentation) to a single module or a minor hardware component.', 69, NOW(), NOW()),
    ('AI7-WP2', 'Known and accepted errors', 'The documentation of known and accepted errors identified in testing (for identification in documented procedures together with any additional procedures required)', 70, NOW(), NOW()),
    ('AI7-WP3', 'Promotion to production', 'Handover of a system from development to testing to operations in line with an implementation plan', 71, NOW(), NOW()),
    ('AI7-WP4', 'Software release and distribution plan', 'Documented plan on how to roll out the release. It should include identifying requirements for ensuring sign-off, packaging, regression testing, distribution, handover, status tracking, backup procedures and user notification.', 72, NOW(), NOW()),
    ('AI7-WP5', 'Post-implementation review', 'An assessment and review of the completed working solution performed after a period of live running', 73, NOW(), NOW()),
    ('AI7-WP6', 'Internal control monitoring', 'Identified key controls that should be subject to monitoring, including frequency, possible procedures and thresholds for monitoring', 74, NOW(), NOW()),
    ('DS1-WP1', 'Contract review report', 'A report reviewing contracted IT services', 75, NOW(), NOW()),
    ('DS1-WP2', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose', 76, NOW(), NOW()),
    ('DS1-WP3', 'New/updated service requirements', 'New or updated specifications of services required and defined by business needs, to be addressed by IT', 77, NOW(), NOW()),
    ('DS1-WP4', 'SLAs', 'An agreement, preferably documented, between a service provider and the customer(s)/user(s) that defines minimum performance targets for a service and how they will be measured', 78, NOW(), NOW()),
    ('DS1-WP5', 'OLAs', 'An internal agreement covering the delivery of services that support the IT organisation in its delivery of services
(see AI3)', 79, NOW(), NOW()),
    ('DS1-WP6', 'Updated IT service portfolio', 'The updated set of services that are managed by a service provider; is used to manage the entire life cycle of all IT services', 80, NOW(), NOW()),
    ('DS2-WP1', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 81, NOW(), NOW()),
    ('DS2-WP2', 'Supplier catalogue', 'A catalogue of third-party suppliers of IT products and services', 82, NOW(), NOW()),
    ('DS2-WP3', 'Supplier risks', 'Risks associated with the engagement or provisioning of third-party IT products and services', 83, NOW(), NOW()),
    ('DS3-WP1', 'Performance and capacity information', 'Information on IT resources available or needed to process IT workloads', 84, NOW(), NOW()),
    ('DS3-WP2', 'Performance and capacity plan (requirements)', 'A plan for the review of performance and capacity of IT resources to ensure that cost-justifiable capacity and performance are available to process the agreed-upon workloads as determined by the SLAs', 85, NOW(), NOW()),
    ('DS3-WP3', 'Required changes', 'Changes required to be made to infrastructure and applications', 86, NOW(), NOW()),
    ('DS3-WP4', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 87, NOW(), NOW()),
    ('DS4-WP1', 'Contingency test results', 'Test results for contingency arrangements that ensure that IT systems can effectively recover from systems failure or disruption in the case of an adverse event', 88, NOW(), NOW()),
    ('DS4-WP2', 'Criticality of IT configuration items', 'The criticality of particular IT infrastructure components that are under the control of configuration management', 89, NOW(), NOW()),
    ('DS4-WP3', 'Backup storage and protection plan', 'A plan for copying and storing data and other IT resources to protect against loss of integrity or availability of the original that includes procedures for backup and restoration of systems, applications, data and documentation according to business need, continuity plan and security requirements', 90, NOW(), NOW()),
    ('DS4-WP4', 'Incident/disaster thresholds', 'The trigger points marking a major disruption on key functions and processes', 91, NOW(), NOW()),
    ('DS4-WP5', 'Disaster service requirements, including roles and responsibilities', 'Definition of requirements designed to return the organisation to an acceptable condition, including restoration of critical business functions, from an interruption in services', 92, NOW(), NOW()),
    ('DS4-WP6', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 93, NOW(), NOW()),
    ('DS5-WP1', 'Security incident definition', 'Specification of the characteristics of security incidents that are used by the incident and problem management process', 94, NOW(), NOW()),
    ('DS5-WP2', 'Specific training requirements on security awareness', 'Requirements definition for the awareness training of individuals who potentially have access to the organisation’s information', 95, NOW(), NOW()),
    ('DS5-WP3', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 96, NOW(), NOW()),
    ('DS5-WP4', 'Required security changes', 'Change requirements in the context of IT security', 97, NOW(), NOW()),
    ('DS5-WP5', 'Security threats and vulnerabilities', 'Potential for threat sources to successfully exploit lapses in security either accidentally or intentionally', 98, NOW(), NOW()),
    ('DS5-WP6', 'IT security plan and policies', 'A high-level document defining an organisation’s information security philosophy, commitment preparedness and response in the context of business, risk and compliance requirements', 99, NOW(), NOW()),
    ('DS6-WP1', 'IT financials', 'Financial information pertaining to IT-related activities and operations', 100, NOW(), NOW()),
    ('DS6-WP2', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 101, NOW(), NOW()),
    ('DS7-WP1', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 102, NOW(), NOW()),
    ('DS7-WP2', 'Required documentation updates', 'Requirements for documentation updates pertaining to IT operations and use', 103, NOW(), NOW()),
    ('DS8-WP1', 'Service requests/RFCs', 'A user request for advice or information; for a standard change; for access to an IT service, e.g., to reset a password; to provide standard IT services for a new user. Service requests are usually handled by a service desk, and do not require a formal RFC to be submitted.', 104, NOW(), NOW()),
    ('DS8-WP2', 'Incident reports', 'Reports of unexpected events, an interruption or unplanned quality reduction to an IT service, which may involve an attack or series of attacks (compromise and/or breach of security)', 105, NOW(), NOW()),
    ('DS8-WP3', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 106, NOW(), NOW()),
    ('DS8-WP4', 'User satisfaction reports', 'Reports on satisfaction levels of IT users on one or more of the following: IT services, application performance, incident/problem resolution, education and training', 107, NOW(), NOW()),
    ('DS9-WP1', 'IT configuration/asset details', 'Details regarding a component of an infrastructure—or an item, such as a request for change, associated with an infrastructure—that is (or is to be) under the control of configuration management', 108, NOW(), NOW()),
    ('DS9-WP2', 'RFC (where and how to apply the fix)', 'A formal proposal for a change to be made, which includes details of the proposed change', 109, NOW(), NOW()),
    ('DS9-WP3', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 110, NOW(), NOW()),
    ('DS10-WP1', 'RFC (where and how to apply the fix)', 'A formal proposal for a change to be made, which includes details of the proposed change
(Repeated from DS9-WP2)', 111, NOW(), NOW()),
    ('DS10-WP2', 'Problem records', 'Records of the unknown underlying cause of one or more incidents. The cause is not usually known at the time a problem record is created, and the problem management process is responsible for further investigation.', 112, NOW(), NOW()),
    ('DS10-WP3', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 113, NOW(), NOW()),
    ('DS10-WP4', 'Known problems, known errors and workarounds', 'Known error:  A problem that has a documented root cause and a workaround. Known errors are created and managed throughout their life cycle by problem management.', 114, NOW(), NOW()),
    ('DS11-WP1', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 115, NOW(), NOW()),
    ('DS11-WP2', 'Operator instructions for data management outcomes', 'Procedural instructions for data storage/retention/archival arrangements, disposal, backup/restoration, and compliance with security requirements', 116, NOW(), NOW()),
    ('DS12-WP1', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 117, NOW(), NOW()),
    ('DS13-WP1', 'Incident tickets', 'An acknowledgement generated upon the initial lodgment of an incident', 118, NOW(), NOW()),
    ('DS13-WP2', 'Error logs', 'A serial or chronological record of design flaws or malfunctions that cause failures of one or more configuration items or IT services. Errors include mistakes made by persons or a faulty process that affects a configuration item or IT service.', 119, NOW(), NOW()),
    ('DS13-WP3', 'Process performance reports', 'Reports for the extent to which the execution of processes achieve their purpose

(Repeated from DS1-WP2)', 120, NOW(), NOW()),
    ('ME1-WP1', 'Performance input to IT planning', 'Performance in terms of IT’s contribution to business objectives, functionality, stability, complexity, costs, strengths and weaknesses', 121, NOW(), NOW()),
    ('ME1-WP2', 'Remedial action plans', 'Plan (including timeframe) showing remedial action in place to address non-compliance issues', 122, NOW(), NOW()),
    ('ME1-WP3', 'Historic risk trends and events', 'A monitoring report that is part of the enterprise risk management (ERM) system that continuously identifies risk trends and events', 123, NOW(), NOW()),
    ('ME1-WP4', 'Process performance reports', 'Reporting on the performance of selected processes against agreed performance indicators', 124, NOW(), NOW()),
    ('ME2-WP1', 'Report on effectiveness of IT controls', 'Report outlining effectiveness of IT controls based on management assessment and independent assurance reviews', 125, NOW(), NOW()),
    ('ME3-WP1', 'Catalogue of legal and regulatory requirements related to IT service delivery', 'Reporting outlining legal and regulatory requirements and their impact on IT service delivery', 126, NOW(), NOW()),
    ('ME3-WP2', 'Report on compliance of IT activities with external legal and regulatory requirements', 'Report outlining compliance of IT activities with external legal and regulatory requirements based on management assessment and independent assurance reviews', 127, NOW(), NOW()),
    ('ME4-WP1', 'Process framework improvements', 'Action taken to change an enterprise’s framework (its strategies, goals, policies responsibilities and activities) so that they meet the enterprise’s business goals', 128, NOW(), NOW()),
    ('ME4-WP2', 'Report on IT governance status', 'An assessment of the effectiveness of IT governance based on independent assurance', 129, NOW(), NOW()),
    ('ME4-WP3', 'Expected business outcomes of IT-enabled business investments', 'Documentation of an observable result or change in business performance, possibly supported by transaction-based metrics, resulting from new initiatives based on new or changed IT', 130, NOW(), NOW()),
    ('ME4-WP4', 'Enterprise strategic direction for IT', 'A monitoring report of the enterprise governance of IT that identifies strategic issues such as technology capabilities, which creates a shared understanding between business and IT', 131, NOW(), NOW()),
    ('ME4-WP5', 'Enterprise appetite for IT risks', 'A statement of approved risk appetite for IT based on and consistent with the enterprise risk appetite statement', 132, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `wp` = VALUES(`wp`),
    `description` = VALUES(`description`),
    `order_no` = VALUES(`order_no`),
    `updated_at` = NOW();

INSERT INTO `trs_wp_to_obj_4.1` (`process_id`, `wp_id`, `created_at`, `updated_at`)
SELECT p.`id`, w.`wp_id`, NOW(), NOW()
FROM `mst_cobit4_processes` AS p
INNER JOIN `mst_focusarea` AS f ON f.`id` = p.`focus_area_id` AND f.`version` = '4.1'
INNER JOIN `mst_wp_4.1` AS w ON p.`code` = SUBSTRING_INDEX(w.`wp_id`, '-', 1)
WHERE w.`wp_id` IN (
    'PO2-WP4', 'PO2-WP5', 'PO2-WP6', 'PO3-WP1', 'PO3-WP2', 'PO3-WP3', 'PO3-WP4', 'PO3-WP5', 'PO4-WP1', 'PO4-WP2', 'PO4-WP3', 'PO4-WP4', 'PO5-WP1', 'PO5-WP2', 'PO5-WP3', 'PO5-WP4', 'PO6-WP1', 'PO6-WP2', 'PO7-WP1', 'PO7-WP2', 'PO7-WP3', 'PO7-WP4', 'PO7-WP5', 'PO8-WP1', 'PO8-WP2', 'PO8-WP3', 'PO8-WP4', 'PO9-WP1', 'PO9-WP2', 'PO9-WP43', 'PO9-WP54', 'PO10-WP1', 'PO10-WP2', 'PO10-WP3', 'PO10-WP4', 'PO10-WP5', 'AI1-WP1', 'AI2-WP1', 'AI2-WP2', 'AI2-WP3', 'AI2-WP4', 'AI2-WP5', 'AI3-WP1', 'AI3-WP2', 'AI3-WP3', 'AI3-WP4', 'AI3-WP5', 'AI3-WP6', 'AI3-WP7', 'AI4-WP1', 'AI4-WP2', 'AI4-WP3', 'AI5-WP1', 'AI5-WP2', 'AI5-WP3', 'AI6-WP1', 'AI6-WP2', 'AI6-WP3', 'AI6-WP4', 'AI7-WP1', 'AI7-WP2', 'AI7-WP3', 'AI7-WP4', 'AI7-WP5', 'AI7-WP6', 'DS1-WP1', 'DS1-WP2', 'DS1-WP3', 'DS1-WP4', 'DS1-WP5', 'DS1-WP6', 'DS2-WP1', 'DS2-WP2', 'DS2-WP3', 'DS3-WP1', 'DS3-WP2', 'DS3-WP3', 'DS3-WP4', 'DS4-WP1', 'DS4-WP2', 'DS4-WP3', 'DS4-WP4', 'DS4-WP5', 'DS4-WP6', 'DS5-WP1', 'DS5-WP2', 'DS5-WP3', 'DS5-WP4', 'DS5-WP5', 'DS5-WP6', 'DS6-WP1', 'DS6-WP2', 'DS7-WP1', 'DS7-WP2', 'DS8-WP1', 'DS8-WP2', 'DS8-WP3', 'DS8-WP4', 'DS9-WP1', 'DS9-WP2', 'DS9-WP3', 'DS10-WP1', 'DS10-WP2', 'DS10-WP3', 'DS10-WP4', 'DS11-WP1', 'DS11-WP2', 'DS12-WP1', 'DS13-WP1', 'DS13-WP2', 'DS13-WP3', 'ME1-WP1', 'ME1-WP2', 'ME1-WP3', 'ME1-WP4', 'ME2-WP1', 'ME3-WP1', 'ME3-WP2', 'ME4-WP1', 'ME4-WP2', 'ME4-WP3', 'ME4-WP4', 'ME4-WP5'
)
ON DUPLICATE KEY UPDATE
    `wp_id` = VALUES(`wp_id`);

COMMIT;

-- Verification after import:
-- SELECT COUNT(*) AS total_wp FROM `mst_wp_4.1`;
-- SELECT p.code, COUNT(*) AS total_wp
-- FROM `trs_wp_to_obj_4.1` r JOIN `mst_cobit4_processes` p ON p.id = r.process_id
-- GROUP BY p.id, p.code ORDER BY FIELD(p.domain_code, 'PO','AI','DS','ME'), p.id;
