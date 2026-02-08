<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStampCorrectionRequestController extends Controller
{
    public function list(Request $request)
    {
        $tab = $request->input('tab', 'pending');
        if (!in_array($tab, ['pending', 'approved'])) {
            $tab = 'pending';
        }

        $status = $tab === 'approved'
            ? StampCorrectionRequest::STATUS_APPROVED
            : StampCorrectionRequest::STATUS_PENDING;

        $corrections = StampCorrectionRequest::where('status', $status)
            ->with(['user', 'attendance', 'correctionRests'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.stamp_correction.list', compact('corrections', 'tab'));
    }

    public function show($id)
    {
        $correction = StampCorrectionRequest::with(['user', 'attendance.rests', 'correctionRests'])
            ->findOrFail($id);

        return view('admin.stamp_correction.show', compact('correction'));
    }

    public function approve($id)
    {
        $correction = StampCorrectionRequest::with('correctionRests')
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->findOrFail($id);

        DB::transaction(function () use ($correction) {
            $attendance = Attendance::findOrFail($correction->attendance_id);

            $attendance->update([
                'clock_in' => $correction->requested_clock_in,
                'clock_out' => $correction->requested_clock_out,
            ]);

            if ($correction->correctionRests->isNotEmpty()) {
                $attendance->rests()->delete();
                foreach ($correction->correctionRests as $rest) {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'rest_start' => $rest->rest_start,
                        'rest_end' => $rest->rest_end,
                    ]);
                }
            }

            $correction->update([
                'status' => StampCorrectionRequest::STATUS_APPROVED,
                'approved_by' => auth()->id(),
            ]);
        });

        return redirect()->route('admin.stamp_correction.list')
            ->with('success', '修正申請を承認しました');
    }
}
