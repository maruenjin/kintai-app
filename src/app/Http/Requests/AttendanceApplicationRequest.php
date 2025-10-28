<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        
        return true;
    }

    public function rules(): array
    {
        return [
           
            'attendance_id' => ['required','integer','exists:attendances,id'],
            'work_date'     => ['nullable','date'],
            'clock_in'      => ['nullable','date'],
            'clock_out'     => ['nullable','date','after:clock_in'],
            'note'          => ['nullable','string','max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_id.required' => '対象の勤怠が不正です。',
            'attendance_id.exists'   => '対象の勤怠が見つかりません。',
            'work_date.date'         => '日付の形式が正しくありません。',
            'clock_in.date'          => '出勤時刻の形式が正しくありません。',
            'clock_out.date'         => '退勤時刻の形式が正しくありません。',
            'clock_out.after'        => '退勤時刻は出勤時刻より後にしてください。',
            'note.max'               => '備考は500文字以内で入力してください。',
        ];
    }

    
    public function attributes(): array
    {
        return [
            'work_date' => '日付',
            'clock_in'  => '出勤時刻',
            'clock_out' => '退勤時刻',
            'note'      => '備考',
        ];
    }

    
    protected function prepareForValidation(): void
    {
        $norm = fn($v) => ($v === '' ? null : $v);
        $this->merge([
            'work_date' => $norm($this->input('work_date')),
            'clock_in'  => $norm($this->input('clock_in')),
            'clock_out' => $norm($this->input('clock_out')),
            'note'      => $norm($this->input('note')),
        ]);
    }
}

