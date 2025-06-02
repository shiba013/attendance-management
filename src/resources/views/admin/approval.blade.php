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
            @method('patch')
            @csrf
            <div class="approval-form__table">
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">名前</label>
                    <p class="approval-form__p">{{ $workRequest->user->name }}</p>
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">日付</label>
                    <p class="approval-form__p">{{ $workRequest->work->date->translatedFormat('Y年') }}</p>
                    <p class="approval-form__p">{{ $workRequest->work->date->translatedFormat('n月j日') }}</p>
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">出勤・退勤</label>
                    <input type="time" name="start_work" class="approval-form__input"
                    value="{{ old('start_work', optional($workRequest->work->start_time)->format('H:i')) ?? '' }}">
                    <span class="approval-form__span">〜</span>
                    <input type="time" name="end_work" class="approval-form__input"
                    value="{{ old('end_work', optional($workRequest->work->end_time)->format('H:i')) ?? '' }}">
                    <p class="alert">
                        @error('end_work')
                        {{ $message }}
                        @enderror
                    </p>
                </section>
                @foreach($workRequest->work->rests as $index => $rest)
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">
                        {{ $loop->first ? '休憩' : '休憩' . $loop->iteration }}
                    </label>
                    <input type="time" name="start_rest[]" class="approval-form__input"
                    value="{{ old("start_rest.$index", optional($rest->start_time)->format('H:i')) ?? '' }}">
                    <span class="approval-form__span">〜</span>
                    <input type="time" name="end_rest[]" class="approval-form__input"
                    value="{{ old("end_rest.$index", optional($rest->end_time)->format('H:i')) ?? '' }}">
                    <p class="alert">
                        @error("start_rest.$index")
                        {{ $message }}
                        @enderror
                        @error("end_rest.$index")
                        {{ $message }}
                        @enderror
                    </p>
                </section>
                @endforeach
                <section class="approval-form__group">
                    @php
                    $lastIndex = $workRequest->work->rests->count()
                    @endphp
                    <label for="" class="approval-form__label">
                        休憩{{ $lastIndex + 1 }}
                    </label>
                    <input type="time" name="start_rest[]" class="approval-form__input"
                    value="{{ old("start_rest.$lastIndex") }}" >
                    <span class="approval-form__span">〜</span>
                    <input type="time" name="end_rest[]" class="approval-form__input"
                    value="{{ old("end_rest.$lastIndex") }}">
                    <p class="alert">
                        @error("start_rest.$lastIndex")
                        {{ $message }}
                        @enderror
                        @error("end_rest.$lastIndex")
                        {{ $message }}
                        @enderror
                    </p>
                </section>
                <section class="approval-form__group">
                    <label for="" class="approval-form__label">備考</label>
                    <textarea name="remarks" class="approval-form__text">{{ $workRequest->remarks }}</textarea>
                    <p class="alert__remarks">
                        @error('remarks')
                        {{ $message }}
                        @enderror
                    </p>
                </section>
            </div>
            <div class="approval-form__submit">
                @if (optional($workRequest)->status === 1)
                <input type="submit" value="承認済み" class="approved-form__button">
                @elseif (optional($workRequest)->status === 0)
                <input type="submit" value="承認" class="approval-form__button">
                @endif
            </div>
        </form>
    </div>
</div>
@endsection