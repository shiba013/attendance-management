@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/approval.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="approval__title">勤怠詳細</h1>
    </div>
    <div class="approval">
        <form action="" method="post" class="approval-form">
            @csrf
            <div class="approval-form__table">
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">名前</label>
                    <p class="approval-form__p">修正申請詳細画面だよ</p>
                    <input type="hidden" name="" value="">
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">日付</label>
                    <p class="approval-form__p">2000年</p>
                    <p class="approval-form__p">11月11日</p>
                    <input type="hidden" name="" value="">
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">出勤・退勤</label>
                    <input type="time" name="" value="" class="approval-form__input">
                    <span class="approval-form__span">〜</span>
                    <input type="time" name="" value="" class="approval-form__input">
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">休憩</label>
                    <input type="time" name="" value="" class="approval-form__input">
                    <span class="approval-form__span">〜</span>
                    <input type="time" name="" value="" class="approval-form__input">
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">休憩2</label>
                        <input type="time" name="" value="" class="approval-form__input">
                        <span class="approval-form__span">〜</span>
                        <input type="time" name="" value="" class="approval-form__input">
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">備考</label>
                        <textarea name="" class="approval-form__text"></textarea>
                </section>
            </div>
            <div class="approval-form__submit">
                {{-- @if($tab == 'attendance_correct_request') --}}
                <input type="submit" value="承認済み" class="approved-form__button">
                {{-- @else --}}
                <input type="submit" value="承認" class="approval-form__button">
            </div>
        </form>
    </div>
</div>
@endsection