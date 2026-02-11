<?php

namespace App\Policies;

use App\Models\DoctorNote;
use App\Models\User;

class DoctorNotePolicy
{
    /**
     * Determine if user can update the doctor note
     */
    public function update(User $user, DoctorNote $note): bool
    {
        return $user->id === $note->created_by || $user->isAdmin();
    }

    /**
     * Determine if user can delete the doctor note
     */
    public function delete(User $user, DoctorNote $note): bool
    {
        return $user->id === $note->created_by || $user->isAdmin();
    }
}
