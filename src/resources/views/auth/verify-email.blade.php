@extends('layouts.app')

@section('content')
<div class="auth">
    <h2 class="auth__title">メール認証</h2>

    <p style="text-align: center; margin-bottom: 20px;">
        登録していただいたメールアドレスに認証メールを送信しました。<br>
        メール内のリンクをクリックして認証を完了してください。
    </p>

    @if (session('status') == 'verification-link-sent')
    <div class="alert alert--success">
        新しい認証リンクを送信しました。
    </div>
    @endif

    <form action="/email/verification-notification" method="POST">
        @csrf
        <button class="form__button" type="submit">認証メールを再送信</button>
    </form>
</div>
@endsection
