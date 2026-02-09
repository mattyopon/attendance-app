@extends('layouts.app')

@section('content')
<div class="auth" style="text-align: center;">
    <p style="margin-bottom: 24px; font-size: 14px; line-height: 1.8;">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    @if (session('status') == 'verification-link-sent')
    <div class="alert alert--success">
        新しい認証リンクを送信しました。
    </div>
    @endif

    <a href="#" style="display: inline-block; padding: 12px 40px; border: 1px solid #333; border-radius: 4px; color: #333; font-size: 14px; margin-bottom: 20px;">認証はこちらから</a>

    <div style="margin-top: 16px;">
        <form action="/email/verification-notification" method="POST" style="display: inline;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #2196F3; font-size: 14px; cursor: pointer; text-decoration: underline;">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection
