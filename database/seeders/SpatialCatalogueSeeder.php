<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpatialCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $categories = [
                ['RESIDENTIAL','Residential',['Housing','Homes']],
                ['COMMERCIAL','Commercial / Office',['Office','Business']],
                ['HEALTHCARE','Healthcare',['Hospital','Medical']],
                ['EDUCATIONAL','Educational',['School','Education']],
                ['HOSPITALITY','Hospitality / Banquet',['Hotel','Function Hall','Convention']],
                ['RETAIL','Retail / Shopping',['Mall','Showroom']],
                ['BANKING','Banking / Institutional',['Bank','Financial Institution']],
                ['INDUSTRIAL','Industrial / Warehouse',['Factory','Warehouse']],
                ['MIXED_USE','Mixed Use',['Mixed-Use']],
                ['OTHER','Other / Custom',['Custom']],
            ];
            foreach ($categories as $i=>$r) {
                DB::table('spatial_project_categories')->updateOrInsert(['code'=>$r[0]],[
                    'name'=>$r[1],'aliases'=>json_encode($r[2]),'sort_order'=>($i+1)*10,'is_system'=>1,'is_active'=>1,
                    'created_at'=>now(),'updated_at'=>now()
                ]);
            }
            $catIds=DB::table('spatial_project_categories')->pluck('id','code');

            $projectTypes = [
                'RESIDENTIAL'=>['INDEPENDENT_HOUSE'=>'Independent House','DUPLEX_HOUSE'=>'Duplex House','TRIPLEX_HOUSE'=>'Triplex House','VILLA'=>'Villa','APARTMENT'=>'Apartment Building','GROUP_HOUSING'=>'Group Housing'],
                'COMMERCIAL'=>['COMMERCIAL_BUILDING'=>'Commercial Building','OFFICE_BUILDING'=>'Office Building','BUSINESS_CENTRE'=>'Business Centre','COWORKING'=>'Co-working Facility'],
                'HEALTHCARE'=>['HOSPITAL'=>'Hospital','CLINIC'=>'Clinic','DIAGNOSTIC_CENTRE'=>'Diagnostic Centre','DAY_CARE_HOSPITAL'=>'Day Care Hospital'],
                'EDUCATIONAL'=>['SCHOOL'=>'School','COLLEGE'=>'College','UNIVERSITY'=>'University','TRAINING_CENTRE'=>'Training Centre'],
                'HOSPITALITY'=>['HOTEL'=>'Hotel','RESORT'=>'Resort','BANQUET_HALL'=>'Banquet Hall','FUNCTION_HALL'=>'Function Hall','CONVENTION_CENTRE'=>'Convention Centre'],
                'RETAIL'=>['SHOPPING_MALL'=>'Shopping Mall','RETAIL_SHOWROOM'=>'Retail Showroom','SUPERMARKET'=>'Supermarket','RETAIL_COMPLEX'=>'Retail Complex'],
                'BANKING'=>['BANK_BRANCH'=>'Bank Branch','FINANCIAL_INSTITUTION'=>'Financial Institution','INSTITUTIONAL_BUILDING'=>'Institutional Building'],
                'INDUSTRIAL'=>['FACTORY'=>'Factory','WAREHOUSE'=>'Warehouse','WORKSHOP'=>'Workshop','LOGISTICS_FACILITY'=>'Logistics Facility'],
                'MIXED_USE'=>['MIXED_USE_BUILDING'=>'Mixed-Use Building'],
                'OTHER'=>['EPC_PROJECT'=>'Large-Size EPC Project','CUSTOM_PROJECT'=>'Custom Project'],
            ];
            $n=0;
            foreach($projectTypes as $cat=>$rows) foreach($rows as $code=>$name) {
                DB::table('spatial_project_types')->updateOrInsert(['code'=>$code],[
                    'spatial_project_category_id'=>$catIds[$cat],'name'=>$name,'sort_order'=>(++$n)*10,'is_system'=>1,'is_active'=>1,
                    'created_at'=>now(),'updated_at'=>now()
                ]);
            }

            $spaceCats = [
                'RES_PRIVATE'=>'Residential Private','RES_COMMON'=>'Residential Common','SANITARY'=>'Sanitary / Washrooms','KITCHEN_UTILITY'=>'Kitchen / Utility',
                'CIRCULATION'=>'Circulation / Access','PARKING'=>'Parking','BUILDING_SERVICES'=>'Building Services','COMMERCIAL_RETAIL'=>'Commercial / Retail',
                'OFFICE_ADMIN'=>'Office / Administration','CLINICAL'=>'Clinical / OPD','DIAGNOSTIC'=>'Diagnostic','SURGICAL'=>'Surgical / Sterile',
                'PATIENT_CARE'=>'Patient Care','ACADEMIC'=>'Academic','EDU_SUPPORT'=>'Educational Support','HOTEL_GUEST'=>'Hotel Guest',
                'FOOD_BEVERAGE'=>'Food / Beverage','EVENT'=>'Banquet / Event','BANKING_SECURITY'=>'Banking / Security','INDUSTRIAL_SPACE'=>'Industrial / Warehouse',
                'EXTERNAL_SITE'=>'External / Site','CUSTOM'=>'Custom / Other'
            ];
            $i=0;
            foreach($spaceCats as $code=>$name) DB::table('spatial_space_categories')->updateOrInsert(['code'=>$code],[
                'name'=>$name,'sort_order'=>(++$i)*10,'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
            ]);
            $sc=DB::table('spatial_space_categories')->pluck('id','code');

            $types = [
              'RES_PRIVATE'=>[
                'MASTER_BEDROOM'=>['Master Bedroom',['Master Bed Room']],
                'BEDROOM'=>['Bedroom',['Bed Room']],
                'CHILDREN_BEDROOM'=>['Children Bedroom',['Kids Bedroom']],
                'GUEST_BEDROOM'=>['Guest Bedroom',[]],
                'LIVING_ROOM'=>['Living Room',['Living Hall','Hall']],
                'FAMILY_LOUNGE'=>['Family Lounge',['Family Hall']],
                'DINING_ROOM'=>['Dining Room',['Dining']],
                'POOJA_ROOM'=>['Pooja Room',['Prayer Room']],
                'STUDY_ROOM'=>['Study Room',['Study']],
                'HOME_OFFICE'=>['Home Office',[]],
                'DRESSING_ROOM'=>['Dressing Room',['Dresser']],
                'WALK_IN_WARDROBE'=>['Walk-in Wardrobe',['Walk-in Closet']],
              ],
              'RES_COMMON'=>[
                'BALCONY'=>['Balcony',[]],'SIT_OUT'=>['Sit-out',['Sitout']],'TERRACE'=>['Terrace',[]],
                'FOYER'=>['Foyer',['Entrance Lobby']],'COMMON_LOUNGE'=>['Common Lounge',[]]
              ],
              'SANITARY'=>[
                'ATTACHED_WASHROOM'=>['Attached Washroom',['Attached Bathroom','Attached Toilet']],
                'COMMON_WASHROOM'=>['Common Washroom',['Common Bathroom','Common Toilet']],
                'POWDER_ROOM'=>['Powder Room',[]],'PUBLIC_TOILET'=>['Public Toilet',['Public Washroom']],
                'ACCESSIBLE_TOILET'=>['Accessible Toilet',['Disabled Toilet','Universal Toilet']],
                'STAFF_TOILET'=>['Staff Toilet',[]]
              ],
              'KITCHEN_UTILITY'=>[
                'KITCHEN'=>['Kitchen',[]],'UTILITY_AREA'=>['Utility Area',['Wash Area']],
                'PANTRY'=>['Pantry',[]],'COMMERCIAL_KITCHEN'=>['Commercial Kitchen',[]],
                'DISHWASH_AREA'=>['Dishwash Area',['Pot Wash']]
              ],
              'CIRCULATION'=>[
                'CORRIDOR'=>['Corridor',['Passage']],'STAIRCASE'=>['Staircase',['Stairs']],
                'LIFT_LOBBY'=>['Lift Lobby',['Elevator Lobby']],'MAIN_LOBBY'=>['Main Lobby',['Lobby']],
                'ENTRANCE_LOBBY'=>['Entrance Lobby',[]],'RAMP'=>['Ramp',[]],'FIRE_EXIT'=>['Fire Exit',['Emergency Exit']]
              ],
              'PARKING'=>[
                'PARKING_AREA'=>['Parking Area',[]],'PARKING_BAY'=>['Parking Bay',[]],
                'TWO_WHEELER_PARKING'=>['Two-Wheeler Parking',[]],'EV_PARKING'=>['EV Parking',[]]
              ],
              'BUILDING_SERVICES'=>[
                'SECURITY_ROOM'=>['Security Room',['Watchman Room']],'ELECTRICAL_ROOM'=>['Electrical Room',[]],
                'PUMP_ROOM'=>['Pump Room',[]],'METER_ROOM'=>['Meter Room',[]],'DG_ROOM'=>['DG Room',['Generator Room']],
                'TRANSFORMER_ROOM'=>['Transformer Room',[]],'FIRE_PUMP_ROOM'=>['Fire Pump Room',[]],
                'STP_ROOM'=>['STP / Sewage Treatment Area',['STP']],'WTP_ROOM'=>['WTP / Water Treatment Area',['WTP']],
                'SERVICE_SHAFT'=>['Service Shaft',['Shaft']],'HVAC_PLANT_ROOM'=>['HVAC Plant Room',[]],
                'AHU_ROOM'=>['AHU Room',[]],'UPS_ROOM'=>['UPS Room',[]],'SERVER_ROOM'=>['Server / IT Room',['IT Room']],
                'STORE_ROOM'=>['Store Room',['Storage Room']],'HOUSEKEEPING_ROOM'=>['Housekeeping Room',['Janitor Room']]
              ],
              'COMMERCIAL_RETAIL'=>[
                'SHOP'=>['Shop',['Retail Shop']],'SHOWROOM'=>['Showroom',[]],'KIOSK'=>['Kiosk',[]],
                'ANCHOR_STORE'=>['Anchor Store',[]],'FOOD_COURT'=>['Food Court',[]],'ATRIUM'=>['Atrium',[]],
                'LOADING_UNLOADING'=>['Loading / Unloading Area',['Loading Bay']],'SHOP_BACKROOM'=>['Shop Backroom',['Stock Room']]
              ],
              'OFFICE_ADMIN'=>[
                'OPEN_OFFICE'=>['Open Office',['Open Workspace']],'PRIVATE_CABIN'=>['Private Cabin',['Cabin']],
                'MANAGER_CABIN'=>['Manager Cabin',[]],'MEETING_ROOM'=>['Meeting Room',[]],
                'CONFERENCE_ROOM'=>['Conference Room',[]],'BOARD_ROOM'=>['Board Room',['Boardroom']],
                'RECEPTION'=>['Reception',[]],'ADMIN_OFFICE'=>['Administration Office',['Admin Office']],
                'HR_ROOM'=>['HR Room',[]],'PRINT_COPY_ROOM'=>['Print / Copy Room',[]],'BREAKOUT_AREA'=>['Breakout Area',[]]
              ],
              'CLINICAL'=>[
                'HOSPITAL_RECEPTION'=>['Hospital Reception',[]],'REGISTRATION'=>['Registration',[]],
                'WAITING_AREA'=>['Waiting Area',[]],'OPD'=>['OPD',['Outpatient Department']],
                'CONSULTATION_ROOM'=>['Consultation Room',['Doctor Consultation']],
                'EXAMINATION_ROOM'=>['Examination Room',[]],'EMERGENCY'=>['Emergency Department',['Casualty']],
                'TRIAGE'=>['Triage Area',[]],'TREATMENT_ROOM'=>['Treatment Room',[]],'INJECTION_ROOM'=>['Injection Room',[]]
              ],
              'DIAGNOSTIC'=>[
                'LABORATORY'=>['Laboratory',['Lab']],'SAMPLE_COLLECTION'=>['Sample Collection Room',[]],
                'XRAY_ROOM'=>['X-Ray Room',['Radiography Room']],'CT_ROOM'=>['CT Scan Room',['CT Room']],
                'MRI_ROOM'=>['MRI Room',[]],'ULTRASOUND_ROOM'=>['Ultrasound Room',['USG Room']],
                'BLOOD_BANK'=>['Blood Bank',[]],'PHARMACY'=>['Pharmacy',[]]
              ],
              'SURGICAL'=>[
                'OPERATION_THEATRE'=>['Operation Theatre',['OT','Operating Room']],
                'PRE_OP'=>['Pre-Operative Area',['Pre-op']],'POST_OP_RECOVERY'=>['Post-Operative Recovery',['Recovery Room','PACU']],
                'SCRUB_AREA'=>['Scrub Area',[]],'STERILE_STORE'=>['Sterile Store',[]],
                'CSSD'=>['CSSD',['Central Sterile Supply Department']]
              ],
              'PATIENT_CARE'=>[
                'GENERAL_WARD'=>['General Ward',[]],'PATIENT_ROOM'=>['Patient Room',[]],
                'PRIVATE_PATIENT_ROOM'=>['Private Patient Room',[]],'ISOLATION_ROOM'=>['Isolation Room',[]],
                'ICU'=>['ICU',['Intensive Care Unit']],'NICU'=>['NICU',['Neonatal ICU']],
                'PICU'=>['PICU',['Paediatric ICU']],'NURSE_STATION'=>['Nurse Station',['Nursing Station']],
                'MEDICAL_GAS_MANIFOLD'=>['Medical Gas Manifold Room',[]]
              ],
              'ACADEMIC'=>[
                'CLASSROOM'=>['Classroom',['Class Room']],'SCIENCE_LAB'=>['Science Laboratory',['Science Lab']],
                'COMPUTER_LAB'=>['Computer Lab',[]],'LANGUAGE_LAB'=>['Language Lab',[]],
                'LIBRARY'=>['Library',[]],'LECTURE_HALL'=>['Lecture Hall',[]],'SEMINAR_HALL'=>['Seminar Hall',[]]
              ],
              'EDU_SUPPORT'=>[
                'PRINCIPAL_ROOM'=>['Principal Room',['Principal Office']],'STAFF_ROOM'=>['Staff Room',[]],
                'EXAM_ROOM'=>['Examination Room',['Exam Room']],'ACTIVITY_ROOM'=>['Activity Room',[]],
                'SCHOOL_ADMIN'=>['School Administration',['School Admin']],'AUDITORIUM'=>['Auditorium',[]],
                'MULTIPURPOSE_HALL'=>['Multipurpose Hall',[]],'SCHOOL_CAFETERIA'=>['School Cafeteria',['Canteen']]
              ],
              'HOTEL_GUEST'=>[
                'GUEST_ROOM'=>['Guest Room',['Hotel Room']],'HOTEL_SUITE'=>['Hotel Suite',['Suite']],
                'HOTEL_BATHROOM'=>['Hotel Bathroom',['Guest Bathroom']],'HOTEL_LOBBY'=>['Hotel Lobby',[]],
                'HOTEL_RECEPTION'=>['Hotel Reception',[]],'LINEN_ROOM'=>['Linen Room',[]]
              ],
              'FOOD_BEVERAGE'=>[
                'RESTAURANT'=>['Restaurant',[]],'CAFE'=>['Cafe',['Coffee Shop']],'BAR_AREA'=>['Bar Area',[]],
                'BUFFET_AREA'=>['Buffet Area',[]],'BANQUET_KITCHEN'=>['Banquet Kitchen',[]]
              ],
              'EVENT'=>[
                'BANQUET_HALL'=>['Banquet Hall',['Function Hall']],'CONVENTION_HALL'=>['Convention Hall',[]],
                'CONFERENCE_HALL'=>['Conference Hall',[]],'STAGE'=>['Stage',[]],'GREEN_ROOM'=>['Green Room',[]],
                'PRE_FUNCTION_AREA'=>['Pre-Function Area',['Pre-function']],'BRIDE_ROOM'=>['Bride Room',['Bridal Room']],
                'GROOM_ROOM'=>['Groom Room',[]]
              ],
              'BANKING_SECURITY'=>[
                'BANKING_HALL'=>['Banking Hall',[]],'TELLER_AREA'=>['Teller / Cash Counter',['Cash Counter']],
                'CUSTOMER_SERVICE'=>['Customer Service Area',[]],'BANK_MANAGER_CABIN'=>['Bank Manager Cabin',[]],
                'VAULT'=>['Vault / Strong Room',['Strong Room']],'LOCKER_ROOM'=>['Locker Room',[]],
                'ATM_ROOM'=>['ATM Room',['ATM']],'RECORD_ROOM'=>['Record Room',[]],'SECURITY_CONTROL_ROOM'=>['Security Control Room',[]]
              ],
              'INDUSTRIAL_SPACE'=>[
                'PRODUCTION_AREA'=>['Production Area',['Production Floor']],'WAREHOUSE'=>['Warehouse',[]],
                'RAW_MATERIAL_STORE'=>['Raw Material Store',[]],'FINISHED_GOODS_STORE'=>['Finished Goods Store',[]],
                'INDUSTRIAL_LOADING_BAY'=>['Industrial Loading Bay',['Loading Dock']],'WORKSHOP'=>['Workshop',[]],
                'QC_LAB'=>['Quality Control Lab',['QC Lab']],'PLANT_ROOM'=>['Plant Room',[]]
              ],
              'EXTERNAL_SITE'=>[
                'LANDSCAPE_AREA'=>['Landscape Area',['Landscaping']],'DRIVEWAY'=>['Driveway',[]],
                'EXTERNAL_PARKING'=>['External Parking',[]],'UTILITY_YARD'=>['Utility Yard',[]],
                'GATE_AREA'=>['Gate / Entrance Area',['Main Gate']],'COMPOUND_WALL_AREA'=>['Compound Wall Area',[]]
              ],
              'CUSTOM'=>['CUSTOM_SPACE'=>['Custom Space',['Other Space']]]
            ];

            $sort=0;
            foreach($types as $cat=>$rows) foreach($rows as $code=>$v) {
                DB::table('spatial_space_types')->updateOrInsert(['code'=>$code],[
                    'spatial_space_category_id'=>$sc[$cat],'name'=>$v[0],'aliases'=>json_encode($v[1]),
                    'default_location_level'=>'room','allows_children'=>1,'allows_geometry'=>1,'sort_order'=>(++$sort)*10,
                    'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
                ]);
            }
            $st=DB::table('spatial_space_types')->pluck('id','code');

            $subtypes=[
                'BEDROOM'=>['STANDARD_BEDROOM'=>'Standard Bedroom'],
                'KITCHEN'=>['OPEN_KITCHEN'=>'Open Kitchen','CLOSED_KITCHEN'=>'Closed Kitchen'],
                'OPERATION_THEATRE'=>['MAJOR_OT'=>'Major OT','MINOR_OT'=>'Minor OT','MODULAR_OT'=>'Modular OT'],
                'PATIENT_ROOM'=>['SINGLE_PATIENT_ROOM'=>'Single Patient Room','TWIN_PATIENT_ROOM'=>'Twin Patient Room'],
                'CLASSROOM'=>['PRIMARY_CLASSROOM'=>'Primary Classroom','SECONDARY_CLASSROOM'=>'Secondary Classroom'],
                'GUEST_ROOM'=>['STANDARD_GUEST_ROOM'=>'Standard Guest Room','DELUXE_GUEST_ROOM'=>'Deluxe Guest Room'],
                'HOTEL_SUITE'=>['EXECUTIVE_SUITE'=>'Executive Suite','FAMILY_SUITE'=>'Family Suite'],
                'SHOP'=>['SMALL_RETAIL_SHOP'=>'Small Retail Shop','LARGE_RETAIL_SHOP'=>'Large Retail Shop'],
                'WAREHOUSE'=>['GENERAL_WAREHOUSE'=>'General Warehouse','HIGH_BAY_WAREHOUSE'=>'High-Bay Warehouse']
            ];
            $s=0;
            foreach($subtypes as $parent=>$rows) foreach($rows as $code=>$name) DB::table('spatial_space_subtypes')->updateOrInsert(['code'=>$code],[
                'spatial_space_type_id'=>$st[$parent],'name'=>$name,'sort_order'=>(++$s)*10,'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
            ]);

            $components=[
              'ENTRANCE_WALL'=>['Entrance Wall','Wall'], 'HEADBOARD_WALL'=>['Headboard / Bed Wall','Wall'],
              'WARDROBE_WALL'=>['Wardrobe Wall','Wall'],'WINDOW_WALL'=>['Window Wall','Wall'],'BALCONY_WALL'=>['Balcony Wall','Wall'],
              'ATTACHED_WASHROOM_WALL'=>['Attached Washroom Wall','Wall'],'TV_WALL'=>['TV Wall','Wall'],'EXTERNAL_WALL'=>['External Wall','Wall'],
              'PARTITION_WALL'=>['Partition Wall','Wall'],'SHOWER_WALL'=>['Shower Wall','Wall'],'WC_WALL'=>['WC Wall','Wall'],
              'BASIN_WALL'=>['Basin / Vanity Wall','Wall'],'PLUMBING_WALL'=>['Plumbing Wall','Wall'],'VENTILATOR_WALL'=>['Ventilator Wall','Wall'],
              'SHAFT_WALL'=>['Shaft Wall','Wall'],'COUNTER_WALL'=>['Counter Wall','Wall'],'HOB_WALL'=>['Hob Wall','Wall'],
              'SINK_WALL'=>['Sink Wall','Wall'],'TALL_UNIT_WALL'=>['Tall Unit Wall','Wall'],'REFRIGERATOR_WALL'=>['Refrigerator Wall','Wall'],
              'UTILITY_DOOR_WALL'=>['Utility Door Wall','Wall'],'SERVICE_SHAFT_WALL'=>['Service Shaft Wall','Wall'],
              'TEACHING_WALL'=>['Teaching / Display Wall','Wall'],'STORAGE_WALL'=>['Storage Wall','Wall'],'EQUIPMENT_WALL'=>['Equipment Wall','Wall'],
              'MEDICAL_GAS_WALL'=>['Medical Gas Wall','Wall'],'SCRUB_WALL'=>['Scrub Wall','Wall'],'BED_HEAD_PANEL_WALL'=>['Bed Head Panel Wall','Wall'],
              'NURSE_CALL_WALL'=>['Nurse Call Wall','Wall'],'SERVICE_WALL'=>['Service Wall','Wall'],'GLAZED_WALL'=>['Glazed Wall','Wall'],
              'FIRE_RATED_WALL'=>['Fire-Rated Wall','Wall'],'ACOUSTIC_WALL'=>['Acoustic Wall','Wall'],'SECURITY_WALL'=>['Security Wall','Wall'],
              'VAULT_WALL'=>['Vault Wall','Wall'],'SHOPFRONT_WALL'=>['Shopfront Wall','Wall'],'DISPLAY_WALL'=>['Display Wall','Wall'],
              'LOADING_WALL'=>['Loading Wall','Wall'],'CUSTOM_WALL'=>['Custom Wall','Wall']
            ];
            $i=0;
            foreach($components as $code=>$v) DB::table('spatial_component_types')->updateOrInsert(['code'=>$code],[
                'name'=>$v[0],'component_group'=>$v[1],'supports_openings'=>1,'supports_connection'=>1,'is_measurable'=>1,
                'sort_order'=>(++$i)*10,'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
            ]);
            $comp=DB::table('spatial_component_types')->pluck('id','code');

            $openings=[
              'MAIN_DOOR'=>['Main Door','Door',1],'INTERNAL_DOOR'=>['Internal Door','Door',1],'TOILET_DOOR'=>['Toilet Door','Door',1],
              'ATTACHED_WASHROOM_DOOR'=>['Attached Washroom Door','Door',1],'BALCONY_DOOR'=>['Balcony Door','Door',1],
              'SLIDING_DOOR'=>['Sliding Door','Door',1],'FRENCH_DOOR'=>['French Door','Door',1],'FIRE_DOOR'=>['Fire Door','Door',1],
              'SERVICE_DOOR'=>['Service Door','Door',1],'EMERGENCY_EXIT_DOOR'=>['Emergency Exit Door','Door',1],
              'AUTOMATIC_DOOR'=>['Automatic Door','Door',1],'GLASS_DOOR'=>['Glass Door','Door',1],
              'WINDOW'=>['Window','Window',0],'SLIDING_WINDOW'=>['Sliding Window','Window',0],'CASEMENT_WINDOW'=>['Casement Window','Window',0],
              'FIXED_GLAZING'=>['Fixed Glazing','Window',0],'FRENCH_WINDOW'=>['French Window','Window',0],
              'VENTILATOR'=>['Ventilator','Ventilator',0],'LOUVER'=>['Louver','Ventilator',0],
              'SHAFT_OPENING'=>['Shaft Opening','Service Opening',0],'SERVICE_OPENING'=>['Service Opening','Service Opening',0],
              'PASS_BOX'=>['Pass Box','Service Opening',0],'ROLLING_SHUTTER'=>['Rolling Shutter','Door',1],
              'SHOPFRONT_OPENING'=>['Shopfront Opening','Other',0],'CUSTOM_OPENING'=>['Custom Opening','Other',1]
            ];
            $i=0;
            foreach($openings as $code=>$v) DB::table('spatial_opening_types')->updateOrInsert(['code'=>$code],[
                'name'=>$v[0],'opening_group'=>$v[1],'can_connect_spaces'=>$v[2],'deduct_from_wall_area'=>1,'sort_order'=>(++$i)*10,
                'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
            ]);
            $op=DB::table('spatial_opening_types')->pluck('id','code');

            $zones=[
              'FLOOR'=>['Floor','Floor','area','sq.ft'],'CEILING'=>['Ceiling','Ceiling','area','sq.ft'],
              'INTERNAL_WALL'=>['Internal Wall','Wall','area','sq.ft'],'EXTERNAL_WALL_ZONE'=>['External Wall','Wall','area','sq.ft'],
              'SKIRTING'=>['Skirting','Wall','length','r.ft'],'DADO'=>['Dado','Wall','area','sq.ft'],'WALL_TILE'=>['Wall Tile Zone','Wall','area','sq.ft'],
              'SHOWER_TILE'=>['Shower Tile Zone','Wet Area','area','sq.ft'],'WATERPROOF_FLOOR'=>['Waterproof Floor','Wet Area','area','sq.ft'],
              'WATERPROOF_UPTURN'=>['Waterproof Upturn','Wet Area','area','sq.ft'],'PLUMBING_CHASE'=>['Plumbing Chase','Service','area','sq.ft'],
              'COUNTERTOP'=>['Countertop','Counter','area','sq.ft'],'KITCHEN_DADO'=>['Kitchen Dado','Wall','area','sq.ft'],
              'DOOR_REVEAL'=>['Door Reveal','Opening','area','sq.ft'],'WINDOW_REVEAL'=>['Window Reveal','Opening','area','sq.ft'],
              'SILL'=>['Sill','Opening','length','r.ft'],'SOFFIT'=>['Soffit','Ceiling','area','sq.ft'],'WET_AREA'=>['Wet Area','Wet Area','area','sq.ft'],
              'BED_HEAD_ZONE'=>['Bed Head Zone','Wall','area','sq.ft'],'MEDICAL_GAS_ZONE'=>['Medical Gas Zone','Service','area','sq.ft'],
              'STERILE_WALL_ZONE'=>['Sterile Wall Zone','Wall','area','sq.ft'],'ACOUSTIC_TREATMENT'=>['Acoustic Treatment','Wall','area','sq.ft'],
              'FIREPROOFING'=>['Fireproofing','Surface','area','sq.ft'],'SHOPFRONT'=>['Shopfront','Wall','area','sq.ft'],
              'EPOXY_FLOOR'=>['Epoxy Floor','Floor','area','sq.ft'],'VINYL_FLOOR'=>['Vinyl Floor','Floor','area','sq.ft'],
              'FALSE_CEILING'=>['False Ceiling','Ceiling','area','sq.ft'],'CUSTOM_ZONE'=>['Custom Measurement Zone','Custom','custom',null]
            ];
            $i=0;
            foreach($zones as $code=>$v) DB::table('spatial_measurement_zones')->updateOrInsert(['code'=>$code],[
                'name'=>$v[0],'zone_group'=>$v[1],'measurement_basis'=>$v[2],'default_unit'=>$v[3],'sort_order'=>(++$i)*10,
                'is_system'=>1,'is_active'=>1,'created_at'=>now(),'updated_at'=>now()
            ]);
            $zone=DB::table('spatial_measurement_zones')->pluck('id','code');

            $genericWalls=['ENTRANCE_WALL','WINDOW_WALL','EXTERNAL_WALL','PARTITION_WALL','CUSTOM_WALL'];
            $genericOpen=['INTERNAL_DOOR','WINDOW','CUSTOM_OPENING'];
            $genericZones=['FLOOR','CEILING','INTERNAL_WALL','SKIRTING','DOOR_REVEAL','WINDOW_REVEAL'];
            $contexts=[
              'MASTER_BEDROOM'=>[
                ['ENTRANCE_WALL','HEADBOARD_WALL','WARDROBE_WALL','WINDOW_WALL','BALCONY_WALL','ATTACHED_WASHROOM_WALL','TV_WALL','EXTERNAL_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['INTERNAL_DOOR','ATTACHED_WASHROOM_DOOR','BALCONY_DOOR','SLIDING_DOOR','WINDOW','SLIDING_WINDOW','FRENCH_WINDOW','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','EXTERNAL_WALL_ZONE','SKIRTING','DOOR_REVEAL','WINDOW_REVEAL','BED_HEAD_ZONE']
              ],
              'BEDROOM'=>[
                ['ENTRANCE_WALL','HEADBOARD_WALL','WARDROBE_WALL','WINDOW_WALL','TV_WALL','EXTERNAL_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['INTERNAL_DOOR','WINDOW','SLIDING_WINDOW','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','EXTERNAL_WALL_ZONE','SKIRTING','DOOR_REVEAL','WINDOW_REVEAL','BED_HEAD_ZONE']
              ],
              'ATTACHED_WASHROOM'=>[
                ['SHOWER_WALL','WC_WALL','BASIN_WALL','PLUMBING_WALL','VENTILATOR_WALL','SHAFT_WALL','EXTERNAL_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['ATTACHED_WASHROOM_DOOR','TOILET_DOOR','VENTILATOR','WINDOW','SHAFT_OPENING','CUSTOM_OPENING'],
                ['FLOOR','CEILING','WALL_TILE','SHOWER_TILE','WATERPROOF_FLOOR','WATERPROOF_UPTURN','PLUMBING_CHASE','WET_AREA','DOOR_REVEAL','WINDOW_REVEAL']
              ],
              'COMMON_WASHROOM'=>[
                ['SHOWER_WALL','WC_WALL','BASIN_WALL','PLUMBING_WALL','VENTILATOR_WALL','SHAFT_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['TOILET_DOOR','VENTILATOR','WINDOW','SHAFT_OPENING','CUSTOM_OPENING'],
                ['FLOOR','CEILING','WALL_TILE','SHOWER_TILE','WATERPROOF_FLOOR','WATERPROOF_UPTURN','PLUMBING_CHASE','WET_AREA']
              ],
              'KITCHEN'=>[
                ['COUNTER_WALL','HOB_WALL','SINK_WALL','TALL_UNIT_WALL','REFRIGERATOR_WALL','WINDOW_WALL','UTILITY_DOOR_WALL','SERVICE_SHAFT_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['INTERNAL_DOOR','SERVICE_DOOR','WINDOW','SLIDING_WINDOW','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','KITCHEN_DADO','COUNTERTOP','SKIRTING','DOOR_REVEAL','WINDOW_REVEAL']
              ],
              'CLASSROOM'=>[
                ['TEACHING_WALL','STORAGE_WALL','WINDOW_WALL','EXTERNAL_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['INTERNAL_DOOR','WINDOW','SLIDING_WINDOW','CASEMENT_WINDOW','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','SKIRTING','DOOR_REVEAL','WINDOW_REVEAL']
              ],
              'OPERATION_THEATRE'=>[
                ['EQUIPMENT_WALL','MEDICAL_GAS_WALL','SCRUB_WALL','SERVICE_WALL','GLAZED_WALL','FIRE_RATED_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['AUTOMATIC_DOOR','GLASS_DOOR','FIRE_DOOR','PASS_BOX','SERVICE_OPENING','CUSTOM_OPENING'],
                ['FLOOR','CEILING','STERILE_WALL_ZONE','MEDICAL_GAS_ZONE','VINYL_FLOOR','FALSE_CEILING','DOOR_REVEAL']
              ],
              'ICU'=>[
                ['BED_HEAD_PANEL_WALL','MEDICAL_GAS_WALL','NURSE_CALL_WALL','EQUIPMENT_WALL','GLAZED_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['AUTOMATIC_DOOR','GLASS_DOOR','WINDOW','SERVICE_OPENING','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','MEDICAL_GAS_ZONE','VINYL_FLOOR','FALSE_CEILING']
              ],
              'SHOP'=>[
                ['SHOPFRONT_WALL','DISPLAY_WALL','STORAGE_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['GLASS_DOOR','ROLLING_SHUTTER','SHOPFRONT_OPENING','SERVICE_DOOR','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','SHOPFRONT','FALSE_CEILING']
              ],
              'VAULT'=>[
                ['VAULT_WALL','SECURITY_WALL','FIRE_RATED_WALL','CUSTOM_WALL'],
                ['FIRE_DOOR','SERVICE_DOOR','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','FIREPROOFING']
              ],
              'WAREHOUSE'=>[
                ['LOADING_WALL','EXTERNAL_WALL','FIRE_RATED_WALL','PARTITION_WALL','CUSTOM_WALL'],
                ['ROLLING_SHUTTER','FIRE_DOOR','SERVICE_DOOR','LOUVER','CUSTOM_OPENING'],
                ['FLOOR','CEILING','INTERNAL_WALL','EPOXY_FLOOR','FIREPROOFING']
              ]
            ];

            // Give every selectable space a sensible generic context first.
            foreach($st as $code=>$spaceId) {
                $cfg=$contexts[$code] ?? [$genericWalls,$genericOpen,$genericZones];
                $this->syncMappings($spaceId,$cfg[0],'component',$comp);
                $this->syncMappings($spaceId,$cfg[1],'opening',$op);
                $this->syncMappings($spaceId,$cfg[2],'measurement_zone',$zone);
            }
        });
    }

    private function syncMappings(int $spaceTypeId, array $codes, string $group, $ids): void
    {
        foreach(array_values(array_unique($codes)) as $sort=>$code) {
            if (!isset($ids[$code])) continue;
            $key=[
                'spatial_space_type_id'=>$spaceTypeId,
                'spatial_space_subtype_id'=>null,
                'spatial_component_type_id'=>$group==='component' ? $ids[$code] : null,
                'spatial_opening_type_id'=>$group==='opening' ? $ids[$code] : null,
                'spatial_measurement_zone_id'=>$group==='measurement_zone' ? $ids[$code] : null,
                'context_group'=>$group,
            ];
            DB::table('spatial_space_component_mappings')->updateOrInsert($key,$key+[
                'is_default'=>1,'is_required'=>0,'sort_order'=>($sort+1)*10,'is_active'=>1,
                'created_at'=>now(),'updated_at'=>now()
            ]);
        }
    }
}
