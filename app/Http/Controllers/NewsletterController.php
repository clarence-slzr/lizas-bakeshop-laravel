<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        // Validate email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletters,email',
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already subscribed.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('newsletter_error', 'Subscription failed. Please check your email.');
        }

        // Save to database
        Newsletter::create([
            'email' => $request->email,
        ]);

        return redirect()->back()->with('newsletter_success', 'Thank you for subscribing! We\'ll keep you updated with our latest products and promos.');
    }
}