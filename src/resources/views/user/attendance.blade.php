@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance.css') }}">
@endsection

@section('content')
<div class="content">
    <form action="" method="post" class="attendance-form">
        @csrf
        <p class="attendance__category">勤務外</p>
        <p class="attendance__category">勤務中</p>
        <p class="attendance__category">休憩中</p>
        <p class="attendance__category">退勤済</p>
        <p class="attendance__date">{{ now()->translatedFormat('Y年m月d日(D)') }}</p>
        <p class="attendance__time">{{ now()->translatedFormat('h:m') }}</p>
        <input type="submit" value="出勤" class="work__button">
        <div class="form__button">
            <input type="submit" value="退勤" class="done__button">
            <input type="submit" value="休憩入" class="rest__button">
        </div>
        <input type="submit" value="休憩戻" class="rested__button">
        <p class="done__message">お疲れ様でした。</p>
    </form>
</div>
@endsection