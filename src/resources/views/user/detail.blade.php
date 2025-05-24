@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/detail.css') }}">
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
                    <p class="edit-form__p">{{ Auth::user()->name }}</p>
                    <input type="hidden" name="" value="">
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">日付</label>
                    <p class="edit-form__p">{{ $work->date->translatedFormat('Y年') }}</p>
                    <p class="edit-form__p">{{ $work->date->translatedFormat('m月d日') }}</p>
                    <input type="hidden" name="" value="">
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">出勤・退勤</label>
                    <input type="time" name="start_work" value="{{ $work->start_time->format('H:i') }}" class="edit-form__input">
                    <span class="edit-form__span">〜</span>
                    <input type="time" name="end_work" value="{{ $work->end_time->format('H:i') }}" class="edit-form__input">
                </section>
                @foreach($work->rests as $rest)
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">
                        {{ $loop->first ? '休憩' : '休憩' . $loop->iteration }}
                    </label>
                    <input type="time" name="start_rest[]" value="{{ optional($rest->start_time)->format('H:i') }}" class="edit-form__input">
                    <span class="edit-form__span">〜</span>
                    <input type="time" name="end_rest[]" value="{{ optional($rest->end_time)->format('H:i') }}" class="edit-form__input">
                </section>
                @endforeach
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">備考</label>
                    <textarea name="remarks" class="edit-form__text"></textarea>
                </section>
            </div>
            <div class="edit-form__submit">
                <input type="submit" value="修正" class="edit-form__button">
                <p class="edit-form__message">*承認待ちのため修正はできません。</p>
            </div>
        </form>
    </div>
</div>
@endsection