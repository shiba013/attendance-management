@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="attendance-list__title">勤怠一覧</h1>
    </div>
    <div class="select__month">
        <a href="/attendance/list?date={{ $previousMonth }}" class="previous-month">
            <img src="{{ asset('icon/arrow.svg') }}" alt="左矢印" class="left-arrow__img">前月
        </a>
        <form action="/attendance/list" method="get" class="month__search-form">
            <label for="month" class="calendar__label">
                <img src="{{ asset('icon/calendar.svg') }}" alt="カレンダー" class="calendar__img">
                {{ $thisMonth->translatedFormat('Y年n月') }}
            </label>
            <input type="month" name="date" id="month" class="calendar__input"
            value="{{ $thisMonth->translatedFormat('Y年n月') }}" readonly>
        </form>
        <a href="/attendance/list?date={{ $nextMonth }}" class="next-month">
            翌月<img src="{{ asset('icon/arrow.svg') }}" alt="右矢印" class="right-arrow__img">
        </a>
    </div>
    <div class="attendance-list">
        <table class="attendance-list__table">
            <tr class="label__row">
                <th class="table__label">日付</th>
                <th class="table__label">出勤</th>
                <th class="table__label">退勤</th>
                <th class="table__label">休憩</th>
                <th class="table__label">合計</th>
                <th class="table__label">詳細</th>
            </tr>
            @foreach ($dailyWorks as $entry)
            @php
            $date = $entry['date'];
            $work = $entry['work'];
            $workId = $entry['id'];
            @endphp
            <tr class="data__row">
                <td class="table__data">
                    {{ $date->translatedFormat('m/d(D)') }}
                </td>
                <td class="table__data">
                    {{ optional(optional($work)->start_time)->format('H:i') ?? '' }}
                </td>
                <td class="table__data">
                    {{ optional(optional($work)->end_time)->format('H:i') ?? '' }}
                </td>
                <td class="table__data">
                    {{ optional($work)->totalRestTimeFormat() ?? '' }}
                </td>
                <td class="table__data">
                    {{ optional($work)->totalWorkTimeFormat() ?? '' }}
                </td>
                <td class="table__data">
                    <a href="/attendance/{{ $workId }}" class="data__link">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection