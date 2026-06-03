<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment_transaction';

    protected $fillable = [
        'matric_number', 'email', 'names', 'amount', 'rrr',
        'trans_ref', 'destination', 'gateway', 'user_id',
        'status_code', 'status_msg', 'time_stamp',
        'p_gateway_transaction_id', 'app_id',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'user_id');
    }

    public function officialApplication()
    {
        return $this->belongsTo(OfficialApplication::class, 'app_id', 'application_id');
    }

    public function isPending(): bool
    {
        return $this->status_code === '025' && $this->status_msg === 'pending';
    }

    public function isSuccessful(): bool
    {
        return $this->status_code === '00' && $this->status_msg === 'success';
    }
}
