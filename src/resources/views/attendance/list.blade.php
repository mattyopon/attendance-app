@extends('layouts.app')

@section('title', '勤怠一覧 - 勤怠管理アプリ')

@section('content')
<div class="page-header">
    <h1 class="page-header__title">勤怠一覧</h1>
</div>

<div class="date-nav">
    <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="date-nav__arrow">&lt; 前月</a>
    <span class="date-nav__current">📅 {{ $date->format('Y/m') }}</span>
    <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="date-nav__arrow">翌月 &gt;</a>
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
                <td><a href="{{ route('attendance.show', $attendance->id) }}" class="table__link">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
