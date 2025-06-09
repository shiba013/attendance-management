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
        <form action="/attendance/{{ $work->id }}" method="post" class="edit-form">
            @csrf
            <div class="edit-form__table">
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">名前</label>
                    <p class="edit-form__p">{{ Auth::user()->name }}</p>
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">日付</label>
                    <p class="edit-form__p">{{ $work->date->translatedFormat('Y年') }}</p>
                    <p class="edit-form__p">{{ $work->date->translatedFormat('n月j日') }}</p>
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">出勤・退勤</label>
                    @if(optional($workRequest)->status === null)
                    <input type="time" name="start_work" class="edit-form__input"
                    value="{{ old('start_work', optional($work->start_time)->format('H:i')) ?? '' }}">
                    @elseif(optional($workRequest)->status === 0)
                    <input type="time" name="start_work" class="edit-form__input"
                    value="{{ $startWork }}">
                    @elseif(optional($workRequest)->status === 1)
                    <input type="time" name="start_work" class="edit-form__input"
                    value="{{ old('start_work', $startWork ?? optional($work->start_time)->format('H:i')) }}">
                    @endif
                    <span class="edit-form__span">〜</span>
                    @if(optional($workRequest)->status === null)
                    <input type="time" name="end_work" class="edit-form__input"
                    value="{{ old('end_work', optional($work->end_time)->format('H:i')) ?? '' }}">
                    @elseif(optional($workRequest)->status === 0)
                    <input type="time" name="end_work" class="edit-form__input"
                    value="{{ $endWork }}">
                    @elseif(optional($workRequest)->status === 1)
                    <input type="time" name="end_work" class="edit-form__input"
                    value="{{ old('end_time', $endWork ?? optional($work->end_time)->format('H:i')) }}">
                    @endif
                    <p class="alert">
                        @error('end_work')
                        {{ $message }}
                        @enderror
                    </p>
                </section>
                @foreach($work->rests as $index => $rest)
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">
                    {{ $loop->first ? '休憩' : '休憩' . $loop->iteration }}
                    </label>
                    @if(optional($workRequest)->status === null)
                    <input type="time" name="start_rest[]" class="edit-form__input"
                    value="{{ old("start_rest.[$index]", optional($rest->start_time)->format('H:i') ?? '') }}">
                    @elseif(optional($workRequest)->status === 0)
                    <input type="time" name="start_rest[]" class="edit-form__input"
                    value="{{ $restRequestTimes[$rest->id]['start_rest'] ?? '' }}">
                    @elseif(optional($workRequest)->status === 1)
                    <input type="time" name="start_rest[]" class="edit-form__input"
                    value="{{ old("start_rest.[$index]", $restRequestTimes[$rest->id]['start_rest'] ?? optional($rest->start_time)->format('H:i')) }}">
                    @endif
                    <span class="edit-form__span">〜</span>
                    @if(optional($workRequest)->status === null)
                    <input type="time" name="end_rest[]" class="edit-form__input"
                    value="{{ old("end_rest.[$index]", optional($rest->end_time)->format('H:i') ?? '') }}">
                    @elseif(optional($workRequest)->status === 0)
                    <input type="time" name="end_rest[]" class="edit-form__input"
                    value="{{ $restRequestTimes[$rest->id]['end_rest'] ?? '' }}">
                    @elseif(optional($workRequest)->status === 1)
                    <input type="time" name="end_rest[]" class="edit-form__input"
                    value="{{ old("end_rest.[$index]", $restRequestTimes[$rest->id]['end_rest'] ?? optional($rest->end_time)->format('H:i')) }}">
                    @endif
                    <p class="alert">
                        @error("end_rest.$index")
                        {{ $message }}
                        @enderror
                    </p>
                </section>
                @endforeach
                <section class="edit-form__group">
                    @php
                    $lastIndex = $work->rests->count()
                    @endphp
                    <label for="" class="edit-form__label">
                        休憩{{ $lastIndex + 1 }}
                    </label>
                    @if(optional($workRequest)->status === null)
                    <input type="time" name="start_rest[]" class="edit-form__input"
                    value="{{ old("start_rest.$lastIndex") ?? '' }}" >
                    @elseif(optional($workRequest)->status === 0)
                    <input type="time" name="start_rest[]" class="edit-form__input"
                    value="{{ $startRestNew ?? '' }}" >
                    @elseif(optional($workRequest)->status === 1)
                    <input type="time" name="start_rest[]" class="edit-form__input"
                    value="{{ old("start_rest.$lastIndex") ?? '' }}">
                    @endif
                    <span class="edit-form__span">〜</span>
                    @if(optional($workRequest)->status === null)
                    <input type="time" name="end_rest[]" class="edit-form__input"
                    value="{{ old("end_rest.$lastIndex") ?? '' }}">
                    @elseif(optional($workRequest)->status === 0)
                    <input type="time" name="end_rest[]" class="edit-form__input"
                    value="{{ $endRestNew ?? '' }}">
                    @elseif(optional($workRequest)->status === 1)
                    <input type="time" name="end_rest[]" class="edit-form__input"
                    value="{{ old("start_rest.$lastIndex") ?? '' }}">
                    @endif
                    <p class="alert">
                        @error("end_rest.$lastIndex")
                        {{ $message }}
                        @enderror
                    </p>
                </section>
                <section class="edit-form__group">
                    <label for="" class="edit-form__label">備考</label>
                    @if(optional($workRequest)->status === null)
                    <textarea name="remarks" class="edit-form__text">{{ old('remarks') }}</textarea>
                    @elseif(optional($workRequest)->status === 0)
                    <textarea name="remarks" class="edit-form__text">{{ $workRequest->remarks }}</textarea>
                    @elseif(optional($workRequest)->status === 1)
                    <textarea name="remarks" class="edit-form__text">{{ old('remarks', $workRequest->remarks ?? '') }}</textarea>
                    @endif
                    <p class="alert__remarks">
                        @error('remarks')
                        {{ $message }}
                        @enderror
                    </p>
                </section>
            </div>
            <div class="edit-form__submit">
                @if(optional($workRequest)->status === null ||optional($workRequest)->status === 1)
                <input type="submit" value="修正" class="edit-form__button">
                @elseif(optional($workRequest)->status === 0)
                <p class="edit-form__message">*承認待ちのため修正はできません。</p>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection