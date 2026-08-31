<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'applicant_id', 'matric_number', 'subject', 'message', 'attachment',
        'status', 'admin_response', 'responded_by', 'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}
