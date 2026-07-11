# Balochistan Single Window System - Architecture Documentation

## Database Schema Overview

### Core Tables

#### `users`
- `id` (bigint, PK)
- `name` (string)
- `email` (string, unique)
- `password` (string)
- `cnic` (string, nullable)
- `role_id` (bigint, FK)
- `department_id` (bigint, FK, nullable)
- `remember_token`, `created_at`, `updated_at`

#### `roles`
- `id` (bigint, PK)
- `name` (string, unique) - applicant, department_staff, department_executive, superadmin
- `description` (text, nullable)
- `created_at`, `updated_at`

#### `departments`
- `id` (bigint, PK)
- `name` (string) - BRA, Labour Department, Excise, BFA, BHC
- `slug` (string, unique)
- `description` (text, nullable)
- `is_active` (boolean)
- `contact_email` (string, nullable)
- `created_at`, `updated_at`

#### `businesses`
- `id` (bigint, PK)
- `user_id` (bigint, FK)
- `legal_name` (string)
- `business_type` (enum) - company, aop, sole_proprietor
- `ntn` (string, nullable, unique)
- `cnic` (string, nullable)
- `business_nature` (text, nullable)
- `employs_workers` (boolean)
- `active_status` (enum) - draft, submitted, active, inactive
- `created_at`, `updated_at`

#### `applications`
- `id` (bigint, PK)
- `business_id` (bigint, FK)
- `global_status` (enum) - draft, pending, approved, rejected, action_required
- `progress_percentage` (integer)
- `submitted_at`, `completed_at` (timestamps, nullable)
- `created_at`, `updated_at`

#### `department_applications`
- `id` (bigint, PK)
- `application_id` (bigint, FK)
- `department_id` (bigint, FK)
- `status` (enum) - pending, under_review, queried, approved, rejected
- `registration_number` (string, nullable) - Pre-existing
- `query_notes` (text, nullable)
- `reviewed_by`, `approved_by` (bigint, FK to users, nullable)
- `certificate_issued_at`, `certificate_path` (nullable)
- `created_at`, `updated_at`

#### `documents`
- `id` (bigint, PK)
- `business_id` (bigint, FK)
- `department_application_id` (bigint, FK, nullable)
- `document_type` (string)
- `file_path` (string) - Protected storage
- `original_filename` (string)
- `file_size` (integer)
- `mime_type` (string)
- `status` (enum) - uploaded, verified, rejected
- `uploaded_by` (bigint, FK)
- `rejected_reason` (text, nullable)
- `created_at`, `updated_at`

#### `bra_registrations` (BRA-specific logic)
- `id` (bigint, PK)
- `department_application_id` (bigint, FK)
- `registration_type` (enum) - withholding_agent, service_provider
- `sales_tax_number` (string, nullable)
- `wwf_applicable` (boolean)
- `wwf_registration_number` (string, nullable)
- `created_at`, `updated_at`

#### `department_user` (Pivot)
- `id` (bigint, PK)
- `user_id` (bigint, FK)
- `department_id` (bigint, FK)
- `role` (enum) - staff, executive
- `created_at`, `updated_at`

#### `action_logs`
- `id` (bigint, PK)
- `user_id` (bigint, FK)
- `loggable_type` (string)
- `loggable_id` (bigint)
- `action` (string)
- `changes` (json, nullable)
- `ip_address` (string, nullable)
- `created_at`

## Model Relationships

```
User
  ├── hasOne: Business
  ├── hasMany: Documents
  ├── hasMany: ActionLogs
  ├── belongsToMany: Department (pivot: department_user)
  └── hasMany: DepartmentApplications (as reviewer/approver)

Business
  ├── belongsTo: User
  ├── hasMany: Applications
  ├── hasMany: Documents
  └── hasMany: DepartmentApplications (through applications)

Application
  ├── belongsTo: Business
  ├── hasMany: DepartmentApplications
  └── hasMany: Documents (through department_applications)

DepartmentApplication
  ├── belongsTo: Application
  ├── belongsTo: Department
  ├── hasMany: Documents
  ├── hasOne: BraRegistration
  ├── belongsTo: User (reviewer)
  └── belongsTo: User (approver)

Department
  ├── hasMany: DepartmentApplications
  ├── belongsToMany: User (pivot: department_user)
  └── hasMany: Documents

Document
  ├── belongsTo: Business
  ├── belongsTo: DepartmentApplication (nullable)
  └── belongsTo: User (uploader)
```

## Security & Data Isolation

### Authorization Policies

- **Applicant:** View/edit only their own business & applications
- **Department Staff:** View queries & documents for their department only
- **Department Executive:** Approve/reject & issue certificates for their department only
- **Superadmin:** Global access with comprehensive audit logging

### Query Scopes

- Policy-based authorization per model
- Prevent cross-department data access via query scopes
- File access via signed Laravel URLs with authorization checks

## Workflow

1. Applicant submits form with department selection
2. Application created, department_applications instantiated per selected department
3. Department staff reviews, may query for additional information
4. Applicant re-uploads corrected documents
5. Department executive approves, certificate generated
6. Application marked complete, global status updated

## Implementation Roadmap

- [ ] Create Laravel 11 project structure
- [ ] Generate database migrations
- [ ] Create all Models with relationships
- [ ] Build Filament Admin Panel (Superadmin)
- [ ] Build Filament Resources (Departments)
- [ ] Implement Livewire multi-step form
- [ ] Excel import/export service
- [ ] Document management & file uploads
- [ ] Email notifications
- [ ] PDF certificate generation
- [ ] Queue jobs for background processing
- [ ] API endpoints
- [ ] Tests & documentation
