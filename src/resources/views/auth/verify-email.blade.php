@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="content">
    <h2 class="verify-email">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </h2>
    <div class="verify-email__group">
        @php
        // 認証URLを生成（これは実際にはメールに入るURLと同じ）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => auth()->user()->id, 'hash' => sha1(auth()->user()->email)]
        );
        @endphp
        <button class="auth">
            <a href="{{ $verificationUrl }}" class="auth__button">認証はこちらから</a>
        </button>
    </div>
    <div class="verify-email__group">
        <form action="/email/verify/resend" method="post" class= "resend-form">
            @csrf
            <input type="submit" value="認証メールを再送する" class="resend-form__link">
        </form>
    </div>

</div>
@endsection