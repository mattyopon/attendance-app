@extends('layouts.app')

@section('title', 'ログイン - 勤怠管理アプリ')

@section('content')
<div class="auth">
    <h2 class="auth__title">ログイン</h2>

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="form__group">
            <label class="form__label" for="email">メールアドレス</label>
            <input class="form__input" type="email" id="email" name="email" value="{{ old('email') }}">
            @error('email')
            <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form__group">
            <label class="form__label" for="password">パスワード</label>
            <input class="form__input" type="password" id="password" name="password">
            @error('password')
            <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

        <button class="form__button" type="submit">ログイン</button>
    </form>

    <div class="auth__link">
        <a href="{{ route('register') }}">会員登録はこちら</a>
    </div>
</div>
@endsection
