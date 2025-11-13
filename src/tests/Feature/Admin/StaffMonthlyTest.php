<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffMonthlyTest extends TestCase
{
    use RefreshDatabase;

    private function makeUsers(): array
    {
        $admin = User::factory()->create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'role' => 1,
            'email_verified_at' => now(),
        ]);

        $staff = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        return [$admin, $staff];
    }

   
    public function test_スタッフ一覧を閲覧できる()
    {
        [$admin, $staff] = $this->makeUsers();

        $res = $this->actingAs($admin)->get(route('admin.staffs.index'));
        $res->assertStatus(200);
        $res->assertSee('山田太郎');
        $res->assertSee('taro@example.com');
    }

   
    public function test_スタッフ月次勤怠を閲覧できる()
    {
        [$admin, $staff] = $this->makeUsers();

        Attendance::factory()->create([
            'user_id'   => $staff->id,
            'work_date' => '2025-11-11',
            'clock_in'  => '2025-11-11 09:00:00',
            'clock_out' => '2025-11-11 18:00:00',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.staffs.monthly', $staff));
        $res->assertStatus(200);

        $html = $res->getContent();
      
        $this->assertTrue(
            str_contains($html, '2025年11月') ||
            str_contains($html, '2025-11')
        );

      
        $this->assertTrue(
            str_contains($html, '09:00') ||
            str_contains($html, '9:00')  ||
            str_contains($html, '09時')
        );
        $this->assertTrue(
            str_contains($html, '18:00') ||
            str_contains($html, '18時')
        );
    }

  
    public function test_CSV出力が成功する()
    {
         [$admin, $staff] = $this->makeUsers();

    \App\Models\Attendance::factory()->create([
        'user_id'   => $staff->id,
        'work_date' => '2025-11-11',
        'clock_in'  => '2025-11-11 09:00:00',
        'clock_out' => '2025-11-11 18:00:00',
    ]);

    $res = $this->actingAs($admin)->get(route('admin.staffs.monthly.csv', $staff));
    $res->assertStatus(200);

    
    $contentType = $res->headers->get('content-type') ?? '';
    $this->assertTrue(
        str_contains($contentType, 'text/csv') ||
        str_contains($contentType, 'application/octet-stream')
    );

   
    $raw = '';

    
    if (method_exists($res, 'streamedContent')) {
        $raw = $res->streamedContent();
    }

    
    if ($raw === '' && $res->baseResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
        ob_start();
        $res->baseResponse->sendContent();
        $raw = ob_get_clean();
    }

    
    if ($raw === '') {
        $raw = $res->getContent() ?? '';
    }
   

    
    $this->assertTrue($raw !== '', 'CSV body should not be empty');

    
    if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
        $raw = substr($raw, 3);
    }

    
    $candidates = ['UTF-8', 'SJIS-win', 'CP932', 'EUC-JP', 'ISO-2022-JP'];
    $csv = $raw;
    foreach ($candidates as $enc) {
        $converted = @mb_convert_encoding($raw, 'UTF-8', $enc);
        if ($converted !== false && $converted !== '') {
            $csv = $converted;
            break;
        }
    }

    
    $this->assertTrue(str_contains($csv, "\n") || str_contains($csv, "\r"));
    $this->assertTrue(str_contains($csv, ',') || str_contains($csv, "\t") || str_contains($csv, ';'));

   
    $header = strtok($csv, "\r\n");
    $this->assertNotEmpty($header);

    $this->assertTrue(
       
        str_contains($header, '日付') || str_contains($header, '出勤') || str_contains($header, '退勤')
        ||
      
        str_contains(strtolower($header), 'date') ||
        str_contains(strtolower($header), 'work_date') ||
        str_contains(strtolower($header), 'clock_in') ||
        str_contains(strtolower($header), 'clockout') ||
        str_contains(strtolower($header), 'clock_out') ||
        str_contains(strtolower($header), 'start') ||
        str_contains(strtolower($header), 'end')
    );

    $this->assertTrue(
    
    str_contains($csv, '2025-11-11') ||
    str_contains($csv, '"2025-11-11"') ||
    str_contains($csv, '2025/11/11') ||
    str_contains($csv, '2025年11月11日') ||
    str_contains($csv, '11/11') ||
    str_contains($csv, '11-11') ||
    str_contains($csv, '11日')
);

    $this->assertTrue(
        str_contains($csv, '09:00') || str_contains($csv, '9:00')
    );
    $this->assertTrue(
        str_contains($csv, '18:00') || str_contains($csv, '18時')
    );
    }
}


