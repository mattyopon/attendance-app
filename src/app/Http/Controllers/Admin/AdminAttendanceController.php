<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $currentDate = $request->input('date', Carbon::today()->toDateString());
        $date = Carbon::parse($currentDate);

        $attendances = Attendance::where('date', $date->toDateString())
            ->with(['user', 'rests'])
            ->orderBy('user_id')
            ->get();

        $prevDate = $date->copy()->subDay()->toDateString();
        $nextDate = $date->copy()->addDay()->toDateString();

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate', 'date'));
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'rests', 'stampCorrectionRequests'])
            ->findOrFail($id);

        return view('admin.attendance.show', compact('attendance'));
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('rests')->findOrFail($id);
        $date = $attendance->date->toDateString();

        DB::transaction(function () use ($attendance, $request, $date) {
            $attendance->update([
                'clock_in' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->input('clock_in')),
                'clock_out' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->input('clock_out')),
            ]);

            $attendance->rests()->delete();

            $rests = $request->input('rests', []);
            foreach ($rests as $rest) {
                if (!empty($rest['rest_start']) && !empty($rest['rest_end'])) {
                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'rest_start' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $rest['rest_start']),
                        'rest_end' => Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $rest['rest_end']),
                    ]);
                }
            }
        });

        return redirect()->route('admin.attendance.show', $id)
            ->with('success', '勤怠情報を更新しました');
    }
}
