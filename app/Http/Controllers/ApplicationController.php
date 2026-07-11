<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Application;
use App\Http\Requests\StoreBusinessRequest;
use App\Services\ApplicationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationService $applicationService
    ) {}

    /**
     * Show business registration form
     */
    public function create(): View
    {
        return view('applications.create', [
            'businessTypes' => ['company' => 'Company (SECP)', 'aop' => 'Association of Persons', 'sole_proprietor' => 'Sole Proprietorship'],
            'departments' => \App\Models\Department::active()->get(),
        ]);
    }

    /**
     * Store business and create application
     */
    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $business = Business::create([
            'user_id' => auth()->id(),
            'legal_name' => $request->legal_name,
            'business_type' => $request->business_type,
            'ntn' => $request->ntn,
            'cnic' => $request->cnic,
            'business_nature' => $request->business_nature,
            'employs_workers' => $request->boolean('employs_workers'),
            'active_status' => 'draft',
        ]);

        $application = $this->applicationService->createApplication(
            $business,
            $request->departments
        );

        return redirect()->route('applications.edit', $application)
            ->with('success', 'Business registration started successfully!');
    }

    /**
     * Show application edit form
     */
    public function edit(Application $application): View
    {
        $this->authorize('view', $application);

        return view('applications.edit', [
            'application' => $application,
            'business' => $application->business,
            'departmentApplications' => $application->departmentApplications()->with('department', 'documents')->get(),
        ]);
    }

    /**
     * Submit application for processing
     */
    public function submit(Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $this->applicationService->submitApplication($application);

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Application submitted successfully for processing!');
    }

    /**
     * Show applicant dashboard
     */
    public function dashboard(): View
    {
        $applications = auth()->user()->business?->applications()
            ->with('departmentApplications.department')
            ->latest()
            ->paginate(10);

        return view('applicant.dashboard', [
            'applications' => $applications,
        ]);
    }
}
