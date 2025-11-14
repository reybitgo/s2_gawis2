<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ReferralClick;

class ReferralController extends Controller
{
    /**
     * Display the referral dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $referralLink = route('register', ['ref' => $user->referral_code]);

        // Get referral statistics
        $totalClicks = ReferralClick::where('user_id', $user->id)->count();
        $totalRegistrations = ReferralClick::where('user_id', $user->id)
                                          ->where('registered', true)
                                          ->count();
        $directReferrals = $user->referrals()->count();

        return view('referral.index', compact(
            'user',
            'referralLink',
            'totalClicks',
            'totalRegistrations',
            'directReferrals'
        ));
    }

    /**
     * Track referral click (called when someone clicks a referral link)
     */
    public function trackClick(Request $request)
    {
        $refCode = $request->query('ref');

        if ($refCode) {
            $user = User::where('referral_code', $refCode)->first();

            if ($user) {
                ReferralClick::create([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                // Store in session for registration form pre-fill
                session(['referral_code' => $refCode]);
            }
        }

        return redirect()->route('register');
    }
}
