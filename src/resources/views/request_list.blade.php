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
        <input type="radio" name="tab" id="wait" {{ $tab == '' ? 'checked' : '' }}>
        <label for="wait" class="tab__label">
            <a href="/stamp_correction_request/list" class="tab__link">承認待ち</a>
        </label>

        <input type="radio" name="tab" id="done"
        {{ $tab == 'done' ? 'checked' : '' }}>
        <label for="done" class="tab__label">
            <a href="/stamp_correction_request/list/?tab=done" class="tab__link">承認済み</a>
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
            @foreach($corrections as $correction)
            <tr class="data__row">
                <td class="table__data">
                    @if ($correction->status === 0)
                    承認待ち
                    @elseif ($correction->status === 1)
                    承認済み
                    @endif
                </td>
                <td class="table__data">{{ $correction->user->name }}</td>
                <td class="table__data">{{ $correction->work->date->translatedFormat('Y年m月d日')  }}</td>
                <td class="table__data">{{ $correction->remarks }}</td>
                <td class="table__data">{{ $correction->created_at->translatedFormat('Y年m月d日') }}</td>
                <td class="table__data">
                    <a href="/attendance/{{ $correction->work_id }}" class="data__link">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection