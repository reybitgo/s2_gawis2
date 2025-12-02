<?php

namespace App\Observers;

use App\Models\User;
use App\Services\NotificationService;
use App\Services\RankAdvancementService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Send new user registration notification
        NotificationService::notifyNewUserRegistration($user);
        
        // If user has a sponsor and a rank, track sponsorship and check for advancement
        if ($user->sponsor_id && $user->current_rank) {
            $sponsor = User::find($user->sponsor_id);
            
            if ($sponsor && $sponsor->current_rank) {
                $rankService = app(RankAdvancementService::class);
                $rankService->trackSponsorship($sponsor, $user);
            }
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check for suspicious activity patterns
        if ($user->wasChanged('email')) {
            NotificationService::notifySuspiciousActivity(
                $user,
                'Email address changed',
                [
                    'old_email' => $user->getOriginal('email'),
                    'new_email' => $user->email,
                    'ip_address' => request()->ip() ?? 'Unknown'
                ]
            );
        }

        if ($user->wasChanged('name')) {
            NotificationService::notifySuspiciousActivity(
                $user,
                'Name changed',
                [
                    'old_name' => $user->getOriginal('name'),
                    'new_name' => $user->name,
                    'ip_address' => request()->ip() ?? 'Unknown'
                ]
            );
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Notify of user account deletion
        NotificationService::notifySuspiciousActivity(
            $user,
            'User account deleted',
            [
                'deleted_by' => auth()->user()?->name ?? 'System',
                'ip_address' => request()->ip() ?? 'Unknown'
            ]
        );
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
