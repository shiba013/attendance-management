@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/private_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="private-list__title">{{ $user->name }}さんの勤怠</h1>
    </div>
    <div class="select__month">
        <a href="/admin/attendance/staff/{{ $user->id }}?date={{ $previousMonth }}" class="previous-month">
            <img src="{{ asset('icon/arrow.svg') }}" alt="左矢印" class="left-arrow__img">前月
        </a>
        <form action="/admin/attendance/staff/{{ $user->id }}" method="get" class="month__search-form">
            <label for="month" class="calendar__label">
                <img src="{{ asset('icon/calendar.svg') }}" alt="カレンダー" class="calendar__img">
                {{ $thisMonth->translatedFormat('Y/m') }}
            </label>
            <input type="month" name="date" id="month" class="calendar__input"
            value="{{ $thisMonth->translatedFormat('Y年n月') }}" readonly>
        </form>
        <a href="/admin/attendance/staff/{{ $user->id }}?date={{ $nextMonth }}" class="next-month">
            翌月<img src="{{ asset('icon/arrow.svg') }}" alt="右矢印" class="right-arrow__img">
        </a>
    </div>
    <div class="private-list">
        <table class="private-list__table">
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
                    {{ $date->translatedFormat('m/d(D)') ?? '' }}
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
        <form action="/admin/attendance/staff/{{ $user->id }}/export" method="post" class="export-form">
            @csrf
            <input type="hidden" name="date" value="{{ request('date') }}">
            <input type="submit" value="CSV出力" class="export-form__button">
        </form>
    </div>
</div>
@endsection