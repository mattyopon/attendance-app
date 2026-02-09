@extends('layouts.app')

@section('title', '勤怠詳細 - 勤怠管理アプリ')

@section('content')
<div class="detail">
    <h2 class="detail__title">勤怠詳細</h2>

    <form action="{{ route('stamp_correction.store', $attendance->id) }}" method="POST">
        @csrf

        <div class="detail__row">
            <span class="detail__label">名前</span>
            <span class="detail__value">{{ $attendance->user->name }}</span>
        </div>

        <div class="detail__row">
            <span class="detail__label">日付</span>
            <span class="detail__value">{{ $attendance->date->format('Y年m月d日') }}</span>
        </div>

        <div class="detail__row">
            <span class="detail__label">出勤・退勤</span>
            <span class="detail__value">
                <input type="text" name="clock_in" class="detail__input"
                    value="{{ old('clock_in', $attendance->clock_in->format('H:i')) }}"
                    {{ $hasPendingCorrection ? 'disabled' : '' }}>
                〜
                <input type="text" name="clock_out" class="detail__input"
                    value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}"
                    {{ $hasPendingCorrection ? 'disabled' : '' }}>
            </span>
        </div>
        @error('clock_in')
        <p class="form__error">{{ $message }}</p>
        @enderror
        @error('clock_out')
        <p class="form__error">{{ $message }}</p>
        @enderror

        <div id="rest-fields">
            @foreach($attendance->rests as $index => $rest)
            <div class="detail__row">
                <span class="detail__label">休憩{{ $index + 1 }}</span>
                <span class="detail__value">
                    <input type="text" name="rests[{{ $index }}][rest_start]" class="detail__input"
                        value="{{ old("rests.{$index}.rest_start", $rest->rest_start->format('H:i')) }}"
                        {{ $hasPendingCorrection ? 'disabled' : '' }}>
                    〜
                    <input type="text" name="rests[{{ $index }}][rest_end]" class="detail__input"
                        value="{{ old("rests.{$index}.rest_end", $rest->rest_end ? $rest->rest_end->format('H:i') : '') }}"
                        {{ $hasPendingCorrection ? 'disabled' : '' }}>
                </span>
            </div>
            @error("rests.{$index}.rest_start")
            <p class="form__error">{{ $message }}</p>
            @enderror
            @error("rests.{$index}.rest_end")
            <p class="form__error">{{ $message }}</p>
            @enderror
            @endforeach
        </div>

        <div class="detail__row">
            <span class="detail__label">備考</span>
            <span class="detail__value">
                <textarea name="reason" class="detail__textarea"
                    {{ $hasPendingCorrection ? 'disabled' : '' }}>{{ old('reason') }}</textarea>
            </span>
        </div>
        @error('reason')
        <p class="form__error">{{ $message }}</p>
        @enderror

        <div class="detail__actions">
            @if($hasPendingCorrection)
            <p style="color: #e74c3c; text-align: right; margin-top: 16px;">＊承認待ちのため修正はできません。</p>
            @else
            <button type="submit" class="detail__button">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection
