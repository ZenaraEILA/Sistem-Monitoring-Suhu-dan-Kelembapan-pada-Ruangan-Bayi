<?php

namespace App\Policies;

use App\Models\IncidentMarker;
use App\Models\User;

class IncidentMarkerPolicy
{
    /**
     * Determine if user can delete the incident marker
     */
    public function delete(User $user, IncidentMarker $marker): bool
    {
        return $user->id === $marker->created_by || $user->isAdmin();
    }
}
