<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Mail\NewContactQueryMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Store a new contact / query message from guest or authenticated user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:150',
            'category' => 'nullable|string|max:50',
            'message' => 'required|string|min:5|max:3000',
        ]);

        $userId = auth('sanctum')->id();

        $contact = Contact::create([
            'user_id' => $userId,
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'subject' => trim($validated['subject']),
            'category' => $validated['category'] ?? 'general',
            'message' => trim($validated['message']),
            'status' => 'pending',
        ]);

        // Send instant notification email to administrator
        $adminEmail = env('ADMIN_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@futureself.in'));

        try {
            Mail::to($adminEmail)->queue(new NewContactQueryMail($contact));
        } catch (\Throwable $e) {
            Log::error('Failed to queue contact notification email: ' . $e->getMessage());
        }

        return ResponseHelper::send(200, 'Thank you! Your query has been received. Our team will get back to you shortly.', [
            'contact' => $contact,
        ]);
    }
}
