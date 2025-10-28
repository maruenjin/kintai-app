<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
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
            'clock_in'  => ['nullable','date'],
            'clock_out' => ['nullable','date'],
            'breaks'    => ['array'],
            'breaks.*.start' => ['nullable','date'],
            'breaks.*.end'   => ['nullable','date'],
            'note'      => ['required','string'],
        ];
    }

    public function withValidator($v)
    {
        $v->after(function($v){
            $in  = $this->input('clock_in')  ? Carbon::parse($this->input('clock_in'))  : null;
            $out = $this->input('clock_out') ? Carbon::parse($this->input('clock_out')) : null;

            
            if ($in && $out && $in->gt($out)) {
                $v->errors()->add('clock_in',  '出勤時間もしくは退勤時間が不適切な値です');
                $v->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

           
            foreach ((array)$this->input('breaks', []) as $i => $b) {
                $bs = !empty($b['start']) ? Carbon::parse($b['start']) : null;
                $be = !empty($b['end'])   ? Carbon::parse($b['end'])   : null;

                if ($bs && $in && $bs->lt($in)) {
                    $v->errors()->add("breaks.$i.start",'休憩時間が不適切な値です');
                }
                if ($be && $out && $be->gt($out)) {
                    $v->errors()->add("breaks.$i.end",'休憩時間もしくは退勤時間が不適切な値です');
                }
                if ($bs && $be && $be->lt($bs)) {
                    $v->errors()->add("breaks.$i.end",'休憩時間が不適切な値です');
                }
            }

            
            if (!$this->filled('note')) {
                $v->errors()->add('note', '備考を記入してください');
            }
        });
    }

    public function messages(): array
    {
        return [
            'note.required' => '備考を記入してください',
        ];
    }

}
