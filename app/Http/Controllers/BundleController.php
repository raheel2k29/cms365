<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function index()
    {
        return view('bundles.index');
    }
}
