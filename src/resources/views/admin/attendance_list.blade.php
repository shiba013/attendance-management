@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="attendance__title">{{ $thisDate->translatedFormat('Y年n月j日') }}の勤怠</h1>
    </div>
    <div class="select__date">
        <a href="/admin/attendance/list?date={{ $previousDay }}" class="previous-day">
            <img src="{{ asset('icon/arrow.svg') }}" alt="左矢印" class="left-arrow__img">前日
        </a>
        <form action="/admin/attendance/list" method="get" class="date__search-form">
            <label for="date" class="calendar__label">
                <img src="{{ asset('icon/calendar.svg') }}" alt="カレンダー" class="calendar__img">
                {{ $thisDate->translatedFormat('Y年n月j日') }}
            </label>
            <input type="date" name="date" id="date" class="calendar__input"
            value="{{ request('date') ?? $thisDate->translatedFormat('Y年n月j日') }}">
            <input type="submit" value="検索" class="calendar__submit">
        </form>
        <a href="/admin/attendance/list?date={{ $nextDay }}" class="next-day">
            翌日<img src="{{ asset('icon/arrow.svg') }}" alt="右矢印" class="right-arrow__img">
        </a>
    </div>
    <div class="attendance-list">
        <table class="attendance-list__table">
            <tr class="label__row">
                <th class="table__label">名前</th>
                <th class="table__label">出勤</th>
                <th class="table__label">退勤</th>
                <th class="table__label">休憩</th>
                <th class="table__label">合計</th>
                <th class="table__label">詳細</th>
            </tr>
            @foreach($works as $work)
            <tr class="data__row">
                <td class="table__data">
                    {{ $work->user->name }}
                </td>
                <td class="table__data">
                    {{ optional($work->start_time)->format('H:i') ?? '' }}
                </td>
                <td class="table__data">
                    {{ optional($work->end_time)->format('H:i') ?? '' }}
                </td>
                <td class="table__data">
                    {{ $work->totalRestTimeFormat() ?? '' }}
                </td>
                <td class="table__data">
                    {{ $work->totalWorkTimeFormat() ?? '' }}
                </td>
                <td class="table__data">
                    <a href="/attendance/{{ $work->id }}" class="data__link">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection