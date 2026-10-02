<?php

namespace Database\Seeders;

use App\Models\WorkPackage;
use App\Models\WorkProjectType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkExecutionMasterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedProjectTypes();
            $this->seedWorkPackages();
        });
    }

    private function seedProjectTypes(): void
    {
        $projectTypes = [
            [
                'code' => 'GENERAL',
                'name' => 'General / Universal',
                'description' => 'Work activities broadly applicable across construction project types.',
                'is_general' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'RESIDENTIAL',
                'name' => 'Residential / Apartment',
                'description' => 'Apartments, residential towers, gated communities and similar residential developments.',
                'is_general' => false,
                'sort_order' => 10,
            ],
            [
                'code' => 'VILLA',
                'name' => 'Villa / Independent House',
                'description' => 'Independent houses, villas, duplex houses and similar low-rise residential projects.',
                'is_general' => false,
                'sort_order' => 20,
            ],
            [
                'code' => 'HOSPITAL',
                'name' => 'Hospital / Healthcare',
                'description' => 'Hospitals, clinics, diagnostic centres and healthcare facilities.',
                'is_general' => false,
                'sort_order' => 30,
            ],
            [
                'code' => 'EDUCATION',
                'name' => 'School / College / Institutional',
                'description' => 'Schools, colleges, universities, training centres and institutional buildings.',
                'is_general' => false,
                'sort_order' => 40,
            ],
            [
                'code' => 'OFFICE',
                'name' => 'Office / Commercial',
                'description' => 'Corporate offices, business centres and commercial office developments.',
                'is_general' => false,
                'sort_order' => 50,
            ],
            [
                'code' => 'MALL',
                'name' => 'Shopping Mall',
                'description' => 'Shopping malls, large retail centres, food courts and entertainment retail developments.',
                'is_general' => false,
                'sort_order' => 60,
            ],
            [
                'code' => 'SHOWROOM',
                'name' => 'Showroom / Retail',
                'description' => 'Retail stores, branded showrooms, dealerships and standalone retail spaces.',
                'is_general' => false,
                'sort_order' => 70,
            ],
            [
                'code' => 'HOTEL',
                'name' => 'Hotel / Hospitality',
                'description' => 'Hotels, resorts, serviced accommodation and hospitality projects.',
                'is_general' => false,
                'sort_order' => 80,
            ],
            [
                'code' => 'BANQUET',
                'name' => 'Function / Banquet Hall',
                'description' => 'Function halls, banquet halls, convention facilities and event venues.',
                'is_general' => false,
                'sort_order' => 90,
            ],
            [
                'code' => 'WAREHOUSE',
                'name' => 'Warehouse / Logistics',
                'description' => 'Warehouses, logistics buildings, distribution centres and storage facilities.',
                'is_general' => false,
                'sort_order' => 100,
            ],
            [
                'code' => 'INDUSTRIAL',
                'name' => 'Industrial',
                'description' => 'Factories, workshops, production buildings and industrial facilities.',
                'is_general' => false,
                'sort_order' => 110,
            ],
            [
                'code' => 'MIXED_USE',
                'name' => 'Mixed Use',
                'description' => 'Projects containing multiple occupancies such as residential, retail, office and hospitality.',
                'is_general' => false,
                'sort_order' => 120,
            ],
            [
                'code' => 'RELIGIOUS',
                'name' => 'Religious / Community',
                'description' => 'Places of worship, community halls and related community facilities.',
                'is_general' => false,
                'sort_order' => 130,
            ],
            [
                'code' => 'SPORTS',
                'name' => 'Sports / Recreation',
                'description' => 'Sports complexes, gyms, clubs, recreation centres and related facilities.',
                'is_general' => false,
                'sort_order' => 140,
            ],
        ];

        foreach ($projectTypes as $projectType) {
            WorkProjectType::updateOrCreate(
                [
                    'code' => $projectType['code'],
                ],
                array_merge(
                    $projectType,
                    [
                        'is_system' => true,
                        'is_active' => true,
                    ]
                )
            );
        }
    }

    private function seedWorkPackages(): void
    {
        $packages = [
            ['01', 'PRE_CONSTRUCTION', 'Pre-Construction & Approvals'],
            ['02', 'SURVEY_ENGINEERING', 'Survey, Setting Out & Engineering'],
            ['03', 'SITE_ESTABLISHMENT', 'Site Establishment & Mobilization'],
            ['04', 'SITE_PREPARATION', 'Site Preparation, Clearing & Demolition'],
            ['05', 'EARTHWORK', 'Earthwork, Excavation & Filling'],
            ['06', 'FOUNDATION', 'Foundations & Substructure'],
            ['07', 'RCC', 'RCC Structure'],
            ['08', 'STRUCTURAL_STEEL', 'Structural Steel & Metal Works'],
            ['09', 'MASONRY', 'Masonry & Partition Works'],
            ['10', 'PLASTERING', 'Plastering, Screeding & Rendering'],
            ['11', 'WATERPROOFING', 'Waterproofing & Damp Proofing'],
            ['12', 'ROOFING', 'Roofing & Roof Works'],
            ['13', 'FLOORING', 'Flooring, Tiling & Stone Works'],
            ['14', 'CEILING', 'Ceilings & False Ceiling'],
            ['15', 'DOORS_WINDOWS', 'Doors, Windows, Glazing & Hardware'],
            ['16', 'JOINERY', 'Joinery, Carpentry & Fixed Furniture'],
            ['17', 'PAINTING', 'Painting, Coatings & Decorative Finishes'],
            ['18', 'FACADE', 'Façade & External Envelope'],
            ['19', 'ELECTRICAL', 'Electrical Power & Lighting'],
            ['20', 'ELV_ICT', 'ELV, ICT, Security & Automation'],
            ['21', 'PLUMBING', 'Plumbing & Water Supply'],
            ['22', 'DRAINAGE', 'Drainage, Sewerage & Sanitary'],
            ['23', 'FIRE_FIGHTING', 'Fire Fighting & Life Safety'],
            ['24', 'FIRE_ALARM', 'Fire Alarm & Emergency Systems'],
            ['25', 'HVAC', 'HVAC & Mechanical Ventilation'],
            ['26', 'VERTICAL_TRANSPORT', 'Lifts, Escalators & Vertical Transportation'],
            ['27', 'MEDICAL_SYSTEMS', 'Medical & Healthcare Specialist Systems'],
            ['28', 'SPECIALIST_EQUIPMENT', 'Commercial Kitchen, Laundry & Specialist Equipment'],
            ['29', 'INTERIOR_FITOUT', 'Interior Fit-Out & Architectural Specialties'],
            ['30', 'ACOUSTIC_AV', 'Acoustic, Auditorium, Stage & AV Works'],
            ['31', 'EXTERNAL_DEVELOPMENT', 'External Development & Site Infrastructure'],
            ['32', 'ROADS_PARKING', 'Roads, Parking, Paving & Hardscape'],
            ['33', 'LANDSCAPING', 'Landscaping & Irrigation'],
            ['34', 'WATER_TREATMENT', 'Water Treatment, STP, WTP & Rainwater Systems'],
            ['35', 'UTILITY_CONNECTIONS', 'Utility & External Service Connections'],
            ['36', 'TEMPORARY_WORKS', 'Scaffolding, Access & Temporary Works'],
            ['37', 'SITE_OPERATIONS', 'General Site Operations & Support Works'],
            ['38', 'TESTING_COMMISSIONING', 'Testing, Inspection & Commissioning'],
            ['39', 'RECTIFICATION', 'Snagging, Rectification & Rework'],
            ['40', 'HANDOVER', 'Cleaning, Handover & Closeout'],
        ];

        foreach ($packages as [$number, $code, $name]) {
            WorkPackage::updateOrCreate(
                [
                    'code' => $code,
                ],
                [
                    'name' => $name,
                    'description' => null,
                    'sort_order' => (int) $number,
                    'is_system' => true,
                    'is_active' => true,
                    'remarks' => null,
                ]
            );
        }
    }
}