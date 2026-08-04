<?php

namespace Database\Seeders;

use App\Models\DesignationRole;
use App\Models\LabourType;
use App\Models\SkillCategory;
use Illuminate\Database\Seeder;

class DesignationRoleSeeder extends Seeder
{
    /**
     * Seed designation and role examples used in Labour Master.
     */
    public function run(): void
    {
        $skillCategories = SkillCategory::query()
            ->pluck('id', 'code');

        $labourTypes = LabourType::query()
            ->get()
            ->mapWithKeys(function (LabourType $labourType): array {
                return [
    strtolower(trim($labourType->labour_type_name)) => $labourType->id,
];
            });

        $designations = [
            [
                'code' => 'HEAD_MASON',
                'name' => 'Head Mason',
                'labour_type_names' => [
                    'mason',
                    'masonry mason',
                ],
                'skill_code' => 'HIGHLY_SKILLED',
                'sort_order' => 10,
                'remarks' => 'Senior mason responsible for supervising masonry work and guiding the site masonry team.',
            ],
            [
                'code' => 'MASON',
                'name' => 'Mason',
                'labour_type_names' => [
                    'mason',
                    'masonry mason',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 20,
                'remarks' => 'Skilled masonry worker responsible for brickwork, blockwork, plastering, or related civil work.',
            ],
            [
                'code' => 'CIVIL_HELPER',
                'name' => 'Civil Helper',
                'labour_type_names' => [
                    'general labour / helper',
                    'civil helper',
                    'helper',
                ],
                'skill_code' => 'UNSKILLED',
                'sort_order' => 30,
                'remarks' => 'General helper supporting civil and structural construction activities.',
            ],
            [
                'code' => 'BAR_BENDER',
                'name' => 'Bar Bender',
                'labour_type_names' => [
                    'bar bender / steel fixer',
                    'bar bender',
                    'steel fixer',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 40,
                'remarks' => 'Skilled worker engaged in cutting, bending, placing, and tying reinforcement steel.',
            ],
            [
                'code' => 'STEEL_HELPER',
                'name' => 'Steel Helper',
                'labour_type_names' => [
                    'bar bender / steel fixer',
                    'steel helper',
                    'general labour / helper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 50,
                'remarks' => 'Helper supporting bar bending and reinforcement fixing activities.',
            ],
            [
                'code' => 'SHUTTERING_CARPENTER',
                'name' => 'Shuttering Carpenter',
                'labour_type_names' => [
                    'shuttering carpenter',
                    'carpenter',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 60,
                'remarks' => 'Skilled carpenter engaged in shuttering, formwork, centering, and related structural works.',
            ],
            [
                'code' => 'SHUTTERING_HELPER',
                'name' => 'Shuttering Helper',
                'labour_type_names' => [
                    'shuttering helper',
                    'general labour / helper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 70,
                'remarks' => 'Helper supporting shuttering, formwork, and centering activities.',
            ],
            [
                'code' => 'TILE_MASON',
                'name' => 'Tile Mason',
                'labour_type_names' => [
                    'tile layer',
                    'tile mason',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 80,
                'remarks' => 'Skilled worker engaged in floor tile, wall tile, and related finishing work.',
            ],
            [
                'code' => 'TILE_HELPER',
                'name' => 'Tile Helper',
                'labour_type_names' => [
                    'tile helper',
                    'general labour / helper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 90,
                'remarks' => 'Helper supporting tile cutting, mixing, handling, and installation activities.',
            ],
            [
                'code' => 'ELECTRICIAN',
                'name' => 'Electrician',
                'labour_type_names' => [
                    'electrician',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 100,
                'remarks' => 'Skilled worker engaged in electrical conduit, wiring, fixtures, panels, and testing work.',
            ],
            [
                'code' => 'ELECTRICAL_HELPER',
                'name' => 'Electrical Helper',
                'labour_type_names' => [
                    'electrical helper',
                    'general labour / helper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 110,
                'remarks' => 'Helper supporting electricians in conduit, wiring, material handling, and installation work.',
            ],
            [
                'code' => 'PLUMBER',
                'name' => 'Plumber',
                'labour_type_names' => [
                    'plumber',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 120,
                'remarks' => 'Skilled worker engaged in water supply, drainage, sanitary, and piping installations.',
            ],
            [
                'code' => 'PLUMBING_HELPER',
                'name' => 'Plumbing Helper',
                'labour_type_names' => [
                    'plumbing helper',
                    'general labour / helper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 130,
                'remarks' => 'Helper supporting plumbers in pipe preparation, handling, chasing, and installation.',
            ],
            [
                'code' => 'PAINTER',
                'name' => 'Painter',
                'labour_type_names' => [
                    'painter',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 140,
                'remarks' => 'Skilled worker engaged in putty, primer, painting, texture, and surface finishing.',
            ],
            [
                'code' => 'PAINTING_HELPER',
                'name' => 'Painting Helper',
                'labour_type_names' => [
                    'painting helper',
                    'general labour / helper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 150,
                'remarks' => 'Helper supporting painting preparation, material handling, masking, and cleaning work.',
            ],
            [
                'code' => 'CARPENTER',
                'name' => 'Carpenter',
                'labour_type_names' => [
                    'carpenter',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 160,
                'remarks' => 'Skilled worker engaged in carpentry, joinery, doors, furniture, or interior installation.',
            ],
            [
                'code' => 'CARPENTER_HELPER',
                'name' => 'Carpenter Helper',
                'labour_type_names' => [
                    'carpenter helper',
                    'general labour / helper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 170,
                'remarks' => 'Helper supporting carpentry and interior installation activities.',
            ],
            [
                'code' => 'MACHINE_OPERATOR',
                'name' => 'Machine Operator',
                'labour_type_names' => [
                    'machine operator',
                    'crane operator',
                    'jcb operator',
                    'excavator operator',
                ],
                'skill_code' => 'SKILLED',
                'sort_order' => 180,
                'remarks' => 'Operator responsible for construction machinery, equipment, or specialized site machines.',
            ],
            [
                'code' => 'SITE_SUPERVISOR',
                'name' => 'Site Supervisor',
                'labour_type_names' => [
                    'safety officer / supervisor',
                    'site supervisor',
                    'supervisor',
                ],
                'skill_code' => 'HIGHLY_SKILLED',
                'sort_order' => 190,
                'remarks' => 'Site-level supervisor responsible for manpower coordination, execution monitoring, and reporting.',
            ],
            [
                'code' => 'STORE_ASSISTANT',
                'name' => 'Store Assistant',
                'labour_type_names' => [
                    'store keeper / material handler',
                    'store assistant',
                    'store keeper',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 200,
                'remarks' => 'Site store personnel supporting material receipt, issue, storage, and record maintenance.',
            ],
            [
                'code' => 'SECURITY_GUARD',
                'name' => 'Security Guard',
                'labour_type_names' => [
                    'security guard',
                    'watchman / security',
                    'watchman',
                ],
                'skill_code' => 'SEMI_SKILLED',
                'sort_order' => 210,
                'remarks' => 'Security personnel assigned for site access control and asset protection.',
            ],
            [
                'code' => 'GENERAL_HELPER',
                'name' => 'General Helper',
                'labour_type_names' => [
                    'general labour / helper',
                    'general helper',
                    'helper',
                ],
                'skill_code' => 'UNSKILLED',
                'sort_order' => 220,
                'remarks' => 'General-purpose helper used across construction, material handling, and housekeeping activities.',
            ],
        ];

        foreach ($designations as $designation) {
            $labourTypeId = $this->resolveLabourTypeId(
                $designation['labour_type_names'],
                $labourTypes
            );

            DesignationRole::updateOrCreate(
                [
                    'code' => $designation['code'],
                ],
                [
                    'name' => $designation['name'],
                    'labour_type_id' => $labourTypeId,
                    'skill_category_id' => $skillCategories[
                        $designation['skill_code']
                    ] ?? null,
                    'is_system' => true,
                    'sort_order' => $designation['sort_order'],
                    'is_active' => true,
                    'remarks' => $designation['remarks'],
                ]
            );
        }
    }

    /**
     * Resolve the first matching Labour Type ID.
     */
    private function resolveLabourTypeId(
        array $possibleNames,
        $labourTypes
    ): ?int {
        foreach ($possibleNames as $possibleName) {
            $normalizedName = strtolower(trim($possibleName));

            if ($labourTypes->has($normalizedName)) {
                return (int) $labourTypes->get($normalizedName);
            }
        }

        return null;
    }
}