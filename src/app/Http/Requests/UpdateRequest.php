<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_work' => 'nullable',
            'end_work' => 'nullable |after:start_work',
            'start_rest' => 'nullable |array',
            'end_rest' => 'nullable |array',
            'start_rest.*' => 'nullable',
            'end_rest.*' => 'nullable |before:end_work',
            'remarks' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'end_work.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_rest.*.before' => '休憩が勤務時間外です',
            'remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startWork = $this->input('start_work');
            $endWork = $this->input('end_work');
            $startRests = $this->input('start_rest', []);
            $endRests = $this->input('end_rest', []);

            foreach ($startRests as $i => $startRest) {
                $endRest = $endRests[$i] ?? null;

                if ($startWork && $endWork && $startRest && $endRest) {
                    $startWorkTime = Carbon::createFromFormat('H:i', $startWork);
                    $endWorkTime = Carbon::createFromFormat('H:i', $endWork);
                    $startRestTime = Carbon::createFromFormat('H:i', $startRest);
                    $endRestTime = Carbon::createFromFormat('H:i', $endRest);

                    if ($startRestTime->gte($endRestTime)) {
                        $validator->errors()->add("end_rest.$i", '休憩の開始時間もしくは終了時間が不適切な値です');
                    }

                    if ($startRestTime->lte($startWorkTime)) {
                        $validator->errors()->add("ned_rest.$i", '休憩が勤務時間外です');
                    }
                }
            }
        });
    }
}
