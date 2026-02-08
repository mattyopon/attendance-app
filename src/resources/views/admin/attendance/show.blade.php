@extends('layouts.admin')

@section('title', '勤怠詳細 - 管理者')

@section('content')
<div class="detail">
    <h2 class="detail__title">勤怠詳細</h2>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="detail__row">
            <span class="detail__label">名前</span>
            <span class="detail__value">{{ $attendance->user->name }}</span>
        </div>

        <div class="detail__row">
            <span class="detail__label">日付</span>
            <span class="detail__value">{{ $attendance->date->format('Y年m月d日') }}</span>
        </div>

        <div class="detail__row">
            <span class="detail__label">出勤</span>
            <span class="detail__value">
                <input type="text" name="clock_in" class="detail__input"
                    value="{{ old('clock_in', $attendance->clock_in->format('H:i')) }}">
            </span>
        </div>
        @error('clock_in')
        <p class="form__error">{{ $message }}</p>
        @enderror

        <div class="detail__row">
            <span class="detail__label">退勤</span>
            <span class="detail__value">
                <input type="text" name="clock_out" class="detail__input"
                    value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}">
            </span>
        </div>
        @error('clock_out')
        <p class="form__error">{{ $message }}</p>
        @enderror

        <div id="rest-fields">
            @foreach($attendance->rests as $index => $rest)
            <div class="detail__row rest-row">
                <span class="detail__label">休憩{{ $index + 1 }}</span>
                <span class="detail__value">
                    <input type="text" name="rests[{{ $index }}][rest_start]" class="detail__input"
                        value="{{ old("rests.{$index}.rest_start", $rest->rest_start->format('H:i')) }}">
                    〜
                    <input type="text" name="rests[{{ $index }}][rest_end]" class="detail__input"
                        value="{{ old("rests.{$index}.rest_end", $rest->rest_end ? $rest->rest_end->format('H:i') : '') }}">
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

        <div class="detail__actions">
            <button type="submit" class="detail__button">修正</button>
        </div>
    </form>
</div>
@endsection
