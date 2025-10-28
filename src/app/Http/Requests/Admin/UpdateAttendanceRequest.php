<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
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
            'clock_in'  => ['nullable','date_format:H:i'],
            'clock_out' => ['nullable','date_format:H:i'],
            'b1_start'  => ['nullable','date_format:H:i'],
            'b1_end'    => ['nullable','date_format:H:i'],
            'b2_start'  => ['nullable','date_format:H:i'],
            'b2_end'    => ['nullable','date_format:H:i'],
            'note'      => ['required','string'], 
        ];
    }

     public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください', 
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            /** @var \App\Models\Attendance $attendance */
            $attendance = $this->route('attendance');
            $date = $attendance->work_date->toDateString();

            $in  = $this->filled('clock_in')  ? Carbon::parse("$date ".$this->clock_in)   : null;
            $out = $this->filled('clock_out') ? Carbon::parse("$date ".$this->clock_out)  : null;

           
            if ($in && $out && $in->gte($out)) {
                $v->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                $v->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            
            foreach ([1,2] as $i) {
                $s = $this->input("b{$i}_start");
                $e = $this->input("b{$i}_end");
                if (($s && !$e) || (!$s && $e)) {
                    $v->errors()->add("b{$i}_start", '休憩時間が不適切な値です'); 
                    $v->errors()->add("b{$i}_end",   '休憩時間が不適切な値です');
                }
            }

            
            foreach ([1,2] as $i) {
                if (!$this->filled("b{$i}_start") || !$this->filled("b{$i}_end")) continue;

                $bs = Carbon::parse("$date ".$this->input("b{$i}_start"));
                $be = Carbon::parse("$date ".$this->input("b{$i}_end"));

                
                if ($be->lte($bs)) {
                    $v->errors()->add("b{$i}_end", '休憩時間もしくは退勤時間が不適切な値です'); 
                }

                
                if ($in && $bs->lt($in)) {
                    $v->errors()->add("b{$i}_start", '休憩時間が不適切な値です');
                }
                if ($out && $be->gt($out)) {
                    $v->errors()->add("b{$i}_end", '休憩時間もしくは退勤時間が不適切な値です'); 
                }
            }
        });
    }

}
