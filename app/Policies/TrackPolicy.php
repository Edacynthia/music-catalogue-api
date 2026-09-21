<?php

namespace App\Policies;

use App\Models\Track;
use App\Models\User;

class TrackPolicy
{
    /**
     * Determine whether the user can view the track.
     */
    public function view(User $user, Track $track): bool
    {
        return $user->id === $track->user_id;
    }

    /**
     * Determine whether the user can update the track.
     */
    public function update(User $user, Track $track): bool
    {
        return $user->id === $track->user_id;
    }

    /**
     * Determine whether the user can delete the track.
     */
    public function delete(User $user, Track $track): bool
    {
        return $user->id === $track->user_id;
    }
}