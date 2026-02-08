<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'request_date',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'status',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'requested_clock_in' => 'datetime',
            'requested_clock_out' => 'datetime',
            'status' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function correctionRests(): HasMany
    {
        return $this->hasMany(StampCorrectionRequestRest::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
