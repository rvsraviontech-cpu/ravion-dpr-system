<?php

namespace App\Http\Controllers;

use App\Imports\ActivityMappingsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActivityMappingController extends Controller
{
    public function index()
    {
        return view('activity-mappings.index');
    }

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
    ]);

    try {
        $import = new ActivityMappingsImport;

        Excel::import(
            $import,
            $request->file('file')
        );

        return back()->with(
            'success',
            $import->importedCount . ' activity mappings imported successfully.'
        );

    } catch (\Throwable $e) {

        return back()->withErrors([
            'import_error' => $e->getMessage()
        ]);
    }
}
}
