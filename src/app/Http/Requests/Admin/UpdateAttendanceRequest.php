<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in_time'        => ['nullable','date_format:H:i'],
            'clock_out_time'       => ['nullable','date_format:H:i','after_or_equal:clock_in_time'],

            
            'breaks.0.start'       => ['nullable','date_format:H:i','after_or_equal:clock_in_time','before_or_equal:clock_out_time'],
            'breaks.0.end'         => ['nullable','date_format:H:i','after_or_equal:breaks.0.start','before_or_equal:clock_out_time'],
            'breaks.1.start'       => ['nullable','date_format:H:i','after_or_equal:clock_in_time','before_or_equal:clock_out_time'],
            'breaks.1.end'         => ['nullable','date_format:H:i','after_or_equal:breaks.1.start','before_or_equal:clock_out_time'],

           
           'note' => ['required','string','max:500'], 
        ];
    }

    public function messages(): array
    {
        return [
            'clock_out_time.after_or_equal' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.start.after_or_equal'  => '休憩時間が不適切な値です',
            'breaks.*.start.before_or_equal' => '休憩時間が不適切な値です',

            'breaks.*.end.after_or_equal'    => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.end.before_or_equal'   => '休憩時間もしくは退勤時間が不適切な値です',

           
            'note.required'                  => '備考を記入してください',
        ];
    }

    
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        $normalize = function ($v) { return $v === '' ? null : $v; };

        $data['clock_in_time']  = $normalize($data['clock_in_time']  ?? null);
        $data['clock_out_time'] = $normalize($data['clock_out_time'] ?? null);

        if (isset($data['breaks']) && is_array($data['breaks'])) {
            foreach ($data['breaks'] as $i => $row) {
                $data['breaks'][$i]['start'] = $normalize($row['start'] ?? null);
                $data['breaks'][$i]['end']   = $normalize($row['end']   ?? null);
            }
        }

        $this->replace($data);
    }

    
    public function withValidator($validator): void
{
    $validator->after(function ($v) {
        /** @var \App\Models\Attendance $attendance */
        $attendance = $this->route('attendance');

       
        $dateStr = method_exists($attendance->work_date, 'toDateString')
            ? $attendance->work_date->toDateString()
            : (string) $attendance->work_date;

        
        $inStr  = $this->input('clock_in_time');
        $outStr = $this->input('clock_out_time');
        $in  = $inStr  ? \Carbon\Carbon::parse("$dateStr $inStr")  : null;
        $out = $outStr ? \Carbon\Carbon::parse("$dateStr $outStr") : null;

        
        if ($in && $out && $in->gt($out)) {
            $v->errors()->add('clock_out_time', '出勤時間もしくは退勤時間が不適切な値です');
        }

       
        $breaks = (array) $this->input('breaks', []);

        
        foreach ([0, 1] as $i) {
            $sStr = data_get($breaks, "$i.start");
            $eStr = data_get($breaks, "$i.end");

           
            if (($sStr && !$eStr) || (!$sStr && $eStr)) {
                $v->errors()->add("breaks.$i.end", '休憩時間が不適切な値です');
                continue;
            }
            if (!$sStr || !$eStr) continue;

            $bs = \Carbon\Carbon::parse("$dateStr $sStr");
            $be = \Carbon\Carbon::parse("$dateStr $eStr");

            
            if ($be->lte($bs)) {
                $v->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
            }
            
            if ($in && $bs->lt($in)) {
                $v->errors()->add("breaks.$i.end", '休憩時間が不適切な値です');
            }
            if ($out && $be->gt($out)) {
                $v->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
            }
        }
    });
}

}

