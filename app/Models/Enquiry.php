<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A public lead captured from the marketing website's "Request a demo /
 * Contact us" form (via the API's public POST /enquiry). Worked by the
 * super-admin in the Enquiries module.
 */
class Enquiry extends Model
{
    protected $table = 'enquiries';

    protected $fillable = [
        'name', 'email', 'phone', 'organization', 'city', 'student_count',
        'subject', 'message', 'source', 'status', 'admin_notes', 'handled_by',
        'contacted_at', 'ip_address',
    ];

    protected $casts = [
        'student_count' => 'integer',
        'contacted_at'  => 'datetime',
    ];

    public const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'closed'];

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('organization', 'like', "%{$term}%")
              ->orWhere('city', 'like', "%{$term}%");
        });
    }
}
