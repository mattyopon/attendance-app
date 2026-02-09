@extends('layouts.admin')

@section('title', '修正申請詳細 - 管理者')

@section('content')
<div class="detail">
    <h2 class="detail__title">勤怠詳細</h2>

    <div class="detail__row">
        <span class="detail__label">名前</span>
        <span class="detail__value">{{ $correction->user->name }}</span>
    </div>

    <div class="detail__row">
        <span class="detail__label">日付</span>
        <span class="detail__value">{{ $correction->attendance->date->format('Y年') }}　{{ $correction->attendance->date->format('n月j日') }}</span>
    </div>

    <div class="detail__row">
        <span class="detail__label">出勤・退勤</span>
        <span class="detail__value">
            {{ $correction->requested_clock_in->format('H:i') }}
            〜
            {{ $correction->requested_clock_out ? $correction->requested_clock_out->format('H:i') : '-' }}
        </span>
    </div>

    @foreach($correction->correctionRests as $index => $rest)
    <div class="detail__row">
        <span class="detail__label">休憩{{ $index > 0 ? ($index + 1) : '' }}</span>
        <span class="detail__value">
            {{ $rest->rest_start->format('H:i') }}
            〜
            {{ $rest->rest_end->format('H:i') }}
        </span>
    </div>
    @endforeach

    <div class="detail__row">
        <span class="detail__label">備考</span>
        <span class="detail__value">{{ $correction->reason }}</span>
    </div>

    <div class="detail__actions">
        @if($correction->isPending())
        <form action="{{ route('admin.stamp_correction.approve', $correction->id) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="detail__button">承認</button>
        </form>
        @else
        <button class="detail__button" disabled>承認済み</button>
        @endif
    </div>
</div>
@endsection
