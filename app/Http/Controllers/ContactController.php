<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'nullable|max:100',
            'message' => 'required',
        ]);

        Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        return redirect()->route('landing')->with('contact_success', 'Salamat, ' . $validated['name'] . '!');
    }
}
