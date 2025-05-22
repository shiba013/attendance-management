@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="request-list__title">申請一覧</h1>
    </div>
    <div class="request-list__tab">
        <input type="radio" name="tab" id="wait" {{-- $tab == '' ? 'checked' : '' --}}>
        <label for="wait" class="tab__label">
            <a href="/stamp_correction_request/approve" class="tab__link">承認待ち</a>
        </label>

        <input type="radio" name="tab" id="done"
        {{-- $tab == 'attendance_correct_request' ? 'checked' : '' --}}>
        <label for="done" class="tab__label">
            <a href="/stamp_correction_request/approve/{attendance_correct_request}" class="tab__link">承認済み</a>
        </label>
    </div>
    <div class="request-list">
        <table class="request-list__table">
            <tr class="label__row">
                <th class="table__label">状態</th>
                <th class="table__label">名前</th>
                <th class="table__label">対象日時</th>
                <th class="table__label">申請理由</th>
                <th class="table__label">申請日時</th>
                <th class="table__label">詳細</th>
            </tr>
            <tr class="data__row">
                <td class="table__data">承認待ち</td>
                <td class="table__data">申請太郎</td>
                <td class="table__data">2025/05/01</td>
                <td class="table__data">遅延のため</td>
                <td class="table__data">20205/05/05</td>
                <td class="table__data">
                    <a href="" class="data__link">詳細</a>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection