<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        try {
            // Send email to admin using the ContactFormMail mailable
            Mail::to('support@gawisherbal.com')
                ->send(new ContactFormMail($validated));

            Log::info('Contact form submitted successfully', [
                'name' => $validated['fname'] . ' ' . $validated['lname'],
                'email' => $validated['email']
            ]);

            return back()->with('success', 'Thank you for your message! We will get back to you soon.');
        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Sorry, we were unable to send your message. Please try again later or email us directly at support@gawisherbal.com')
                ->withInput();
        }
    }
}
