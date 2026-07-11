<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BraRegistration extends Model
{
    protected $table = 'bra_registrations';
    protected $fillable = ['department_application_id', 'registration_type', 'sales_tax_number', 'wwf_applicable', 'wwf_registration_number'];
    protected $casts = ['wwf_applicable' => 'boolean'];

    public function departmentApplication(): BelongsTo
    {
        return $this->belongsTo(DepartmentApplication::class);
    }

    public function scopeWithholdingAgents($query)
    {
        return $query->where('registration_type', 'withholding_agent');
    }

    public function scopeServiceProviders($query)
    {
        return $query->where('registration_type', 'service_provider');
    }

    public function scopeWwfApplicable($query)
    {
        return $query->where('wwf_applicable', true);
    }

    public function isWithholdingAgent(): bool
    {
        return $this->registration_type === 'withholding_agent';
    }

    public function isServiceProvider(): bool
    {
        return $this->registration_type === 'service_provider';
    }
}
