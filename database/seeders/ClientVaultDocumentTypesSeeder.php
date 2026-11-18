<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientVaultDocumentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $master = json_decode(<<<'JSON'
{
  "Contracts & Agreements": [
    "Construction Agreement",
    "Work Order",
    "Letter of Intent (LOI)",
    "Contract Addendum",
    "Scope of Work",
    "Payment Schedule Agreement",
    "Consultancy Agreement",
    "Contract Variation / Change Order",
    "Client Authorization Letter",
    "Termination / Closure Agreement"
  ],
  "Statutory Approvals & Permissions": [
    "Building Permission / Approval Letter",
    "Sanctioned Building Plan",
    "Land Use / Zoning Approval",
    "Commencement Certificate",
    "Fire NOC",
    "Environmental Clearance",
    "Utility Connection Approval",
    "Occupancy Certificate",
    "Completion Certificate",
    "Other Statutory NOC"
  ],
  "Certificates & Reports": [
    "Soil Investigation Report",
    "Structural Stability Certificate",
    "Material Test Certificate",
    "Concrete Cube Test Report",
    "Steel Test Certificate",
    "Waterproofing Test Report",
    "Electrical Test Report",
    "Plumbing Pressure Test Report",
    "Survey / Site Inspection Report",
    "Quality Inspection Report"
  ],
  "Official Correspondence": [
    "Client Approval Letter",
    "Consultant Correspondence",
    "Official Notice",
    "Meeting Minutes (MOM)",
    "Site Instruction",
    "Clarification / RFI Response",
    "Change Request Letter",
    "Authority Correspondence",
    "Contractor Correspondence",
    "Client Communication Record"
  ],
  "Warranties & Manuals": [
    "Waterproofing Warranty",
    "Product / Material Warranty",
    "Equipment Warranty",
    "Manufacturer Certificate",
    "Operation & Maintenance Manual",
    "Electrical Equipment Manual",
    "Plumbing Equipment Manual",
    "Door / Window Warranty",
    "Maintenance Schedule",
    "Supplier Warranty Letter"
  ],
  "Handover Records": [
    "Project Handover Certificate",
    "Practical Completion Certificate",
    "Final Completion Certificate",
    "Snag List",
    "Snag Closure Report",
    "Key Handover Record",
    "As-Built Documentation Handover",
    "Asset / Equipment Handover List",
    "Final Inspection Record",
    "Defect Liability Closure"
  ],
  "Other Project Documents": [
    "General Project Document",
    "Supporting Document",
    "Site Record",
    "Client-Provided Document",
    "Project Reference Document",
    "Miscellaneous Certificate"
  ]
}
JSON, true);
        foreach ($master as $categoryName => $titles) {
            $categoryId = DB::table('client_vault_categories')->where('name', $categoryName)->value('id');
            if (!$categoryId) {
                throw new \RuntimeException("Missing vault category: {$categoryName}");
            }
            foreach ($titles as $index => $title) {
                DB::table('client_vault_document_types')->updateOrInsert(
                    ['category_id' => $categoryId, 'title' => $title],
                    ['sort_order' => ($index + 1) * 10, 'is_custom' => false, 'updated_at' => now()]
                );
            }
            DB::table('client_vault_document_types')->updateOrInsert(
                ['category_id' => $categoryId, 'title' => 'Other / Custom Document'],
                ['sort_order' => 9999, 'is_custom' => true, 'updated_at' => now()]
            );
        }
    }
}
