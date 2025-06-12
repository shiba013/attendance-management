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
                @case('3') 休憩中 @break
                @case('4') 勤務中 @break
                @case('2') 退勤済 @break
                @default 勤務外
            @endswitch
        </p>
        <p class="attendance__date" id="date">{{ now()->translatedFormat('Y年m月d日(D)') }}</p>
        <p class="attendance__time" id="time">{{ now()->format('H:i') }}</p>
        @if($status === 0)
        <input type="submit" name="start_work" value="出勤" class="work__button">
        @elseif($status === 1 || $status === 4)
        <div class="form__button">
            <input type="submit" name="end_work" value="退勤" class="done__button">
            <input type="submit" name="start_rest" value="休憩入" class="rest__button">
        </div>
        @elseif($status === 3)
        <input type="submit" name="end_rest" value="休憩戻" class="rested__button">
        @elseif($status === 2)
        <p class="done__message">お疲れ様でした。</p>
        @endif
    </form>
</div>
<script>
    function updateJapanDateTime() {
        const now = new Date();
        const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        const jstString = now.toLocaleString('en-US', { timeZone: 'Asia/Tokyo' });
        const jst = new Date(jstString);
        const year = jst.getFullYear();
        const month = String(jst.getMonth() + 1).padStart(2, '0');
        const date = String(jst.getDate()).padStart(2, '0');
        const day = weekdays[jst.getDay()];
        const hour = String(jst.getHours()).padStart(2, '0');
        const minute = String(jst.getMinutes()).padStart(2, '0');

        document.getElementById('date').textContent = `${year}年${month}月${date}日(${day})`;
        document.getElementById('time').textContent = `${hour}:${minute}`;
    }
    function startClock() {
        updateJapanDateTime();
        requestAnimationFrame(startClock);
    }
    startClock();
</script>
@endsection