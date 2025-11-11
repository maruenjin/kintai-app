<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        
        return true;
    }

    public function rules(): array
    {
        return [
           
            'clock_in'      => ['required', 'date_format:H:i'],
            'clock_out'     => ['required', 'date_format:H:i'],
            'break1_start'  => ['nullable', 'date_format:H:i'],
            'break1_end'    => ['nullable', 'date_format:H:i'],
            'break2_start'  => ['nullable', 'date_format:H:i'],
            'break2_end'    => ['nullable', 'date_format:H:i'],
            'reason'        => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
           'clock_in.required'      => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required'     => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.date_format'   => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.date'          => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date'         => '出勤時間もしくは退勤時間が不適切な値です',
            'break1_start.date_format' => '休憩時間が不適切な値です',
            'break1_end.date_format'   => '休憩時間が不適切な値です',
            'break2_start.date_format' => '休憩時間が不適切な値です',
            'break2_end.date_format'   => '休憩時間が不適切な値です',

            'break1_start.date'        => '休憩時間が不適切な値です',
            'break1_end.date'          => '休憩時間が不適切な値です',
            'break2_start.date'        => '休憩時間が不適切な値です',
            'break2_end.date'          => '休憩時間が不適切な値です',
            'reason.required'        => '備考を記入してください',
            'reason.string'          => '備考を記入してください',
            'reason.max'             => '備考は255文字以内で入力してください',
        ];
    }

    
    public function attributes(): array
    {
        return [
            'clock_in'      => '出勤時間',
            'clock_out'     => '退勤時間',
            'break1_start'  => '1回目の休憩開始時間',
            'break1_end'    => '1回目の休憩終了時間',
            'break2_start'  => '2回目の休憩開始時間',
            'break2_end'    => '2回目の休憩終了時間',
            'reason'        => '備考',
        ];
    }

    
    public function withValidator(Validator $validator): void
    {
         $validator->after(function (Validator $validator) {
            
            if ($validator->errors()->any()) {
                return;
            }

            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            $b1Start = $this->input('break1_start');
            $b1End   = $this->input('break1_end');
            $b2Start = $this->input('break2_start');
            $b2End   = $this->input('break2_end');

          
            $toMinutes = function (?string $time): ?int {
                if (!$time) return null;
                try {
                    $t = Carbon::createFromFormat('H:i', $time);
                    return $t->hour * 60 + $t->minute;
                } catch (\Throwable $e) {
                    return null;
                }
            };

            $in  = $toMinutes($clockIn);
            $out = $toMinutes($clockOut);

            $b1s = $toMinutes($b1Start);
            $b1e = $toMinutes($b1End);
            $b2s = $toMinutes($b2Start);
            $b2e = $toMinutes($b2End);

            
            if (!is_null($in) && !is_null($out) && $in >= $out) {
               
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

           
            if (!is_null($b1s) && (!is_null($in) && $b1s < $in || !is_null($out) && $b1s > $out)) {
                $validator->errors()->add('break1_start', '休憩時間が不適切な値です');
            }
            if (!is_null($b2s) && (!is_null($in) && $b2s < $in || !is_null($out) && $b2s > $out)) {
                $validator->errors()->add('break2_start', '休憩時間が不適切な値です');
            }

            
            if (!is_null($b1e) && !is_null($out) && $b1e > $out) {
                $validator->errors()->add('break1_end', '休憩時間もしくは退勤時間が不適切な値です');
            }
            if (!is_null($b2e) && !is_null($out) && $b2e > $out) {
                $validator->errors()->add('break2_end', '休憩時間もしくは退勤時間が不適切な値です');
    }
         });
        }
    
}

