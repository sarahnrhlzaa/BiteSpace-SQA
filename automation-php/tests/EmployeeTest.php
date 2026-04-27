<?php

use PHPUnit\Framework\TestCase;

/**
 * EmployeeTest.php
 * PHPUnit automation test untuk fitur Manajemen Employee BiteSpace (CodeIgniter 4)
 */
class EmployeeTest extends TestCase
{
    private string $baseUrl   = 'http://localhost:8081';
    private string $cookieJar = '';

    protected function setUp(): void
    {
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'ci4_');
        $this->loginAsAdmin();
    }

    protected function tearDown(): void
    {
        @unlink($this->cookieJar);
    }

    private function loginAsAdmin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query(['username' => 'sarah', 'password' => 'sarah123']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR,      $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $this->cookieJar);
        curl_exec($ch);
        curl_close($ch);
    }

    private function get(string $path): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        curl_setopt($ch, CURLOPT_COOKIEJAR,      $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $this->cookieJar);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$httpCode, $response];
    }

    // TC-23: Halaman daftar employee tampil
    public function testHalamanEmployeeTampil(): void
    {
        [$code, $response] = $this->get('/employee');
        echo "\n[TC-23] GET /employee → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'Employee') || str_contains($response, 'Karyawan'),
            "Halaman employee harus tampil."
        );
    }

    // TC-24: Halaman tambah employee bisa diakses
    public function testHalamanTambahEmployeeBisaAkses(): void
    {
        [$code, $response] = $this->get('/employee/create');
        echo "\n[TC-24] GET /employee/create → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'Tambah') || str_contains($response, 'Employee'),
            "Halaman tambah employee harus tampil."
        );
    }

    // TC-25: Daftar employee ada data sarah dan neyza
    public function testDaftarEmployeeAdaData(): void
    {
        [$code, $response] = $this->get('/employee');
        echo "\n[TC-25] GET /employee (cek data) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'sarah') || str_contains($response, 'neyza') || str_contains($response, 'Sarah'),
            "Harus ada minimal 1 data employee."
        );
    }

    // TC-26: Akses employee tanpa login → redirect ke login
    public function testAksesEmployeeTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/employee');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-26] GET /employee tanpa login → HTTP $httpCode\n";
        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('Login', $response,
            "Harus diarahkan ke Login jika belum login.");
    }
}
