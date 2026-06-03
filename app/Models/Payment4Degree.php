<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment4Degree extends Model
{
    protected $table = 'degree_verification_payment_transaction';

    protected $fillable = [
        'matric_number', 'email', 'names', 'amount', 'rrr',
        'trans_ref', 'destination', 'gateway', 'institution_email',
        'institution_name', 'status_code', 'status_msg', 'time_stamp',
        'p_gateway_transaction_id',
    ];

    public function isPending(): bool
    {
        return $this->status_code === '025' && $this->status_msg === 'pending';
    }

    public function isSuccessful(): bool
    {
        return $this->status_code === '00' && $this->status_msg === 'success';
    }
}
