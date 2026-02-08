<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'rests' => ['nullable', 'array'],
            'rests.*.rest_start' => ['nullable', 'date_format:H:i', 'required_with:rests.*.rest_end'],
            'rests.*.rest_end' => ['nullable', 'date_format:H:i', 'required_with:rests.*.rest_start'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'rests.*.rest_start.date_format' => '休憩時間が不適切な値です',
            'rests.*.rest_start.required_with' => '休憩時間が不適切な値です',
            'rests.*.rest_end.date_format' => '休憩時間が不適切な値です',
            'rests.*.rest_end.required_with' => '休憩時間が不適切な値です',
            'reason.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $rests = $this->input('rests', []);
            foreach ($rests as $index => $rest) {
                $restStart = $rest['rest_start'] ?? null;
                $restEnd = $rest['rest_end'] ?? null;

                if ($restStart && $restEnd) {
                    if ($restStart >= $restEnd) {
                        $validator->errors()->add("rests.{$index}.rest_end", '休憩時間が不適切な値です');
                    }
                    if ($clockIn && $restStart < $clockIn) {
                        $validator->errors()->add("rests.{$index}.rest_start", '休憩時間が不適切な値です');
                    }
                    if ($clockOut && $restEnd > $clockOut) {
                        $validator->errors()->add("rests.{$index}.rest_end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
