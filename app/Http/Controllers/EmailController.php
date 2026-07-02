<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        return view('emails.index');
    }

    public function show($email)
    {
        return view('emails.index');
    }
}
