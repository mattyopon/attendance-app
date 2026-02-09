@extends('layouts.admin')

@section('title', $user->name . 'さんの勤怠 - 管理者')

@section('content')
<div class="page-header">
    <h1 class="page-header__title">{{ $user->name }}さんの勤怠</h1>
</div>

<div class="date-nav">
    <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $prevMonth]) }}" class="date-nav__arrow">&lt; 前月</a>
    <span class="date-nav__current">{{ $date->format('Y年m月') }}</span>
    <a href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $nextMonth]) }}" class="date-nav__arrow">翌月 &gt;</a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$attendance->date->dayOfWeek] }})</td>
                <td>{{ $attendance->clock_in->format('H:i') }}</td>
                <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                <td>{{ $attendance->formatted_total_rest }}</td>
                <td>{{ $attendance->formatted_total_work }}</td>
                <td><a href="{{ route('admin.attendance.show', $attendance->id) }}" class="table__link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="text-align: right; margin-top: 20px;">
    <a href="{{ route('admin.staff.attendance.export', ['id' => $user->id, 'month' => $currentMonth]) }}"
       class="export-button">CSV出力</a>
</div>
@endsection
