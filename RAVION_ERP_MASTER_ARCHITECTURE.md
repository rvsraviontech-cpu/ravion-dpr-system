# RAVION ERP MASTER ARCHITECTURE

# 1. SYSTEM OVERVIEW

## Project Name

Ravion Homes Execution Intelligence System

## System Purpose

The Ravion ERP system is being developed as a complete construction execution-control platform for Ravion Homes.

This system is not a simple DPR application.

It must support:

- daily execution tracking
- project location hierarchy
- labour tracking
- contractor tracking
- material control
- inventory readiness
- PMO verification
- accountant verification
- CEO-level dashboards
- future BOQ integration
- future Odoo integration
- audit trail
- leakage prevention
- approval workflows
- long-term execution analytics

---

# 2. CORE SYSTEM PHILOSOPHY

```text
Site Engineer reports facts
PMO verifies facts
Accountant verifies material/cost
CEO sees decisions & exceptions

3. NON-NEGOTIABLE SYSTEM RULES
Hidden Cost-Code Rule

Site engineers must never see:

RH cost codes
BOQ rates
purchase rates
contractor rates
profitability
Odoo accounting tags
analytic account codes

Backend must silently store:

RH cost code
Odoo type code
UOM
BOQ mapping
analytic account
inventory bucket
future Odoo IDs
Dropdown Rule

Site engineers should use:

dropdowns
searchable dropdowns
controlled masters
mapping layers

Free typing should be avoided wherever possible.

No Hard Delete Rule

The system should use:

active/inactive status
audit logging
correction workflow

Hard deletes should be avoided.

Master Table Rule

All transactional modules must use master tables and IDs instead of free-text values.

Examples:

Project → project_id
Activity → activity_id
Material → material_id
Contractor → contractor_id

Reason:

reporting accuracy
inventory calculations
analytics
future ERP integrations
duplicate prevention
typo prevention
DPR Philosophy

DPR is not the only data-entry screen.

DPR should become:

Daily Consolidation Report

Independent operational modules should work during the day:

Labour Reporting
Material Received
Material Consumed
Material Required
Machinery Usage
Site Issues
Photos
Planning

DPR will later consolidate these entries.

4. CURRENT COMPLETED MODULES
Authentication & Roles

Implemented:

login
role-based access
Admin role
PMO role
DGM role
Engineer role
Accountant role
CEO role

Role middleware uses:

auth()->user()->role->name
Project Master

Implemented:

project creation
project listing
project edit
project status
project assignment

Project access rule:

Admin / PMO / DGM can access all active projects.
Engineers access only assigned projects through project_user mapping.
Location Hierarchy Engine

Implemented hierarchy:

Project
→ Block
→ Floor
→ Unit
→ Room
→ Sub-space

Implemented project-specific tables:

project_blocks
project_floors
project_units
project_rooms
project_subspaces

Features completed:

create
edit
activate/deactivate
project-wise management
master dropdown integration
Standard Location Masters

Implemented:

location_block_masters
location_floor_masters
location_unit_masters
location_room_masters
location_subspace_masters

Purpose:

standardize dropdown values
prevent spelling duplication
maintain reporting consistency
avoid entries like Flat 101, Flats 101, Flat-101
Activity Mapping Engine

Implemented:

activity_divisions
activities
activity_mappings
Excel import engine
545 activity mappings imported
532 unique visible activities created
activity mappings linked to divisions
activity division filtering added to activity selection

Purpose:

hidden RH cost-code mapping
Odoo-ready backend mapping
activity dropdown intelligence
future BOQ readiness
Project Locations UI

Implemented:

collapsible add sections
grouped hierarchy forms
grouped hierarchy tables
edit functionality
activate/deactivate functionality
master dropdown usage
Labour Reporting Module

Implemented as independent operational module.

Material Categories Master

Implemented.

Materials Master

Implemented.

Material Received Module

Implemented.

Material Consumption Module

Status: completed

Implemented:

migration
model
controller foundation
index page
create page
store entry
project hierarchy filtering
activity division → activity filtering
material category → material filtering

Pending:

Sidebar ERP Navigation

Implemented
view page
edit/update
submit workflow
approve workflow

Implemented grouped navigation:

Dashboard
Masters
Daily Execution
Planning & Controls
PMO & Verification
Reports
Administration

5. CURRENT IMPLEMENTED DATABASE STRUCTURE
users

Purpose:
System users.

Important Relationships:

belongsTo Role
belongsToMany Project

Used for:

Admin
PMO
DGM
Engineer
Accountant
CEO
roles

Purpose:
Stores user roles.

Expected roles:

Admin
PMO
DGM
Engineer
Accountant
CEO
projects

Purpose:
Project master.

Common Fields:

id
project_name
project_code
location
status
created_at
updated_at

Relationships:

hasMany ProjectBlock
hasMany ProjectFloor
hasMany ProjectUnit
hasMany ProjectRoom
hasMany ProjectSubspace
hasMany DPR
belongsToMany User through project_user
project_user

Purpose:
Assign users/engineers to projects.

Relationships:

project_id
user_id

Usage:

Engineers see only assigned projects.
Admin / PMO / DGM can access all active projects.
5A. LOCATION MASTER TABLES
location_block_masters

Fields:

id
name
type
is_active
remarks
created_at
updated_at
location_floor_masters

Fields:

id
name
sequence
is_active
remarks
created_at
updated_at
location_unit_masters

Fields:

id
name
type
is_active
remarks
created_at
updated_at
location_room_masters

Fields:

id
name
room_type
is_active
remarks
created_at
updated_at
location_subspace_masters

Fields:

id
name
type
is_active
remarks
created_at
updated_at
5B. PROJECT LOCATION TABLES
project_blocks

Fields:

id
project_id
name
code
type
is_active
remarks
created_at
updated_at

Relationships:

belongsTo Project
hasMany ProjectFloor
project_floors

Fields:

id
project_id
project_block_id
name
sequence
is_active
remarks
created_at
updated_at

Relationships:

belongsTo Project
belongsTo ProjectBlock
hasMany ProjectUnit
project_units

Fields:

id
project_id
project_block_id
project_floor_id
name
type
is_active
remarks
created_at
updated_at

Relationships:

belongsTo Project
belongsTo ProjectBlock
belongsTo ProjectFloor
hasMany ProjectRoom
project_rooms

Fields:

id
project_id
project_block_id
project_floor_id
project_unit_id
name
room_type
is_active
remarks
created_at
updated_at

Relationships:

belongsTo Project
belongsTo ProjectBlock
belongsTo ProjectFloor
belongsTo ProjectUnit
hasMany ProjectSubspace
project_subspaces

Fields:

id
project_id
project_block_id
project_floor_id
project_unit_id
project_room_id
name
type
is_active
remarks
created_at
updated_at

Relationships:

belongsTo Project
belongsTo ProjectBlock
belongsTo ProjectFloor
belongsTo ProjectUnit
belongsTo ProjectRoom
5C. ACTIVITY STRUCTURE TABLES
activity_divisions

Purpose:
WBS-level division grouping for activities.

Fields:

id
code
name
sequence
is_active
remarks
created_at
updated_at

Relationships:

hasMany ActivityMapping
hasMany Activity
activities

Purpose:
Visible engineer-facing activity master.

Actual Fields:

id
activity_division_id
activity_name
unit
work_stage
is_active
created_at
updated_at

Relationships:

belongsTo ActivityDivision
hasOne ActivityMapping

Important:
Engineers should see only activity_name, never RH cost codes.

activity_mappings

Purpose:
Hidden ERP intelligence mapping table.

Fields:

id
activity_id
activity_division_id
division_code
rh_cost_code
odoo_type_code
odoo_type
unit
project_type
structure_type
work_stage
activity_name
boq_item_id
material_group
contractor_type
productivity_norm
quality_checklist_id
odoo_analytic_account_code
odoo_analytic_tag_code
inventory_expense_bucket
procurement_mode
is_active
remarks
created_at
updated_at

Relationships:

belongsTo Activity
belongsTo ActivityDivision

Purpose Notes:

This table is not visible to site engineers.

This is the central ERP mapping layer connecting:

DPR
RH cost codes
BOQ
Inventory
Future Odoo integration
Analytics
Procurement logic

Critical Rule:
RH cost codes must remain backend-only.

5D. DPR TABLES
dprs

Purpose:
Main DPR header table.

Common Fields:

id
project_id
user_id / engineer_id
dpr_date
weather
status
remarks
created_at
updated_at

Relationships:

belongsTo Project
belongsTo User / Engineer
hasMany DprWorkItem
hasMany DPR labour records
hasMany material records
hasMany machinery records
hasMany issues
hasMany photos
hasMany tomorrow plans

Purpose Notes:
DPR should become a daily consolidation report.

dpr_work_items

Purpose:
Detailed location-wise execution entries inside DPR.

Important Fields:

id
dpr_id
activity_id
activity_mapping_id
project_block_id
project_floor_id
project_unit_id
project_room_id
project_subspace_id
contractor_id
quantity_completed
remarks
created_at
updated_at

Relationships:

belongsTo DPR
belongsTo Activity
belongsTo ActivityMapping
belongsTo ProjectBlock
belongsTo ProjectFloor
belongsTo ProjectUnit
belongsTo ProjectRoom
belongsTo ProjectSubspace
belongsTo Contractor
5E. LABOUR REPORTING TABLE
labour_reports

Purpose:
Tracks labour attendance and productivity independently during the day.

Fields:

id
dpr_id nullable
project_id
user_id
project_block_id nullable
project_floor_id nullable
project_unit_id nullable
project_room_id nullable
project_subspace_id nullable
activity_id nullable
activity_mapping_id nullable
contractor_id nullable
skilled_count
semi_skilled_count
helper_count
semi_helper_count
supervisor_count
technician_count
machine_operator_count
male_count
female_count
local_count
non_local_count
total_labour
shift
work_output
work_output_unit
entry_date
entry_time
status
remarks
created_at
updated_at
5F. MATERIAL MASTER TABLES
material_categories

Purpose:
Stores standard material categories.

Fields:

id
category_name
category_code
is_active
remarks
created_at
updated_at

Relationships:

hasMany Material
materials

Purpose:
Stores standard materials used for received, consumed and inventory tracking.

Fields:

id
material_category_id
material_code
material_name
specification
brand
unit
minimum_stock_level
is_active
remarks
created_at
updated_at

Relationships:

belongsTo MaterialCategory
hasMany MaterialReceived
hasMany MaterialConsumed
5G. MATERIAL RECEIVED TABLE
material_receiveds

Purpose:
Tracks inward material movement independently during the day.

Fields:

id
dpr_id nullable
project_id
user_id
project_block_id nullable
project_floor_id nullable
project_unit_id nullable
storage_location
material_category_id nullable
material_id nullable
material_category
material_name
specification
brand
quantity_received
unit
vendor_name
supplied_by_contractor
contractor_id nullable
vehicle_number
driver_name
challan_number
bill_number
received_date
received_time
material_condition
accepted_quantity
short_quantity
damaged_quantity
rejected_quantity
site_engineer_verification_status
pmo_verification_status
accountant_verification_status
status
remarks
created_at
updated_at

Relationships:

belongsTo Project
belongsTo User / Engineer
belongsTo ProjectBlock
belongsTo ProjectFloor
belongsTo ProjectUnit
belongsTo MaterialCategory
belongsTo Material
belongsTo Contractor
belongsTo DPR

Workflow:

Draft
→ Submitted
→ Approved

Implemented Pages:

/material-received
/material-received/create
/material-received/{id}
/material-received/{id}/edit
5H. MATERIAL CONSUMED TABLE
material_consumeds

Purpose:
Tracks exact material usage against exact activity and location.

Fields:

id
dpr_id nullable
project_id
user_id
project_block_id nullable
project_floor_id nullable
project_unit_id nullable
project_room_id nullable
project_subspace_id nullable
activity_division_id nullable
activity_id nullable
activity_mapping_id nullable
material_category_id nullable
material_id
contractor_id nullable
quantity_consumed
unit
related_work_output_quantity
wastage_quantity
wastage_reason
consumed_date
consumed_time
status
remarks
created_at
updated_at

Relationships:

belongsTo Project
belongsTo User / Engineer
belongsTo ProjectBlock
belongsTo ProjectFloor
belongsTo ProjectUnit
belongsTo ProjectRoom
belongsTo ProjectSubspace
belongsTo ActivityDivision
belongsTo Activity
belongsTo ActivityMapping
belongsTo MaterialCategory
belongsTo Material
belongsTo Contractor
belongsTo DPR

Status:

In Progress

Implemented:

migration
model
controller foundation
index page
create page
store entry
category/material filtering
activity division/activity filtering

Pending:

view
edit/update
submit
approve
6. TABLE RELATIONSHIP SUMMARY
Project Location Hierarchy
Project
 └── ProjectBlock
      └── ProjectFloor
           └── ProjectUnit
                └── ProjectRoom
                     └── ProjectSubspace
Activity Hierarchy
ActivityDivision
 └── Activity
      └── ActivityMapping
Material Hierarchy
MaterialCategory
 └── Material
      ├── MaterialReceived
      └── MaterialConsumed
DPR Execution Hierarchy
DPR
 └── DprWorkItem
      ├── Activity
      ├── ActivityMapping
      ├── Contractor
      ├── ProjectBlock
      ├── ProjectFloor
      ├── ProjectUnit
      ├── ProjectRoom
      └── ProjectSubspace
Operational Module Hierarchy
Project
 ├── LabourReport
 ├── MaterialReceived
 └── MaterialConsumed
7. MANDATORY EXECUTION HIERARCHY

Every execution entry should support:

Project
→ Block
→ Floor
→ Unit
→ Room
→ Sub-space
→ Activity Division
→ Activity
→ Quantity
→ Contractor

This hierarchy is mandatory for:

DPR
Labour
Material Consumed
Material Required
Photos
Issues
Planning
Quality
Safety

 8. CURRENT SIDEBAR STRUCTURE
Dashboard
Dashboard
Masters
Projects
Location Masters
Project Locations
Activity Mappings
Materials
Contractors
Vendors
Machinery / Tools
Daily Execution
DPR Entries
Labour Reporting
Material Received
Material Consumed
Material Required
Planning & Controls
Weekly Plans
Weekly Progress
Monthly Plans
PMO & Verification
DPR Reviews
Material Verification
Mapping Pending Queue
Reports
DPR Reports
Labour Reports
Material Reports
Administration
Users
Audit Trail

9. CURRENT DEVELOPMENT STAGE

Current stage:

PHASE 1 DAILY EXECUTION MODULE DEVELOPMENT

Completed:

authentication and roles
project master
location hierarchy
standard location masters
project location management
activity divisions
activity mappings
Excel import engine
grouped ERP sidebar navigation
DPR work item location fields
DPR show/PDF location-aware display
Labour Reporting module
Material Categories Master
Materials Master
Material Received module

In Progress:

Material Consumption module

Next immediate module:

Complete Material Consumption workflow:
View
Edit / Update
Submit
Approve

Next milestone after this:

Inventory / Stock Register Module
10. IMPLEMENTED FILTERING LOGIC

Implemented reusable filtering:

Project
→ Block
→ Floor
→ Unit
→ Room
→ Sub-space
Activity Division
→ Activity
Material Category
→ Material

These filtering patterns should be reused across:

Labour Reporting
Material Received
Material Consumed
Material Required
DPR
Planning
Productivity
Billing

11. LABOUR REPORTING MODULE — IMPLEMENTED

Implemented Features
Labour report creation
Labour report listing
Labour report edit while in Draft
Labour report view/details page

Draft → Submitted → Approved workflow
Role-based approval foundation
Location hierarchy selection
Activity Division → Activity filtering
Contractor linkage
Engineer/user linkage
Total labour auto-calculation
Dashboard summary cards
Filters by Date, Project, Contractor, Activity and Status
Labour Total Logic

Total labour is calculated only from actual labour categories:

skilled_count
+ semi_skilled_count
+ helper_count
+ semi_helper_count
+ supervisor_count
+ technician_count
+ machine_operator_count
= total_labour

The following fields are classification fields only and must not be added into total labour:

male_count
female_count
local_count
non_local_count
Labour Workflow
Draft
→ Submitted
→ Approved

Rules:

Draft reports can be edited.
Submitted reports cannot be edited by engineer.
Admin / PMO / DGM can approve submitted reports.
Approved reports are locked for normal editing.
Future correction workflow will handle post-approval changes.
Labour Routes
GET     /labour-reports
GET     /labour-reports/create
POST    /labour-reports
GET     /labour-reports/{labourReport}
GET     /labour-reports/{labourReport}/edit
PUT     /labour-reports/{labourReport}
PATCH   /labour-reports/{labourReport}/submit
PATCH   /labour-reports/{labourReport}/approve

12. MATERIAL RECEIVED MODULE — IMPLEMENTED
Implemented Features
Material received creation
Material received listing
Material received view/details page
Material received edit/update while Draft
Draft → Submitted → Approved workflow
Project → Block → Floor → Unit filtering
Material Category → Material filtering
Vendor/transport/challan details
Verification quantities
Dashboard summary cards
Filters by Date, Project and Status
Material Received Workflow
Draft
→ Submitted
→ Approved

Material Received Routes
GET     /material-received
GET     /material-received/create
POST    /material-received
GET     /material-received/{materialReceived}
GET     /material-received/{materialReceived}/edit
PUT     /material-received/{materialReceived}
PATCH   /material-received/{materialReceived}/submit
PATCH   /material-received/{materialReceived}/approve

13. MATERIAL CONSUMPTION MODULE — completed
Implemented Features
Material consumption migration
Material consumption model
Material consumption controller foundation
Material consumption index page
Material consumption create page
Material consumption store method

Project hierarchy filtering
Activity Division → Activity filtering
Material Category → Material filtering

Contractor linkage
Wastage quantity and reason capture
Work output quantity capture

completed Features
Material consumed view/details page
Material consumed edit/update
Draft → Submitted workflow
Submitted → Approved workflow
Approval lock rules

Current Routes
GET     /material-consumed
GET     /material-consumed/create
POST    /material-consumed

Pending routes to be completed:

GET     /material-consumed/{materialConsumed}
GET     /material-consumed/{materialConsumed}/edit
PUT     /material-consumed/{materialConsumed}
PATCH   /material-consumed/{materialConsumed}/submit
PATCH   /material-consumed/{materialConsumed}/approve

14. CURRENT INVENTORY DESIGN

Inventory Balance Formula:

Current Stock
=
Total Material Received
-
Total Material Consumed

Future full reconciliation formula:

Opening Stock
+ Material Received
- Material Consumed
- Material Wastage
- Material Transfer
- Material Return
= Closing Stock

Future inventory dashboard:

Material
Received Qty
Consumed Qty
Balance Qty

Filters:

Project
Material Category
Material
Date Range

Reports:

Stock Register
Consumption Register
Material Ledger
Wastage Report
15. FUTURE MATERIAL REQUIRED STRUCTURE

Purpose:
Track material requirements before or during DPR submission.

Future Fields:

id
dpr_id nullable
project_id
user_id / requested_by
project_block_id
project_floor_id
project_unit_id
project_room_id
project_subspace_id
activity_id
activity_mapping_id
material_id
specification
required_quantity
unit
required_date
required_for
priority
stock_available_status
reason
pmo_approval_status
procurement_status
remarks
created_at
updated_at

Priority Options:

Normal
Urgent
Critical

Critical requirements should appear in PMO and CEO dashboards.

16. FUTURE PMO WORKFLOW
Draft
→ Submitted
→ PMO Review
→ Clarification Required
→ Resubmitted
→ Approved
→ Accountant Verified
→ Locked

Rules:

Engineers can edit only in Draft
Engineers cannot edit after submission
PMO verifies site execution facts
Accountant verifies material/cost transactions
Locked entries require correction request
No hard delete after approval
17. FUTURE MATERIAL RECONCILIATION LOGIC

Alert Conditions:

consumed without received stock
received without challan
bill without received material
high wastage
material shortage
material received but not used
stock mismatch
18. FUTURE CEO CONTROL FLOW
Execution Data
→ PMO Verification
→ Accountant Verification
→ Exception Engine
→ CEO Dashboard
→ Decision Queue

CEO should see:

delays
shortages
leakages
approvals
escalations
decisions

CEO should not see raw execution clutter.

19. FUTURE ODOO READINESS

All major tables should remain future-ready for:

Odoo Projects
Odoo Inventory
Odoo Purchase
Odoo Accounting
Odoo Analytic Accounts
Odoo Vendors

No live Odoo integration yet.

Only backend compatibility readiness.

Future fields may include:

odoo_project_id
odoo_analytic_account_id
odoo_analytic_tag_id
odoo_product_id
odoo_vendor_id
odoo_stock_location_id
odoo_purchase_order_id
odoo_bill_id
odoo_external_reference
20. FUTURE ERP ROADMAP

Phase 1 — Practical Daily Execution
Project Master
Location Hierarchy
Activity Mapping
DPR
Labour Reporting
Material Received
Material Consumed
Material Required
Machinery Usage
Issues
Safety Basic
Photos
Tomorrow Plan
PMO Review
Basic CEO Dashboard

Phase 2 — PMO Control
Clarification Workflow
Plan vs Actual
Mapping Pending Queue
Material Reconciliation Alerts
Contractor Productivity Alerts
Drawing / Approval Dependency

Phase 3 — Accountant Control
GRN / Challan Matching
Bill Pending Status
Stock Balance
Material Transfer
Contractor Attendance vs Output
Machinery Rental Tracking
Wastage Reports

Phase 4 — Advanced Corporate Control
BOQ Mapping
Billing Milestone Tracking
Odoo Integration
Contractor Performance Score
Engineer Performance Score
Productivity Norms
Quality Checklist Module
Variation Approval Workflow
Client Dashboard if required

21. CURRENT PROJECT MILESTONES
Milestone Achieved

Core Labour Execution System Completed.

Includes:

Labour Reporting
Location tracking
Activity division filtering
Contractor linkage
Draft/Submit/Approve workflow
Milestone Achieved

Core Material Management Foundation Completed.

Includes:

Material Categories
Materials Master
Material Received
Material Consumption foundation
Next Milestone

Complete Material Consumption Workflow.

Then build:

Inventory & Stock Register

22. FINAL SUCCESS TARGET
Maximum site information
Minimum typing
No cost-code exposure
No leakage
No miscommunication
Complete visibility from Site Engineer to CEO

## Inventory Management Module

### Stock Register

Status: Completed

Features:
- Material-wise stock tracking
- Approved receipts aggregation
- Approved consumption aggregation
- Real-time balance calculation
- Project filter
- Project block filter

Formula:

Balance Stock =
Total Material Received
-
Total Material Consumed

## Material Requirement Module

Status: Completed

Features:
- Create Requirement
- Draft Workflow
- Submit Workflow
- Approval Workflow
- Project-wise Requirement Tracking
- Priority Management
- Requirement Date Tracking

## Material Requirement Module

Status: Completed

Features:
- Requirement Planning
- Priority Management
- Draft Workflow
- Submission Workflow
- Approval Workflow
- Requirement Tracking

---

## Material Shortage Report

Status: Completed

Formula:

Open Requirement
=
Required Quantity
-
Fulfilled Quantity

Shortage
=
Open Requirement
-
Available Stock

Features:
- Project Filter
- Block Filter
- Procurement Planning
- Shortage Identification

# Ravion DPR System – Development Update

## Date

June 2026

---

# Completed Modules

## Tomorrow Plan Module ✅

### Features Implemented

* Tomorrow Plan Create
* Tomorrow Plan Edit
* Tomorrow Plan View
* Tomorrow Plan Listing
* Draft Workflow
* Submit Workflow
* Approval Workflow
* Project Hierarchy Integration

  * Block
  * Floor
  * Unit
  * Room
  * Subspace
* Activity Division → Activity dependent dropdown
* Labour Planning
* Material Requirement Planning
* Machinery Requirement Planning
* Risk & Constraints Tracking
* Priority Management
* Responsible Person Assignment

### Workflow

Draft → Submitted → Approved

---

## Site Issues / Delays Module ✅

### Features Implemented

* Site Issue Create
* Site Issue Edit
* Site Issue View
* Site Issue Listing
* Issue Filtering
* Project Hierarchy Integration
* Activity Division → Activity dependency
* Issue Types

  * Material Shortage
  * Drawing Pending
  * Client Approval Pending
  * Labour Shortage
  * Contractor Delay
  * Machinery Breakdown
  * Safety Issue
  * Quality Issue
  * Other
* Priority Tracking

  * Low
  * Medium
  * High
  * Critical
* Status Tracking

  * Open
  * In Progress
  * Resolved
* Root Cause Analysis
* Resolution Tracking
* Responsible Person Assignment
* Target Closure Date
* Actual Closure Date
* PMO Escalation
* Management Escalation

---

## Plan vs Actual Module ✅

### Features Implemented

* Plan vs Actual Report
* Date Range Filtering
* Project Filtering
* Planned Quantity Calculation
* Actual Quantity Calculation
* Variance Calculation
* Achievement Percentage Calculation
* Status Identification

### Status Logic

* Not Started
* Behind
* On Track
* Ahead

### Data Sources

* Tomorrow Plans (Approved Plans)
* DPR Work Items (Actual Progress)

### KPIs

* Total Planned Quantity
* Total Actual Quantity
* Total Variance
* Overall Achievement Percentage

---

# Database Enhancements

## Site Issues Table

Added:

* project_id
* project_block_id
* project_floor_id
* project_unit_id
* project_room_id
* project_subspace_id
* activity_id
* issue_date
* title
* root_cause
* target_closure_date
* actual_closure_date
* escalated_to_pmo
* escalated_to_management
* resolution
* created_by

## Site Issues Improvements

* dpr_id made nullable to support standalone issue tracking.

---

# Analytics Capability Added

The system can now compare:

Tomorrow Plan
VS
Actual DPR Execution

to measure:

* Planning Accuracy
* Execution Performance
* Productivity
* Delay Identification
* Achievement %

---

# Next Planned Module

## Dashboard Module

### Proposed Features

* Today's DPR Count
* Labour Summary
* Material Consumption Summary
* Open Issues
* Critical Issues
* Tomorrow Plans Pending
* Plan Achievement %
* Project Health Dashboard
* PMO Dashboard
* Management Dashboard

Status: Planned

## 2026-06-12 – Weekly Planning Module Completed

### Weekly Plans
- Weekly Plan create implemented
- Weekly Plan edit implemented
- Weekly Plan view implemented
- Weekly Plan listing implemented
- Engineer assignment added
- Activity selection integrated
- Resource planning fields added
- Risk and constraints tracking added
- Status workflow added

### Weekly Progress Dashboard
- Weekly target vs actual DPR quantity comparison
- Achievement percentage calculation
- Status indicators:
  - Ahead / Completed
  - On Track
  - Behind
  - Not Started
- Engineer-wise weekly performance visibility

### Routing Fix
- Fixed route conflict between:
  - weekly-plans/{weekly_plan}
  - weekly-plans/progress-dashboard
- Custom route moved above resource route



## Monthly Planning Module Completed

### Monthly Plans
- Monthly Plan Creation
- Monthly Plan Listing
- Monthly Plan View
- Monthly Plan Edit
- Monthly Plan Filtering
- Engineer Assignment
- Activity Division Mapping
- Resource Planning
- Risk & Constraint Tracking

### Monthly Progress Dashboard
- Planned Quantity Tracking
- Actual DPR Quantity Tracking
- Achievement Percentage Calculation
- Delayed Activity Identification
- Completed Activity Identification
- Management KPI Dashboard

Status: Completed
Date: 12-Jun-2026

## DPR Approval Workflow Completed

### PMO Review Queue
- Pending DPR Listing
- DPR Review Screen
- Approve DPR
- Reject DPR
- PMO Remarks
- Status Tracking
- Success Notifications
- Role-Based Access Control

### Workflow
Engineer → Submit DPR
PMO → Review DPR
PMO → Approve / Reject
Management → Dashboard Monitoring

### Approval Features
- Pending Status Management
- Approval Remarks
- Rejection Remarks
- DPR Status History

Status: Completed
Date: 12-Jun-2026

# Work Completed - PMO Workflow & Planning Modules

## Weekly Planning Module

* Created Weekly Plan master module
* Added Create, Edit, View, Delete functionality
* Added Weekly Progress Dashboard
* Added Planned vs Actual DPR quantity tracking
* Added Achievement Percentage calculation
* Added Status indicators (On Track / Attention / Delayed)

## Monthly Planning Module

* Created monthly_plans table
* Created MonthlyPlan model
* Created MonthlyPlanController
* Added Create, Edit, View, Delete functionality
* Added Monthly Progress Dashboard
* Added Planned vs Actual monthly progress tracking

## DPR Approval Workflow

* Created PMO DPR Review Queue
* Added Pending DPR listing
* Added DPR approval functionality
* Added DPR rejection functionality
* Added PMO remarks capture
* Updated DPR status workflow
* Added approval success notifications

## Material Verification Workflow

* Created Material Verification module
* Listed received materials pending verification
* Added PMO verification process
* Updated material verification status
* Added verification remarks support
* Added verification success notifications

## Activity Mapping Workflow

* Created Mapping Pending Queue
* Listed DPR work items pending activity mapping
* Added Activity Mapping screen
* Added Activity Division-wise mapping support
* Added Activity Mapping save functionality
* Added location hierarchy display

  * Block
  * Floor
  * Unit
  * Room
  * Subspace
* Added serial numbering with pagination support
* Added automatic removal of mapped items from pending queue
* Added mapping success notifications

## UI Improvements

* Added success messages across modules
* Added pagination support
* Added numbering for queue records
* Improved location visibility for mapping process
* Improved PMO workflow screens

## Database Changes

### New Tables

* monthly_plans

### Updated Tables

* dprs

  * PMO approval workflow fields utilized

* material_receiveds

  * PMO verification workflow fields utilized

* dpr_work_items

  * activity_mapping_id used for mapping workflow

## Git Milestone

Completed PMO Workflow Modules:

* DPR Approval
* Material Verification
* Activity Mapping Queue

System is now capable of:

1. Planning (Tomorrow / Weekly / Monthly)
2. Daily DPR Execution
3. Material Tracking
4. Labour Tracking
5. PMO Approval Workflow
6. Material Verification Workflow
7. Activity Mapping Workflow

Ready for next phase:

* Project Progress Dashboard
* Overall Project Completion Tracking
* Activity-wise Completion Analytics
* Management Dashboard

## Project Progress Dashboard

### Features Implemented

- Executive Dashboard
- Project Count KPI
- Weekly Plan KPI
- Monthly Plan KPI
- DPR Count KPI
- Activity Mapping KPI
- Material Received KPI
- Material Consumed KPI

### Executive KPIs

- Total Monthly Planned Quantity
- Total Weekly Planned Quantity
- Total Completed Quantity
- Overall Progress %

### Approval KPIs

- Approved DPR Count
- Rejected DPR Count
- Verified Material Count
- Pending Activity Mapping Count

### Progress Monitoring

- Project-wise Progress Summary
- Monthly Planned Quantity
- Weekly Planned Quantity
- Completed Quantity
- Progress Percentage
- Visual Progress Bars

### Route

/project-progress-dashboard

### Controller

ProjectProgressDashboardController

### View

resources/views/project-progress-dashboard/index.blade.php

# Ravion DPR System – Progress Update

## Date: 15 June 2026

### Newly Completed Modules

#### 1. Executive Project Progress Dashboard

Created a centralized management dashboard displaying:

* Total Projects
* Weekly Plans Count
* Monthly Plans Count
* DPR Entries Count
* Activity Mappings Count
* Material Received Count
* Material Consumed Count

Additional Executive KPIs:

* Total Monthly Planned Quantity
* Total Weekly Planned Quantity
* Total Completed Quantity
* Overall Project Progress %
* Approved DPR Count
* Rejected DPR Count
* Verified Material Count
* Pending Activity Mapping Count

Dashboard Charts:

* Project Progress % Chart
* Material Received vs Material Consumed Chart

Project Progress Summary Table:

* Project Name
* Monthly Planned Quantity
* Weekly Planned Quantity
* Completed Quantity
* Progress %

---

#### 2. Project Drill-Down Dashboard

Created dedicated project dashboard accessible from the Executive Dashboard.

Route:

```text
/project-dashboard/{project}
```

Features:

* Monthly Planned Quantity
* Weekly Planned Quantity
* Completed Quantity
* Progress Percentage
* Balance Quantity
* Remaining Percentage

Labour Analytics:

* Total Labour Reports
* Total Labour Strength

Material Analytics:

* Material Received Count
* Material Consumed Count

Project Monitoring:

* Progress Bar Visualization
* Recent DPR Entries
* Recent Labour Reports

---

#### 3. Activity Progress Dashboard

Created activity-wise project monitoring screen.

Route:

```text
/projects/{project}/activity-progress
```

Features:

* Activity Wise Planned Quantity
* Activity Wise Completed Quantity
* Balance Quantity
* Progress Percentage

Project Control Capability:

* Detect Planned Activities with No Execution
* Detect Executed Activities Not Present in Monthly Plan
* Identify Planning vs Execution Mismatches

Activity Classification:

* Planned Not Started
* In Progress
* Unplanned DPR Activities

Example Findings:

* Imported Filling Soil planned but not executed.
* Water Proofing executed without corresponding Monthly Plan.
* Authority Inspection Fees executed without Monthly Plan.

This module provides PMO-level visibility into project execution alignment.

---

## System Status After Completion

### Core Operations

✓ DPR Entry Management
✓ DPR Approval Workflow
✓ Labour Reporting
✓ Material Receipt Tracking
✓ Material Verification
✓ Material Consumption Tracking
✓ Activity Mapping Management

### Planning & Controls

✓ Weekly Planning Module
✓ Monthly Planning Module
✓ Executive Progress Dashboard
✓ Project Dashboard
✓ Activity Progress Dashboard

### PMO Controls

✓ DPR Review Queue
✓ Mapping Pending Queue
✓ Material Verification Queue
✓ Planning vs Execution Monitoring

---

## Key Achievement

The Ravion DPR System has now evolved from a DPR recording application into a Project Planning, Monitoring, and PMO Control Platform capable of:

* Planning Management
* Daily Progress Monitoring
* Material Control
* Labour Tracking
* Activity Mapping Governance
* Executive Reporting
* Project Performance Analytics
* Planning vs Execution Analysis

---

## Recommended Next Module

### Project Health Dashboard

Proposed Features:

* Project Health Score
* Progress Status
* Labour Status
* Material Status
* DPR Approval Status
* Traffic Light Indicators (Green / Amber / Red)
* Management-Level Portfolio Monitoring

Priority: High
Category: PMO & Executive Reporting

### PMO Exception Dashboard

Route:
    /pmo-exception-dashboard

Features:
- Rejected DPR Monitoring
- Pending Activity Mapping Monitoring
- Unplanned Activity Detection
- Planned But Not Started Detection
- PMO Action Dashboard

Purpose:
Provides management visibility into project exceptions requiring intervention.

Benefits:
- Early issue identification
- Planning vs Execution monitoring
- PMO governance controls
- Exception-based project management

Date: 15-Jun-2026

ROLE MANAGEMENT MODULE COMPLETED

Features:
- Created RoleController
- Added Roles Resource Routes
- Created Roles Index Page
- Created Add Role Page
- Created Edit Role Page
- Added User Count Per Role
- Prevent Delete When Users Assigned
- Added Roles Menu To Sidebar

Status:
Role Management Module Fully Functional

Next Phase:
Permission Management System
Role Permission Assignment
Permission Based Sidebar Access

RBAC SYSTEM COMPLETED

Modules Created:
- Roles
- Permissions
- Role Permissions

Features:
- Permission Seeder
- Permission Assignment
- Select All Permissions
- Permission Count Tracking
- User Permission Helper
- Permission Based Sidebar

Security Status:
UI Access Control Completed

Next Phase:
Permission Middleware
Route Protection
403 Unauthorized Handling
Audit Trail