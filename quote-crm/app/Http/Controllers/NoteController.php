<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function store(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $note = new Note([
            'body' => $validated['body'],
            'is_pinned' => false,
            'user_id' => auth()->id(),
        ]);

        $quote->notes()->save($note);

        return redirect()->route('quotes.show', $quote)->with('success', 'Note added successfully.');
    }
}
