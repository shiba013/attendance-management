@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="login__title">ログイン</h1>
    <div class="login__inner">
        <form action="/login" method="post" class="login-form">
            @csrf
            <div class="login-form__group">
                <label for="email" class="login-form__label">メールアドレス</label>
                <input type="text" name="email" id="email" class="login-form__input"
                value="{{ old('email') }}">
                <p class="alert">
                    @error('email')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="login-form__group">
                <label for="password" class="login-form__label">パスワード</label>
                <input type="password" name="password" id="password" class="login-form__input"
                value="{{ old('password') }}">
                <p class="alert">
                    @error('password')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="login-form__group">
                <input type="submit" value="ログインする" class="login__button">
            </div>
            <div class="login-form__group">
                <a href="/register" class="form__link">会員登録はこちら</a>
            </div>
        </form>
    </div>
</div>
@endsection