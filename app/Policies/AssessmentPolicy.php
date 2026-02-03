<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssessmentPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Assessment $assessment): Response
    {
        // Admin and Auditors can view anything
        if (in_array(strtolower($user->role), ['admin', 'auditor'])) {
             return Response::allow();
        }

        // Owners can view their own assessments
        if ($user->id === $assessment->user_id) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this assessment.');
    }
}
