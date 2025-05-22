@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="attendance__title">{{ now()->translatedFormat('Y年m月d日（D）') }}の勤怠</h1>
    </div>
    <div class="select__date">
        <a href="" class="previous-day">
            <img src="{{ asset('icon/arrow.svg') }}" alt="左矢印" class="left-arrow__img">前日
        </a>
        <form action="" method="get" class="date__search-form">
            <label for="date" class="calendar__label">
                <img src="{{ asset('icon/calendar.svg') }}" alt="カレンダー" class="calendar__img">
                {{ now()->translatedFormat('Y年m月d日') }}
            </label>
            <input type="date" name="date" id="date" class="calendar__input" value="{{ old('date') }}">
        </form>
        <a href="" class="next-day">
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
            <tr class="data__row">
                <td class="table__data">名前</td>
                <td class="table__data">出勤</td>
                <td class="table__data">退勤</td>
                <td class="table__data">休憩</td>
                <td class="table__data">合計</td>
                <td class="table__data">
                    <a href="" class="data__link">詳細</a>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection