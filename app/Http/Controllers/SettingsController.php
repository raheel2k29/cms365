<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return 'Settings page is working - Hotfix deployed!';
    }

    public function update(Request $request)
    {
        return redirect()->back();
    }
}
