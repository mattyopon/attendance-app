<?php

namespace App\Http\Controllers;

use App\Http\Requests\StampCorrectionFormRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestRest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function store(StampCorrectionFormRequest $request, $id)
    {
        $user = auth()->user();
        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($attendance->hasPendingCorrection()) {
            return redirect()->back()->with('error', '既に承認待ちの修正申請があります');
        }

        $date = $attendance->date->toDateString();
        $requestedClockIn = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->input('clock_in'));
        $requestedClockOut = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->input('clock_out'));

        DB::transaction(function () use ($user, $attendance, $request, $date, $requestedClockIn, $requestedClockOut) {
            $correction = StampCorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'request_date' => $attendance->date,
                'requested_clock_in' => $requestedClockIn,
                'requested_clock_out' => $requestedClockOut,
                'reason' => $request->input('reason'),
                'status' => StampCorrectionRequest::STATUS_PENDING,
            ]);

            $rests = $request->input('rests', []);
            foreach ($rests as $rest) {
                if (!empty($rest['rest_start']) && !empty($rest['rest_end'])) {
                    StampCorrectionRequestRest::create([
                        'stamp_correction_request_id' => $correction->id,
                        'rest_start' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $rest['rest_start']),
                        'rest_end' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $rest['rest_end']),
                    ]);
                }
            }
        });

        return redirect()->route('stamp_correction.list')
            ->with('success', '修正申請を送信しました');
    }

    public function list(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 1) {
            return app(\App\Http\Controllers\Admin\AdminStampCorrectionRequestController::class)->list($request);
        }

        $tab = $request->input('tab', 'pending');
        if (!in_array($tab, ['pending', 'approved'])) {
            $tab = 'pending';
        }

        $status = $tab === 'approved'
            ? StampCorrectionRequest::STATUS_APPROVED
            : StampCorrectionRequest::STATUS_PENDING;

        $corrections = StampCorrectionRequest::where('user_id', $user->id)
            ->where('status', $status)
            ->with(['attendance'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('stamp_correction.list', compact('corrections', 'tab'));
    }
}
