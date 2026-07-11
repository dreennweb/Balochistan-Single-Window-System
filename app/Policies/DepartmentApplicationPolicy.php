<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DepartmentApplication;

class DepartmentApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DepartmentApplication $departmentApplication): bool
    {
        // Applicants can view applications for their business
        if ($user->isApplicant()) {
            return $user->id === $departmentApplication->application->business->user_id;
        }

        // Department staff/executives can view applications for their department only
        if ($user->isDepartmentStaff() || $user->isDepartmentExecutive()) {
            return $departmentApplication->department_id === $user->department_id;
        }

        // Superadmin can view any application
        return $user->isSuperadmin();
    }

    public function review(User $user, DepartmentApplication $departmentApplication): bool
    {
        if ($user->isDepartmentStaff()) {
            return $departmentApplication->department_id === $user->department_id 
                && $departmentApplication->canBeQueried();
        }

        return $user->isSuperadmin();
    }

    public function approve(User $user, DepartmentApplication $departmentApplication): bool
    {
        if ($user->isDepartmentExecutive()) {
            return $departmentApplication->department_id === $user->department_id 
                && $departmentApplication->canBeApproved();
        }

        return $user->isSuperadmin();
    }

    public function reject(User $user, DepartmentApplication $departmentApplication): bool
    {
        if ($user->isDepartmentExecutive()) {
            return $departmentApplication->department_id === $user->department_id;
        }

        return $user->isSuperadmin();
    }
}
