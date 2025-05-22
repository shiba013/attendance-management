@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/detail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="title">
        <h1 class="detail__title">勤怠詳細</h1>
    </div>
    <div class="edit">
        <form action="" method="post" class="edit-form">
            @method('patch')
            @csrf
            <div class="edit-form__table">
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">名前</label>
                    <p class="edit-form__p">管理者だよ</p>
                    <input type="hidden" name="" value="">
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">日付</label>
                    <p class="edit-form__p">2025年</p>
                    <p class="edit-form__p">5月22日</p>
                    <input type="hidden" name="" value="">
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">出勤・退勤</label>
                    <input type="time" name="" value="" class="edit-form__input">
                    <span class="edit-form__span">〜</span>
                    <input type="time" name="" value="" class="edit-form__input">
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">休憩</label>
                    <input type="time" name="" value="" class="edit-form__input">
                    <span class="edit-form__span">〜</span>
                    <input type="time" name="" value="" class="edit-form__input">
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">休憩2</label>
                    <input type="time" name="" value="" class="edit-form__input">
                    <span class="edit-form__span">〜</span>
                    <input type="time" name="" value="" class="edit-form__input">
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">備考</label>
                    <textarea name="" class="edit-form__text"></textarea>
                </section>
            </div>
            <div class="edit-form__submit">
                <input type="submit" value="修正" class="edit-form__button">
            </div>
        </form>
    </div>
</div>
@endsection