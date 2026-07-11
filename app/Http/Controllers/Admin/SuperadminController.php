<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Application;
use App\Services\ExcelExportService;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class SuperadminController extends Controller
{
    /**
     * Show superadmin dashboard
     */
    public function dashboard(): View
    {
        $totalApplications = Application::count();
        $pendingApplications = Application::where('global_status', 'pending')->count();
        $approvedApplications = Application::where('global_status', 'approved')->count();
        $rejectedApplications = Application::where('global_status', 'rejected')->count();
        $totalUsers = User::count();
        $totalDepartments = Department::count();

        return view('admin.dashboard', [
            'totalApplications' => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'approvedApplications' => $approvedApplications,
            'rejectedApplications' => $rejectedApplications,
            'totalUsers' => $totalUsers,
            'totalDepartments' => $totalDepartments,
        ]);
    }

    /**
     * Export all registrations globally
     */
    public function exportGlobalRegistrations(ExcelExportService $excelService): Response
    {
        $export = $excelService->exportGlobalRegistrations();

        return Excel::download(
            new class($export['data'], $export['headings']) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function __construct(private $data, private $headings) {}

                public function collection()
                {
                    return $this->data;
                }

                public function headings(): array
                {
                    return $this->headings;
                }
            },
            $export['fileName']
        );
    }

    /**
     * Export import template
     */
    public function exportImportTemplate(ExcelExportService $excelService): Response
    {
        $export = $excelService->exportImportTemplate();

        return Excel::download(
            new class($export['data'], $export['headings']) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                public function __construct(private $data, private $headings) {}

                public function collection()
                {
                    return $this->data;
                }

                public function headings(): array
                {
                    return $this->headings;
                }
            },
            $export['fileName']
        );
    }

    /**
     * Show users management
     */
    public function manageUsers(): View
    {
        $users = User::with('role', 'department')->paginate(20);
        $roles = Role::all();
        $departments = Department::all();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'departments' => $departments,
        ]);
    }

    /**
     * Show departments management
     */
    public function manageDepartments(): View
    {
        $departments = Department::withCount('departmentApplications', 'users')
            ->paginate(20);

        return view('admin.departments.index', [
            'departments' => $departments,
        ]);
    }

    /**
     * Show system logs
     */
    public function logs(): View
    {
        $logs = \App\Models\ActionLog::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.logs.index', [
            'logs' => $logs,
        ]);
    }
}
