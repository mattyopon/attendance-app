<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RestController extends Controller
{
    public function start(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->where('status', Attendance::STATUS_WORKING)
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index');
        }

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::now(),
        ]);

        $attendance->update(['status' => Attendance::STATUS_ON_BREAK]);

        return redirect()->route('attendance.index');
    }

    public function end(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->where('status', Attendance::STATUS_ON_BREAK)
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index');
        }

        $rest = Rest::where('attendance_id', $attendance->id)
            ->whereNull('rest_end')
            ->latest('rest_start')
            ->first();

        if ($rest) {
            $rest->update(['rest_end' => Carbon::now()]);
        }

        $attendance->update(['status' => Attendance::STATUS_WORKING]);

        return redirect()->route('attendance.index');
    }
}
