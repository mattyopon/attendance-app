<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_WORKING = 1;
    const STATUS_ON_BREAK = 2;
    const STATUS_LEFT = 3;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'status' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rests(): HasMany
    {
        return $this->hasMany(Rest::class);
    }

    public function stampCorrectionRequests(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function getTotalRestMinutesAttribute(): int
    {
        return $this->rests
            ->filter(fn ($rest) => $rest->rest_end !== null)
            ->sum(function ($rest) {
                return $rest->rest_start->diffInMinutes($rest->rest_end);
            });
    }

    public function getFormattedTotalRestAttribute(): string
    {
        $minutes = $this->total_rest_minutes;
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    public function getTotalWorkMinutesAttribute(): ?int
    {
        if (!$this->clock_out) {
            return null;
        }
        $totalMinutes = $this->clock_in->diffInMinutes($this->clock_out);
        return $totalMinutes - $this->total_rest_minutes;
    }

    public function getFormattedTotalWorkAttribute(): string
    {
        $minutes = $this->total_work_minutes;
        if ($minutes === null) {
            return '-';
        }
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return sprintf('%d:%02d', $hours, $mins);
    }

    public function hasPendingCorrection(): bool
    {
        return $this->stampCorrectionRequests()
            ->where('status', 0)
            ->exists();
    }
}
