<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    public function list()
    {
        $staff = User::where('role', 0)->orderBy('name')->paginate(15);
        return view('admin.staff.list', compact('staff'));
    }

    public function attendanceList(Request $request, $id)
    {
        $user = User::where('role', 0)->findOrFail($id);
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $currentMonth);

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->with('rests')
            ->get();

        $prevMonth = $date->copy()->subMonth()->format('Y-m');
        $nextMonth = $date->copy()->addMonth()->format('Y-m');

        return view('admin.staff.attendance_list', compact('user', 'attendances', 'currentMonth', 'prevMonth', 'nextMonth', 'date'));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::where('role', 0)->findOrFail($id);
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $currentMonth);

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->with('rests')
            ->get();

        $filename = $user->name . '_' . $currentMonth . '_勤怠.csv';

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩時間', '勤務時間']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->date->format('Y-m-d'),
                    $attendance->clock_in ? $attendance->clock_in->format('H:i') : '',
                    $attendance->clock_out ? $attendance->clock_out->format('H:i') : '',
                    $attendance->formatted_total_rest,
                    $attendance->formatted_total_work,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
