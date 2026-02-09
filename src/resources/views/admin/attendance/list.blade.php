@extends('layouts.admin')

@section('title', '勤怠一覧 - 管理者')

@section('content')
<div class="page-header">
    <h1 class="page-header__title">{{ $date->format('Y年n月j日') }}の勤怠</h1>
</div>

<div class="date-nav">
    <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="date-nav__arrow">&lt; 前日</a>
    <span class="date-nav__current">&#x1F4C5; {{ $date->format('Y/m/d') }}</span>
    <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="date-nav__arrow">翌日 &gt;</a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>名前</th>
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
                <td>{{ $attendance->user->name }}</td>
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
@endsection
