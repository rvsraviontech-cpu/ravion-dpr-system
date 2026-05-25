<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::latest()->get();

        return view(
            'vendors.index',
            compact('vendors')
        );
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $request->validate([

            'vendor_name' => 'required'

        ]);

        Vendor::create($request->all());

        return redirect('/vendors')
            ->with(
                'success',
                'Vendor created successfully.'
            );
    }
}