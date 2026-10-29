<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class ImportWorkDoneBibleCore extends Command
{
    protected $signature = 'ravion:import-workdone-bible-core {file : Absolute path to V5 workbook} {--apply : Commit import after validation}';
    protected $description = 'Validate or import Work Done Bible V5 stage, assembly, work item and operation masters';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (!is_file($file)) { $file = base_path($file); }
        if (!is_file($file)) { $this->error('Workbook not found.'); return self::FAILURE; }
        try {
            $book = IOFactory::load($file);
            $stageSheet = $book->getSheetByName('01_STAGE_MASTER');
            $operationSheet = $book->getSheetByName('02_WORKDONE_MICRO_MASTER');
            if (!$stageSheet || !$operationSheet) throw new RuntimeException('Required V5 sheets are missing.');
            $stages = $this->records($stageSheet);
            $operations = $this->records($operationSheet);
            $stageCodes = [];
            foreach ($stages as $r) {
                $code = trim((string)($r['Stage_Code'] ?? ''));
                if ($code === '' || isset($stageCodes[$code])) throw new RuntimeException('Missing/duplicate Stage_Code at stage row '.(count($stageCodes)+2).': '.($code === '' ? '[blank]' : $code));
                $stageCodes[$code] = $r;
            }
            $codes = [];
            foreach ($operations as $r) {
                $code = trim((string)($r['WD_Code'] ?? ''));
                $stage = trim((string)($r['Stage_Code'] ?? ''));
                if ($code === '' || isset($codes[$code])) throw new RuntimeException('Missing/duplicate WD_Code: '.$code);
                if (!isset($stageCodes[$stage])) throw new RuntimeException('Unknown stage '.$stage.' for '.$code);
                if (trim((string)($r['Micro_Operation'] ?? '')) === '') throw new RuntimeException('Missing operation description for '.$code);
                $codes[$code] = true;
            }
            if (count($stages) !== 53 || count($operations) !== 4223) throw new RuntimeException('Unexpected Bible counts: '.count($stages).' stages / '.count($operations).' operations. Expected 53 / 4223.');
            $this->info('Validated: 53 stages, 4223 unique WD codes.');
            if (!$this->option('apply')) { $this->comment('Dry run only. Use --apply to import.'); return self::SUCCESS; }
            DB::transaction(function () use ($stages, $operations) {
                foreach ($stages as $r) {
                    $code = trim((string)$r['Stage_Code']);
                    DB::table('work_done_bible_stages')->insertOrIgnore(['stage_code'=>$code, 'stage_name'=>(string)($r['Stage_Name'] ?? $code), 'created_at'=>now(), 'updated_at'=>now()]);
                    DB::table('work_done_bible_stages')->where('stage_code',$code)->update([
                        'stage_name'=>(string)($r['Stage_Name'] ?? $code), 'source_row'=>json_encode($r, JSON_UNESCAPED_UNICODE), 'updated_at'=>now()
                    ]);
                }
                $stageIds = DB::table('work_done_bible_stages')->pluck('id','stage_code')->all();
                $assemblyIds = []; $itemIds = [];
                foreach ($operations as $r) {
                    $assembly = trim((string)($r['Assembly'] ?? ''));
                    $item = trim((string)($r['Work_Item'] ?? ''));
                    if ($assembly !== '' && !isset($assemblyIds[$assembly])) {
                        DB::table('work_done_bible_assemblies')->insertOrIgnore(['assembly_name'=>$assembly,'created_at'=>now(),'updated_at'=>now()]);
                        $assemblyIds[$assembly] = DB::table('work_done_bible_assemblies')->where('assembly_name',$assembly)->value('id');
                    }
                    if ($item !== '' && !isset($itemIds[$item])) {
                        DB::table('work_done_bible_work_items')->insertOrIgnore(['work_item_name'=>$item,'created_at'=>now(),'updated_at'=>now()]);
                        $itemIds[$item] = DB::table('work_done_bible_work_items')->where('work_item_name',$item)->value('id');
                    }
                    $values = [
                        'stage_id'=>$stageIds[trim((string)$r['Stage_Code'])],
                        'assembly_id'=>$assemblyIds[$assembly] ?? null,
                        'work_item_id'=>$itemIds[$item] ?? null,
                        'trade'=>$this->nullable($r['Trade'] ?? null),
                        'element'=>$this->nullable($r['Element'] ?? null),
                        'micro_operation'=>(string)$r['Micro_Operation'],
                        'record_type'=>$this->nullable($r['Record_Type'] ?? null),
                        'uom'=>$this->nullable($r['UOM'] ?? null),
                        'measurement_method'=>$this->nullable($r['Measurement_Method'] ?? null),
                        'predecessor'=>$this->nullable($r['Predecessor'] ?? null),
                        'qa_gate'=>$this->nullable($r['QA_Gate'] ?? null),
                        'source_row'=>json_encode($r, JSON_UNESCAPED_UNICODE), 'updated_at'=>now(),
                    ];
                    DB::table('work_done_bible_operations')->updateOrInsert(['wd_code'=>trim((string)$r['WD_Code'])], $values);
                }
            });
            $this->info('Core Bible masters imported. Existing Work Done records were not modified.');
            return self::SUCCESS;
        } catch (\Throwable $e) { $this->error($e->getMessage()); return self::FAILURE; }
    }

    private function nullable($v): ?string { $s = trim((string)$v); return $s === '' ? null : $s; }

    private function records($sheet): array
    {
        $rows = $sheet->toArray(null, true, true, false);
        $headers = array_map(function ($h) {
            $name = trim((string) $h);
            // Bible V5 stage master uses 'Stage Code' and 'Stage Name',
            // while the micro master uses 'Stage_Code' and 'Stage_Name'.
            return str_replace(' ', '_', $name);
        }, array_shift($rows));
        $result = [];
        foreach ($rows as $row) {
            if (!array_filter($row, fn($v)=>$v !== null && trim((string)$v) !== '')) continue;
            $result[] = array_combine($headers, array_slice(array_pad($row,count($headers),null),0,count($headers)));
        }
        return $result;
    }
}
