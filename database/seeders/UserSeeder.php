<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create superadmin
        $superadminRole = Role::where('name', 'superadmin')->first();
        User::firstOrCreate(
            ['email' => 'admin@sws.local'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role_id' => $superadminRole->id,
            ]
        );

        // Create department staff/executives for each department
        $departments = Department::all();
        $staffRole = Role::where('name', 'department_staff')->first();
        $executiveRole = Role::where('name', 'department_executive')->first();

        foreach ($departments as $department) {
            // Create staff member
            $staffUser = User::firstOrCreate(
                ['email' => "staff.{$department->slug}@sws.local"],
                [
                    'name' => "{$department->name} - Staff",
                    'password' => Hash::make('password'),
                    'role_id' => $staffRole->id,
                    'department_id' => $department->id,
                ]
            );

            // Create executive
            $executiveUser = User::firstOrCreate(
                ['email' => "executive.{$department->slug}@sws.local"],
                [
                    'name' => "{$department->name} - Executive",
                    'password' => Hash::make('password'),
                    'role_id' => $executiveRole->id,
                    'department_id' => $department->id,
                ]
            );

            // Attach to pivot table if not already attached
            if (!$staffUser->departments()->where('department_id', $department->id)->exists()) {
                $staffUser->departments()->attach($department->id, ['role' => 'staff']);
            }

            if (!$executiveUser->departments()->where('department_id', $department->id)->exists()) {
                $executiveUser->departments()->attach($department->id, ['role' => 'executive']);
            }
        }

        // Create sample applicant
        $applicantRole = Role::where('name', 'applicant')->first();
        User::firstOrCreate(
            ['email' => 'applicant@example.com'],
            [
                'name' => 'Sample Applicant',
                'password' => Hash::make('password'),
                'cnic' => '12345-1234567-1',
                'role_id' => $applicantRole->id,
            ]
        );
    }
}
