<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StampCorrectionRequestRest extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_correction_request_id',
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

    public function stampCorrectionRequest(): BelongsTo
    {
        return $this->belongsTo(StampCorrectionRequest::class);
    }
}
