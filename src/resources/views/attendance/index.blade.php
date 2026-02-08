@extends('layouts.app')

@section('title', '勤怠打刻 - 勤怠管理アプリ')

@section('content')
<div class="attendance">
    @if($status === 'off_duty')
    <p class="attendance__status">勤務外</p>
    @elseif($status === 'working')
    <p class="attendance__status attendance__status--working">出勤中</p>
    @elseif($status === 'on_break')
    <p class="attendance__status attendance__status--on_break">休憩中</p>
    @elseif($status === 'left')
    <p class="attendance__status attendance__status--left">退勤済</p>
    @endif

    <p class="attendance__date" id="current-date"></p>
    <p class="attendance__time" id="current-time"></p>

    <div class="attendance__buttons">
        @if($status === 'off_duty')
        <form action="{{ route('attendance.clockIn') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button attendance__button--clock-in">出勤</button>
        </form>
        @elseif($status === 'working')
        <form action="{{ route('attendance.clockOut') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button attendance__button--clock-out">退勤</button>
        </form>
        <form action="{{ route('rest.start') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button attendance__button--rest-start">休憩入</button>
        </form>
        @elseif($status === 'on_break')
        <form action="{{ route('rest.end') }}" method="POST">
            @csrf
            <button type="submit" class="attendance__button attendance__button--rest-end">休憩戻</button>
        </form>
        @elseif($status === 'left')
        <p class="attendance__message">お疲れ様でした。</p>
        @endif
    </div>
</div>

<script>
function updateDateTime() {
    var now = new Date();
    var days = ['日', '月', '火', '水', '木', '金', '土'];
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var dayOfWeek = days[now.getDay()];
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('current-date').textContent = year + '\u5E74' + month + '\u6708' + day + '\u65E5(' + dayOfWeek + ')';
    document.getElementById('current-time').textContent = hours + ':' + minutes;
}

updateDateTime();
setInterval(updateDateTime, 1000);
</script>
@endsection
