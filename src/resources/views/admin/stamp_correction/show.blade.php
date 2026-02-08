@extends('layouts.admin')

@section('title', '修正申請詳細 - 管理者')

@section('content')
<div class="correction-detail">
    <h2 class="correction-detail__title">修正申請詳細</h2>

    <div class="correction-detail__section">
        <h3 class="correction-detail__section-title">申請情報</h3>
        <div class="correction-detail__row">
            <span class="correction-detail__label">申請者</span>
            <span class="correction-detail__value">{{ $correction->user->name }}</span>
        </div>
        <div class="correction-detail__row">
            <span class="correction-detail__label">対象日付</span>
            <span class="correction-detail__value">{{ $correction->attendance->date->format('Y年m月d日') }}</span>
        </div>
        <div class="correction-detail__row">
            <span class="correction-detail__label">申請理由</span>
            <span class="correction-detail__value">{{ $correction->reason }}</span>
        </div>
        <div class="correction-detail__row">
            <span class="correction-detail__label">状態</span>
            <span class="correction-detail__value">
                @if($correction->isPending())
                <span class="badge badge--pending">承認待ち</span>
                @else
                <span class="badge badge--approved">承認済み</span>
                @endif
            </span>
        </div>
    </div>

    <div class="correction-detail__section">
        <h3 class="correction-detail__section-title">現在の勤怠</h3>
        <div class="correction-detail__row">
            <span class="correction-detail__label">出勤</span>
            <span class="correction-detail__value">{{ $correction->attendance->clock_in->format('H:i') }}</span>
        </div>
        <div class="correction-detail__row">
            <span class="correction-detail__label">退勤</span>
            <span class="correction-detail__value">{{ $correction->attendance->clock_out ? $correction->attendance->clock_out->format('H:i') : '-' }}</span>
        </div>
        @foreach($correction->attendance->rests as $index => $rest)
        <div class="correction-detail__row">
            <span class="correction-detail__label">休憩{{ $index + 1 }}</span>
            <span class="correction-detail__value">
                {{ $rest->rest_start->format('H:i') }}
                〜
                {{ $rest->rest_end ? $rest->rest_end->format('H:i') : '-' }}
            </span>
        </div>
        @endforeach
    </div>

    <div class="correction-detail__section">
        <h3 class="correction-detail__section-title">申請内容</h3>
        <div class="correction-detail__row">
            <span class="correction-detail__label">出勤</span>
            <span class="correction-detail__value">{{ $correction->requested_clock_in->format('H:i') }}</span>
        </div>
        <div class="correction-detail__row">
            <span class="correction-detail__label">退勤</span>
            <span class="correction-detail__value">{{ $correction->requested_clock_out ? $correction->requested_clock_out->format('H:i') : '-' }}</span>
        </div>
        @foreach($correction->correctionRests as $index => $rest)
        <div class="correction-detail__row">
            <span class="correction-detail__label">休憩{{ $index + 1 }}</span>
            <span class="correction-detail__value">
                {{ $rest->rest_start->format('H:i') }}
                〜
                {{ $rest->rest_end->format('H:i') }}
            </span>
        </div>
        @endforeach
    </div>

    <div class="correction-detail__actions">
        @if($correction->isPending())
        <form action="{{ route('admin.stamp_correction.approve', $correction->id) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="approve-button" style="padding: 10px 40px; font-size: 15px;">承認</button>
        </form>
        @else
        <button class="approve-button" disabled style="padding: 10px 40px; font-size: 15px;">承認済み</button>
        @endif
    </div>
</div>
@endsection
