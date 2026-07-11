<?php

namespace App\Services;

use App\Models\DepartmentApplication;
use App\Models\User;

class DepartmentApplicationService
{
    /**
     * Query an application for more information
     */
    public function queryApplication(
        DepartmentApplication $departmentApplication,
        string $queryNotes,
        User $reviewedBy
    ): void {
        $departmentApplication->update([
            'status' => 'queried',
            'query_notes' => $queryNotes,
            'reviewed_by' => $reviewedBy->id,
        ]);

        // Update global status to action_required
        $application = $departmentApplication->application;
        $application->update(['global_status' => 'action_required']);
    }

    /**
     * Approve a department application
     */
    public function approveApplication(
        DepartmentApplication $departmentApplication,
        User $approvedBy
    ): void {
        $departmentApplication->update([
            'status' => 'approved',
            'approved_by' => $approvedBy->id,
            'certificate_issued_at' => now(),
        ]);

        // Update application progress
        $applicationService = new ApplicationService();
        $applicationService->updateApplicationProgress($departmentApplication->application);
    }

    /**
     * Reject a department application
     */
    public function rejectApplication(
        DepartmentApplication $departmentApplication,
        User $rejectedBy,
        string $reason
    ): void {
        $departmentApplication->update([
            'status' => 'rejected',
            'query_notes' => $reason,
            'reviewed_by' => $rejectedBy->id,
        ]);

        // Update global status
        $departmentApplication->application->update(['global_status' => 'rejected']);
    }

    /**
     * Get all pending applications for a department
     */
    public function getPendingForDepartment(int $departmentId)
    {
        return DepartmentApplication::where('department_id', $departmentId)
            ->where('status', 'pending')
            ->with(['application.business', 'application.departmentApplications', 'department'])
            ->get();
    }

    /**
     * Get queried applications for an applicant
     */
    public function getQueriedApplicationsForBusiness(int $businessId)
    {
        return DepartmentApplication::whereHas('application', function ($query) use ($businessId) {
            $query->where('business_id', $businessId);
        })
        ->where('status', 'queried')
        ->with(['department', 'application.business', 'documents'])
        ->get();
    }

    /**
     * Generate digital certificate path
     */
    public function generateCertificatePath(DepartmentApplication $departmentApplication): string
    {
        $certificateDir = "certificates/{$departmentApplication->department->slug}";
        $certificateName = "{$departmentApplication->id}_" . time() . ".pdf";
        
        return $certificateDir . "/" . $certificateName;
    }
}
