@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance.css') }}">
@endsection

@section('content')
<div class="content">
    <form action="/attendance" method="post" class="attendance-form">
        @csrf
        <p class="attendance__category">
            @switch($status)
                @case('1') 勤務中 @break
                @case('2') 休憩中 @break
                @case('3') 退勤済 @break
                @default 勤務外
            @endswitch
        </p>
        <p class="attendance__date">{{ now()->translatedFormat('Y年m月d日(D)') }}</p>
        <p class="attendance__time">{{ now()->format('H:i') }}</p>
        @if($status === 1)
        <input type="submit" name="start_work" value="出勤" class="work__button">
        @elseif($status === 2)
        <div class="form__button">
            <input type="submit" name="end_work" value="退勤" class="done__button">
            <input type="submit" name="start_rest" value="休憩入" class="rest__button">
        </div>
        @elseif($status === 3)
        <input type="submit" name="end_rest" value="休憩戻" class="rested__button">
        @elseif($status === 4)
        <p class="done__message">お疲れ様でした。</p>
        @endif
    </form>
</div>
@endsection