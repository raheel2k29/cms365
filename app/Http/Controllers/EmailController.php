<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        $emails = \App\Models\Email::latest('sent_at')->paginate(20);
        return view('emails.index', compact('emails'));
    }

    public function show(\App\Models\Email $email)
    {
        return view('emails.show', compact('email'));
    }
}
