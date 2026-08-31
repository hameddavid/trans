<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $table = 'signatories';

    protected $fillable = [
        'admin_id', 'name', 'title', 'for_title', 'document_type',
        'staff_email', 'signature_path', 'status', 'approved_by', 'approved_at', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public static function getActive(string $documentType): ?self
    {
        return static::where('document_type', $documentType)
            ->where('status', 'approved')
            ->where('is_active', true)
            ->first();
    }

    public function activate(): void
    {
        static::where('document_type', $this->document_type)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }
}
