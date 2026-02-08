<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'rest_start',
        'rest_end',
    ];

    protected function casts(): array
    {
        return [
            'rest_start' => 'datetime',
            'rest_end' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->rest_end) {
            return null;
        }
        return $this->rest_start->diffInMinutes($this->rest_end);
    }
}
