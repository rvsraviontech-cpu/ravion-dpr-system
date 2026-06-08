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
```

---

# 3. NON-NEGOTIABLE SYSTEM RULES

## Hidden Cost-Code Rule

Site engineers must never see:

- RH cost codes
- BOQ rates
- purchase rates
- contractor rates
- profitability
- Odoo accounting tags
- analytic account codes

Backend must silently store:

- RH cost code
- Odoo type code
- UOM
- BOQ mapping
- analytic account
- inventory bucket
- future Odoo IDs

---

## Dropdown Rule

Site engineers should use:

- dropdowns
- searchable dropdowns
- controlled masters
- mapping layers

Free typing should be avoided wherever possible.

---

## No Hard Delete Rule

The system should use:

- active/inactive status
- audit logging
- correction workflow

Hard deletes should be avoided.

---

## DPR Philosophy

DPR is not the only data-entry screen.

DPR should become:

```text
Daily Consolidation Report
```

Independent operational modules should work during the day:

- Labour Reporting
- Material Received
- Material Consumed
- Material Required
- Machinery Usage
- Site Issues
- Photos
- Planning

DPR will later consolidate these entries.

---

# 4. CURRENT COMPLETED MODULES

## Authentication & Roles

Implemented:

- login
- role-based access
- Admin role
- PMO role
- DGM role
- Engineer role
- Accountant role
- CEO role

Role middleware uses:

```php
auth()->user()->role->name
```

---

## Project Master

Implemented:

- project creation
- project listing
- project edit
- project status
- project assignment

---

## Location Hierarchy Engine

Implemented hierarchy:

```text
Project
→ Block
→ Floor
→ Unit
→ Room
→ Sub-space
```

Implemented project-specific tables:

- project_blocks
- project_floors
- project_units
- project_rooms
- project_subspaces

Features completed:

- create
- edit
- activate/deactivate
- project-wise management
- master dropdown integration

---

## Standard Location Masters

Implemented:

- location_block_masters
- location_floor_masters
- location_unit_masters
- location_room_masters
- location_subspace_masters

Purpose:

- standardize dropdown values
- prevent spelling duplication
- maintain reporting consistency
- avoid entries like `Flat 101`, `Flats 101`, `Flat-101`

---

## Activity Mapping Engine

Implemented:

- activity_divisions
- activities
- activity_mappings
- Excel import engine
- 545 activity mappings imported
- 532 unique visible activities created
- activity mappings linked to divisions

Purpose:

- hidden RH cost-code mapping
- Odoo-ready backend mapping
- activity dropdown intelligence
- future BOQ readiness

---

## Project Locations UI

Implemented:

- collapsible add sections
- grouped hierarchy forms
- grouped hierarchy tables
- edit functionality
- activate/deactivate functionality
- master dropdown usage

---

## Sidebar ERP Navigation

Implemented grouped navigation:

```text
Dashboard
Masters
Daily Execution
Planning & Controls
PMO & Verification
Reports
Administration
```

---

# 5. CURRENT IMPLEMENTED DATABASE STRUCTURE

## users

Purpose:
System users.

Important Relationships:
- belongsTo Role

Used for:

- Admin
- PMO
- DGM
- Engineer
- Accountant
- CEO

---

## roles

Purpose:
Stores user roles.

Expected roles:

- Admin
- PMO
- DGM
- Engineer
- Accountant
- CEO

---

## projects

Purpose:
Project master.

Common Fields:

- id
- project_name
- project_code
- location
- status
- created_at
- updated_at

Relationships:

- hasMany ProjectBlock
- hasMany ProjectFloor
- hasMany ProjectUnit
- hasMany ProjectRoom
- hasMany ProjectSubspace
- hasMany DPR

---

# 5A. LOCATION MASTER TABLES

## location_block_masters

Purpose:
Standard reusable block/building/tower names.

Fields:

- id
- name
- type
- is_active
- remarks
- created_at
- updated_at

Examples:

- Main Building
- Block A
- Tower 1
- Villa 1
- External Area
- Not Applicable

---

## location_floor_masters

Purpose:
Standard reusable floor names.

Fields:

- id
- name
- sequence
- is_active
- remarks
- created_at
- updated_at

Examples:

- Basement 1
- Ground Floor
- First Floor
- Second Floor
- Terrace Floor
- External Area
- Not Applicable

---

## location_unit_masters

Purpose:
Standard reusable unit/flat/villa/office names.

Fields:

- id
- name
- type
- is_active
- remarks
- created_at
- updated_at

Examples:

- Flat 101
- Flat 102
- Villa 1
- Office 301
- Common Area
- Lift Lobby
- Corridor

---

## location_room_masters

Purpose:
Standard reusable room/space names.

Fields:

- id
- name
- room_type
- is_active
- remarks
- created_at
- updated_at

Examples:

- Master Bedroom
- Bedroom 1
- Toilet 1
- Kitchen
- Balcony
- Utility
- Lift Lobby

---

## location_subspace_masters

Purpose:
Standard reusable sub-space/element names.

Fields:

- id
- name
- type
- is_active
- remarks
- created_at
- updated_at

Examples:

- North Wall
- South Wall
- Shower Wall
- Vanity Wall
- Floor
- Ceiling
- Wet Area
- Dry Area

---

# 5B. PROJECT LOCATION TABLES

## project_blocks

Purpose:
Project-specific blocks/buildings/towers.

Fields:

- id
- project_id
- name
- code
- type
- is_active
- remarks
- created_at
- updated_at

Relationships:

- belongsTo Project
- hasMany ProjectFloor

---

## project_floors

Purpose:
Project-specific floors under blocks.

Fields:

- id
- project_id
- project_block_id
- name
- sequence
- is_active
- remarks
- created_at
- updated_at

Relationships:

- belongsTo Project
- belongsTo ProjectBlock
- hasMany ProjectUnit

---

## project_units

Purpose:
Project-specific units/flats/villas under floors.

Fields:

- id
- project_id
- project_block_id
- project_floor_id
- name
- type
- is_active
- remarks
- created_at
- updated_at

Relationships:

- belongsTo Project
- belongsTo ProjectBlock
- belongsTo ProjectFloor
- hasMany ProjectRoom

---

## project_rooms

Purpose:
Project-specific rooms/spaces under units.

Fields:

- id
- project_id
- project_block_id
- project_floor_id
- project_unit_id
- name
- room_type
- is_active
- remarks
- created_at
- updated_at

Relationships:

- belongsTo Project
- belongsTo ProjectBlock
- belongsTo ProjectFloor
- belongsTo ProjectUnit
- hasMany ProjectSubspace

---

## project_subspaces

Purpose:
Project-specific sub-spaces/elements under rooms.

Fields:

- id
- project_id
- project_block_id
- project_floor_id
- project_unit_id
- project_room_id
- name
- type
- is_active
- remarks
- created_at
- updated_at

Relationships:

- belongsTo Project
- belongsTo ProjectBlock
- belongsTo ProjectFloor
- belongsTo ProjectUnit
- belongsTo ProjectRoom

---

# 5C. ACTIVITY STRUCTURE TABLES

## activity_divisions

Purpose:
WBS-level division grouping for activities.

Actual Fields:

- id
- code
- name
- sequence
- is_active
- remarks
- created_at
- updated_at

Seeded Divisions:

- 00-00-000 PRE-CONSTRUCTION & APPROVALS
- 01-00-000 SITE ESTABLISHMENT
- 02-00-000 SITE PREPARATION
- 03-00-000 EARTHWORK
- 04-00-000 FOUNDATION WORKS
- 05-00-000 RCC STRUCTURE
- 06-00-000 MASONRY
- 07-00-000 PLASTERING
- 08-00-000 WATERPROOFING
- 09-00-000 FLOORING
- 10-00-000 DOORS & WINDOWS
- 11-00-000 ELECTRICAL SYSTEM
- 12-00-000 PLUMBING SYSTEM
- 13-00-000 HVAC SYSTEM
- 14-00-000 SUBCONTRACT LABOUR
- 15-00-000 MACHINERY & EQUIPMENT
- 16-00-000 FUEL & ENERGY
- 17-00-000 HAND TOOLS
- 18-00-000 ENGINEERING EQUIPMENT
- 19-00-000 SITE CONSUMABLES
- 20-00-000 SCAFFOLDING & TEMPORARY WORKS
- 21-00-000 SAFETY EQUIPMENT
- 22-00-000 SITE OVERHEADS
- 23-00-000 TESTING & QUALITY
- 24-00-000 HANDOVER & CLOSEOUT

Relationships:

- hasMany ActivityMapping

---

## activities

Purpose:
Visible engineer-facing activity master.

Actual Fields:

- id
- activity_name
- unit
- work_stage
- is_active
- created_at
- updated_at

Relationships:

- hasOne ActivityMapping

Important:
Engineers should see only `activity_name`, never RH cost codes.

---

## activity_mappings

Purpose:
Hidden ERP intelligence mapping table.

Actual Fields:

- id
- activity_id
- activity_division_id
- division_code
- rh_cost_code
- odoo_type_code
- odoo_type
- unit
- project_type
- structure_type
- work_stage
- activity_name
- boq_item_id
- material_group
- contractor_type
- productivity_norm
- quality_checklist_id
- odoo_analytic_account_code
- odoo_analytic_tag_code
- inventory_expense_bucket
- procurement_mode
- is_active
- remarks
- created_at
- updated_at

Relationships:

- belongsTo Activity
- belongsTo ActivityDivision

Purpose Notes:
This table is not visible to site engineers.

This is the central ERP mapping layer connecting:

- DPR
- RH cost codes
- BOQ
- Inventory
- Future Odoo integration
- Analytics
- Procurement logic

Critical Rule:
RH cost codes must remain backend-only.

---

# 5D. DPR TABLES

## dprs

Purpose:
Main DPR header table.

Common Fields:

- id
- project_id
- user_id / engineer_id
- dpr_date
- weather
- status
- remarks
- created_at
- updated_at

Relationships:

- belongsTo Project
- belongsTo User / Engineer
- hasMany DprWorkItem
- hasMany DPR labour records
- hasMany material records
- hasMany machinery records
- hasMany issues
- hasMany photos
- hasMany tomorrow plans

Purpose Notes:
DPR should become a daily consolidation report.

---

## dpr_work_items

Purpose:
Detailed location-wise execution entries inside DPR.

Actual / Current Important Fields:

- id
- dpr_id
- activity_id
- activity_mapping_id
- project_block_id
- project_floor_id
- project_unit_id
- project_room_id
- project_subspace_id
- contractor_id
- quantity_completed
- remarks
- created_at
- updated_at

Relationships:

- belongsTo DPR
- belongsTo Activity
- belongsTo ActivityMapping
- belongsTo ProjectBlock
- belongsTo ProjectFloor
- belongsTo ProjectUnit
- belongsTo ProjectRoom
- belongsTo ProjectSubspace
- belongsTo Contractor

Hierarchy:

```text
DPR
→ Work Item
→ Block
→ Floor
→ Unit
→ Room
→ Sub-space
→ Activity Mapping
→ Contractor
→ Quantity Completed
```

Purpose Notes:
This is the core execution-tracking table.

Every work item must become:

- location-specific
- activity-specific
- contractor-linked
- future cost-code ready

---

# 6. TABLE RELATIONSHIP SUMMARY

## Project Location Hierarchy

```text
Project
 └── ProjectBlock
      └── ProjectFloor
           └── ProjectUnit
                └── ProjectRoom
                     └── ProjectSubspace
```

## Activity Mapping Hierarchy

```text
ActivityDivision
 └── ActivityMapping
      └── Activity
```

## DPR Execution Hierarchy

```text
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
```

---

# 7. MANDATORY EXECUTION HIERARCHY

Every execution entry should support:

```text
Project
→ Block
→ Floor
→ Unit
→ Room
→ Sub-space
→ Work Stage
→ Activity
→ Quantity
→ Contractor
```

This hierarchy is mandatory for:

- DPR
- Labour
- Material Consumed
- Material Required
- Photos
- Issues
- Planning
- Quality
- Safety

---

# 8. CURRENT SIDEBAR STRUCTURE

## Dashboard

- Dashboard

## Masters

- Projects
- Location Masters
- Project Locations
- Activity Mappings
- Materials
- Contractors
- Vendors
- Machinery / Tools

## Daily Execution

- DPR Entries
- Labour Reporting
- Material Received
- Material Consumed
- Material Required

## Planning & Controls

- Weekly Plans
- Weekly Progress
- Monthly Plans

## PMO & Verification

- DPR Reviews
- Material Verification
- Mapping Pending Queue

## Reports

- DPR Reports
- Labour Reports
- Material Reports

## Administration

- Users
- Audit Trail

---

# 9. CURRENT DEVELOPMENT STAGE

Current stage:

```text
PHASE 1 FOUNDATION DEVELOPMENT
```

Completed:

- authentication and roles
- project master
- location hierarchy
- standard location masters
- project location management
- activity divisions
- activity mappings
- Excel import engine
- grouped ERP sidebar navigation
- DPR work item location fields
- DPR show/PDF location-aware display

Next immediate module:

```text
Labour Reporting Module
```

---

# 10. UPCOMING MODULES

Immediate next modules:

1. Labour Reporting
2. Material Received
3. Material Consumed
4. Material Required
5. Machinery Usage
6. DPR Consolidation Logic
7. PMO Review Workflow
8. Draft → Submit → Lock Workflow

---

# LABOUR REPORTING MODULE — IMPLEMENTED

## Module Status

Implemented as independent operational module.

Labour Reporting is not only a DPR sub-section. It works as a standalone daily execution entry module and will later auto-link into DPR.

---

## Implemented Features

- Labour report creation
- Labour report listing
- Labour report edit while in Draft
- Labour report view/details page
- Draft → Submitted → Approved workflow
- Role-based approval foundation
- Location hierarchy selection
- Activity Division → Activity filtering
- Contractor linkage
- Engineer/user linkage
- Total labour auto-calculation
- Dashboard summary cards
- Filters by:
  - Date
  - Project
  - Contractor
  - Activity
  - Status

---

## labour_reports Table

Purpose:
Tracks labour attendance and productivity independently during the day.

Fields:

- id
- dpr_id nullable
- project_id
- user_id
- project_block_id nullable
- project_floor_id nullable
- project_unit_id nullable
- project_room_id nullable
- project_subspace_id nullable
- activity_id nullable
- activity_mapping_id nullable
- contractor_id nullable
- skilled_count
- semi_skilled_count
- helper_count
- semi_helper_count
- supervisor_count
- technician_count
- machine_operator_count
- male_count
- female_count
- local_count
- non_local_count
- total_labour
- shift
- work_output
- work_output_unit
- entry_date
- entry_time
- status
- remarks
- created_at
- updated_at

---

## Labour Total Logic

Total labour is calculated only from actual labour categories:

```text
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

## LABOUR WORKFLOW
Draft
→ Submitted
→ Approved

Rules:

Draft reports can be edited.
Submitted reports cannot be edited by engineer.
Admin / PMO / DGM can approve submitted reports.
Approved reports are locked for normal editing.
Future correction workflow will handle post-approval changes.

Labour Module Relationships
LabourReport
 ├── Project
 ├── User / Engineer
 ├── ProjectBlock
 ├── ProjectFloor
 ├── ProjectUnit
 ├── ProjectRoom
 ├── ProjectSubspace
 ├── Activity
 ├── ActivityMapping
 └── Contractor

# 11. FUTURE OPERATIONAL MODULE STRUCTURE

Future independent operational modules:

```text
Labour Reporting
Material Received
Material Consumed
Material Required
Machinery Usage
Safety Reporting
Site Issues
Photos
Planning
```

These modules should work independently during the day.

DPR should later auto-link/consolidate them.

Labour UI Pages

Implemented pages:
/labour-reports
/labour-reports/create
/labour-reports/{id}
/labour-reports/{id}/edit

Labour Routes

Implemented:

GET     /labour-reports
GET     /labour-reports/create
POST    /labour-reports
GET     /labour-reports/{labourReport}
GET     /labour-reports/{labourReport}/edit
PUT     /labour-reports/{labourReport}
PATCH   /labour-reports/{labourReport}/submit
PATCH   /labour-reports/{labourReport}/approve

Labour ERP Importance

This module establishes the reusable execution pattern:
Project
→ Location Hierarchy
→ Activity Division
→ Activity
→ Contractor
→ Execution Data
→ Workflow

This same pattern should be reused for:

DPR
Material Consumed
Material Required
Machinery Usage
Planning
Productivity
Contractor Billing
PMO Verification

DPR Consolidation Readiness
Labour reports have:
- dpr_id nullable

Labour entries created independently during the day
→ DPR submitted later
→ system links same project + same date + same engineer labour records to DPR

---

# 12. FUTURE LABOUR REPORTING STRUCTURE

## labour_reports

Purpose:
Track labour attendance and productivity independently during the day.

Future Fields:

- id
- dpr_id nullable
- project_id
- user_id / engineer_id
- project_block_id
- project_floor_id
- project_unit_id
- project_room_id
- project_subspace_id
- work_stage
- activity_id
- activity_mapping_id
- contractor_id
- labour_category_id
- skilled_count
- semi_skilled_count
- helper_count
- semi_helper_count
- supervisor_count
- technician_count
- machine_operator_count
- male_count
- female_count
- local_count
- non_local_count
- total_labour
- shift
- work_output
- remarks
- entry_date
- entry_time
- status
- created_at
- updated_at

Future Relationships:

- belongsTo Project
- belongsTo User / Engineer
- belongsTo ProjectBlock
- belongsTo ProjectFloor
- belongsTo ProjectUnit
- belongsTo ProjectRoom
- belongsTo ProjectSubspace
- belongsTo Activity
- belongsTo ActivityMapping
- belongsTo Contractor

Future DPR Logic:

```text
Labour entries created during day
→ DPR submitted later
→ system links same project + same date + same engineer records to DPR
```

---

# 13. FUTURE MATERIAL RECEIVED STRUCTURE

Purpose:
Track site inward material movement independently.

Future Fields:

- id
- dpr_id nullable
- project_id
- user_id / engineer_id
- project_block_id nullable
- project_floor_id nullable
- project_unit_id nullable
- storage_location
- material_id
- vendor_id
- contractor_id nullable
- quantity_received
- unit
- vehicle_number
- driver_name
- challan_number
- bill_number
- received_date
- received_time
- material_condition
- accepted_quantity
- short_quantity
- damaged_quantity
- rejected_quantity
- material_photo
- challan_photo
- site_engineer_verified
- pmo_verification_status
- accountant_verification_status
- remarks
- created_at
- updated_at

Future Compatibility:

- inventory
- challan verification
- accountant verification
- reconciliation
- Odoo stock movement

---

# 14. FUTURE MATERIAL CONSUMED STRUCTURE

Purpose:
Track exact material usage against exact activity and location.

Future Fields:

- id
- dpr_id nullable
- project_id
- user_id / engineer_id
- project_block_id
- project_floor_id
- project_unit_id
- project_room_id
- project_subspace_id
- work_stage
- activity_id
- activity_mapping_id
- material_id
- contractor_id
- quantity_consumed
- unit
- related_work_output_quantity
- wastage_quantity
- wastage_reason
- balance_at_site
- photo
- remarks
- created_at
- updated_at

Critical For:

- reconciliation
- leakage control
- inventory
- productivity analytics
- contractor billing

---

# 15. FUTURE MATERIAL REQUIRED STRUCTURE

Purpose:
Track material requirements before or during DPR submission.

Future Fields:

- id
- dpr_id nullable
- project_id
- user_id / requested_by
- project_block_id
- project_floor_id
- project_unit_id
- project_room_id
- project_subspace_id
- activity_id
- activity_mapping_id
- material_id
- specification
- required_quantity
- unit
- required_date
- required_for
- priority
- stock_available_status
- reason
- pmo_approval_status
- procurement_status
- remarks
- created_at
- updated_at

Priority Options:

- Normal
- Urgent
- Critical

Critical requirements should appear in PMO and CEO dashboards.

---

# 16. FUTURE PMO WORKFLOW

```text
Draft
→ Submitted
→ PMO Review
→ Clarification Required
→ Resubmitted
→ Approved
→ Accountant Verified
→ Locked
```

Rules:

- Engineers can edit only in Draft
- Engineers cannot edit after submission
- PMO verifies site execution facts
- Accountant verifies material/cost transactions
- Locked entries require correction request
- No hard delete after approval

---

# 17. FUTURE MATERIAL RECONCILIATION LOGIC

Formula:

```text
Opening Stock
+ Material Received
- Material Consumed
- Material Wastage
- Material Transfer
- Material Return
= Closing Stock
```

Alert Conditions:

- consumed without received stock
- received without challan
- bill without received material
- high wastage
- material shortage
- material received but not used
- stock mismatch

---

# 18. FUTURE CEO CONTROL FLOW

```text
Execution Data
→ PMO Verification
→ Accountant Verification
→ Exception Engine
→ CEO Dashboard
→ Decision Queue
```

CEO should see:

- delays
- shortages
- leakages
- approvals
- escalations
- decisions

CEO should not see raw execution clutter.

---

# 19. FUTURE ODOO READINESS

All major tables should remain future-ready for:

- Odoo Projects
- Odoo Inventory
- Odoo Purchase
- Odoo Accounting
- Odoo Analytic Accounts
- Odoo Vendors

Important:

```text
No live Odoo integration yet.
Only backend compatibility readiness.
```

Future fields may include:

- odoo_project_id
- odoo_analytic_account_id
- odoo_analytic_tag_id
- odoo_product_id
- odoo_vendor_id
- odoo_stock_location_id
- odoo_purchase_order_id
- odoo_bill_id
- odoo_external_reference

---

# 20. FUTURE ERP ROADMAP

## Phase 1 — Practical Daily Execution

- Project Master
- Location Hierarchy
- Activity Mapping
- DPR
- Labour Reporting
- Material Received
- Material Consumed
- Material Required
- Machinery Usage
- Issues
- Safety Basic
- Photos
- Tomorrow Plan
- PMO Review
- Basic CEO Dashboard

## Phase 2 — PMO Control

- Clarification Workflow
- Plan vs Actual
- Mapping Pending Queue
- Material Reconciliation Alerts
- Contractor Productivity Alerts
- Drawing / Approval Dependency

## Phase 3 — Accountant Control

- GRN / Challan Matching
- Bill Pending Status
- Stock Balance
- Material Transfer
- Contractor Attendance vs Output
- Machinery Rental Tracking
- Wastage Reports

## Phase 4 — Advanced Corporate Control

- BOQ Mapping
- Billing Milestone Tracking
- Odoo Integration
- Contractor Performance Score
- Engineer Performance Score
- Productivity Norms
- Quality Checklist Module
- Variation Approval Workflow
- Client Dashboard if required

---

# 21. FINAL SUCCESS TARGET

```text
Maximum site information
Minimum typing
No cost-code exposure
No leakage
No miscommunication
Complete visibility from Site Engineer to CEO
```