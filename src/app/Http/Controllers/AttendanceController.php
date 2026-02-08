<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        $status = 'off_duty';
        if ($attendance) {
            switch ($attendance->status) {
                case Attendance::STATUS_WORKING:
                    $status = 'working';
                    break;
                case Attendance::STATUS_ON_BREAK:
                    $status = 'on_break';
                    break;
                case Attendance::STATUS_LEFT:
                    $status = 'left';
                    break;
            }
        }

        return view('attendance.index', compact('status', 'attendance'));
    }

    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $now = Carbon::now();
        $today = $now->toDateString();

        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return redirect()->route('attendance.index');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $now,
            'status' => Attendance::STATUS_WORKING,
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->whereIn('status', [Attendance::STATUS_WORKING])
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out' => Carbon::now(),
            'status' => Attendance::STATUS_LEFT,
        ]);

        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $user = auth()->user();
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $currentMonth);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->with('rests')
            ->get();

        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth', 'date'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['user', 'rests', 'stampCorrectionRequests'])
            ->firstOrFail();

        $hasPendingCorrection = $attendance->hasPendingCorrection();

        return view('attendance.show', compact('attendance', 'hasPendingCorrection'));
    }
}
