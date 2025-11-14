<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Show the user's profile form.
     */
    public function show(Request $request)
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Check if this is a payment preference update
        if ($request->has('payment_preference')) {
            return $this->updatePaymentPreferences($request);
        }

        // Check if this is a delivery address update (has delivery-specific fields)
        if ($request->has('delivery_time_preference') || $request->has('address')) {
            return $this->updateDeliveryAddress($request);
        }

        // Profile information update
        $validated = $request->validate([
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($user) {
                    // Only check uniqueness if email is not null
                    if ($value !== null) {
                        $exists = \App\Models\User::where('email', $value)
                            ->where('id', '!=', $user->id)
                            ->whereNotNull('email')
                            ->exists();
                        if ($exists) {
                            $fail('The email has already been taken.');
                        }
                    }
                },
            ],
        ]);

        $oldEmail = $user->email;
        $newEmail = $validated['email'] ?? null;

        // If email is being changed
        if ($oldEmail !== $newEmail) {
            // Mark new email as unverified if email is provided
            $user->email_verified_at = $newEmail ? null : null;

            // Update email immediately - no restrictions
            $user->fill($validated);
            $user->save();

            if ($newEmail) {
                // Automatically send verification email for new/changed email
                $user->sendEmailVerificationNotification();
                return redirect()->route('profile.show')->with('success', "Email address updated successfully. A verification email has been sent to {$newEmail}.");
            } else {
                return redirect()->route('profile.show')->with('success', 'Email address removed successfully.');
            }
        }

        // If only username is being updated
        $user->fill($validated);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update the user's delivery address information.
     */
    public function updateDeliveryAddress(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'fullname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:20'],
            'delivery_instructions' => ['nullable', 'string', 'max:1000'],
            'delivery_time_preference' => ['nullable', 'in:anytime,morning,afternoon,weekend'],
        ]);

        $user->fill($validated);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Delivery address updated successfully.');
    }

    /**
     * Update the user's payment preferences.
     */
    public function updatePaymentPreferences(Request $request)
    {
        $user = $request->user();

        // Base validation rules
        $rules = [
            'payment_preference' => ['nullable', 'in:Gcash,Maya,Cash,Others'],
        ];

        // Conditional validation based on payment method
        $paymentMethod = $request->input('payment_preference');

        if ($paymentMethod === 'Gcash') {
            $rules['gcash_number'] = ['required', 'string', 'regex:/^09[0-9]{9}$/', 'size:11'];
        } elseif ($paymentMethod === 'Maya') {
            $rules['maya_number'] = ['required', 'string', 'regex:/^09[0-9]{9}$/', 'size:11'];
        } elseif ($paymentMethod === 'Cash') {
            $rules['pickup_location'] = ['nullable', 'string', 'max:255'];
        } elseif ($paymentMethod === 'Others') {
            $rules['other_payment_method'] = ['required', 'string', 'max:255'];
            $rules['other_payment_details'] = ['required', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);

        // Update payment preference (indicates current preferred method)
        $user->payment_preference = $validated['payment_preference'] ?? null;

        // Update only the specific fields being submitted (retain other saved methods)
        if ($paymentMethod === 'Gcash' && isset($validated['gcash_number'])) {
            $user->gcash_number = $validated['gcash_number'];
        } elseif ($paymentMethod === 'Maya' && isset($validated['maya_number'])) {
            $user->maya_number = $validated['maya_number'];
        } elseif ($paymentMethod === 'Cash') {
            // Auto-fill pickup location with admin's delivery address if not provided
            if (empty($validated['pickup_location'])) {
                // Get admin's delivery address - matches office_pickup in orders
                $adminUser = \App\Models\User::role('admin')->first();
                if ($adminUser) {
                    $addressParts = array_filter([
                        $adminUser->address,
                        $adminUser->address_2,
                        $adminUser->city,
                        $adminUser->state,
                        $adminUser->zip,
                    ]);
                    $user->pickup_location = !empty($addressParts) ? implode(', ', $addressParts) : 'Main Office';
                } else {
                    $user->pickup_location = 'Main Office';
                }
            } else {
                $user->pickup_location = $validated['pickup_location'];
            }
        } elseif ($paymentMethod === 'Others') {
            $user->other_payment_method = $validated['other_payment_method'] ?? null;
            $user->other_payment_details = $validated['other_payment_details'] ?? null;
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Payment preferences updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')->with('success', 'Password updated successfully.');
    }
}
