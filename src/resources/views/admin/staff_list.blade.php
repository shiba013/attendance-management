@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="staff-list__title">スタッフ一覧</h1>
    </div>
    <div class="staff-list">
        <table class="staff-list__table">
            <tr class="label__row">
                <th class="table__label">名前</th>
                <th class="table__label">メールアドレス</th>
                <th class="table__label">月次勤怠</th>
            </tr>
            <tr class="data__row">
                <td class="table__data"></td>
                <td class="table__data"></td>
                <td class="table__data">
                    <a href="" class="data__link">詳細</a>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection