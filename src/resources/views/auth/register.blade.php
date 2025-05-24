@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="register__title">会員登録</h1>
    <div class="register__inner">
        <form action="/register" method="post" class="register-form">
            @csrf
            <div class="register-form__group">
                <label for="name" class="register-form__label">名前</label>
                <input type="text" name="name" id="name" class="register-form__input"
                value="{{ old('name') }}">
                <p class="alert">
                    @error('name')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="register-form__group">
                <label for="email" class="register-form__label">メールアドレス</label>
                <input type="text" name="email" id="email" class="register-form__input"
                value="{{ old('email') }}">
                <p class="alert">
                    @error('email')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="register-form__group">
                <label for="password" class="register-form__label">パスワード</label>
                <input type="password" name="password" id="password" class="register-form__input"
                value="{{ old('password') }}">
                <p class="alert">
                    @error('password')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="register-form__group">
                <label for="password_confirmation" class="register-form__label">パスワード確認</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="register-form__input" value="{{ old('password_confirmation') }}">
                <p class="alert">
                    @error('password_confirmation')
                    {{ $message }}
                    @enderror
                </p>
            </div>
            <div class="register-form__group">
                <input type="submit" value="登録する" class="register__button">
            </div>
            <div class="register-form__group">
                <a href="/login" class="form__link">ログインはこちら</a>
            </div>
        </form>
    </div>
</div>
@endsection