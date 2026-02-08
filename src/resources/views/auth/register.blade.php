@extends('layouts.app')

@section('title', '会員登録 - 勤怠管理アプリ')

@section('content')
<div class="auth">
    <h2 class="auth__title">会員登録</h2>

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="form__group">
            <label class="form__label" for="name">お名前</label>
            <input class="form__input" type="text" id="name" name="name" value="{{ old('name') }}">
            @error('name')
            <p class="form__error">{{ $message }}</p>
            @enderror
        </div>

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

        <div class="form__group">
            <label class="form__label" for="password_confirmation">パスワード確認</label>
            <input class="form__input" type="password" id="password_confirmation" name="password_confirmation">
        </div>

        <button class="form__button" type="submit">登録する</button>
    </form>

    <div class="auth__link">
        <a href="{{ route('login') }}">ログインはこちら</a>
    </div>
</div>
@endsection
