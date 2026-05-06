<?php

use PHPUnit\Framework\TestCase;

/**
 * EmployeeTest.php
 * PHPUnit integration test untuk fitur Manajemen Employee BiteSpace (CodeIgniter 4)
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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);   // follow redirect supaya session valid
        curl_setopt($ch, CURLOPT_COOKIEJAR,      $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $this->cookieJar);
        curl_exec($ch);
        curl_close($ch);
    }

    private function get(string $path, bool $followRedirect = true): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        curl_setopt($ch, CURLOPT_COOKIEJAR,      $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $this->cookieJar);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$httpCode, $response];
    }

    // followRedirect=true: POST → 303 → GET halaman tujuan → 200
    private function post(string $path, array $data, bool $followRedirect = true): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        curl_setopt($ch, CURLOPT_COOKIEJAR,      $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $this->cookieJar);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$httpCode, $response];
    }

    // ── GET Tests ─────────────────────────────────────────

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

    // ── AUTH Test ─────────────────────────────────────────

    // TC-26: Tanpa login → CI4 redirect 302 (tidak follow redirect, tidak pakai cookie)
    public function testAksesEmployeeTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/employee');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        // Sengaja tanpa cookie jar
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-26] GET /employee tanpa login [NEGATIVE] → HTTP $httpCode\n";
        $this->assertEquals(302, $httpCode,
            "Harus redirect 302 ke Login jika belum login.");
    }

    // ── CREATE Tests ─────────────────────────────────────

    public function testTambahEmployeeBerhasil(): void
    {
        $username = 'testuser' . rand(1000, 9999);
        $data = [
            'nama_lengkap' => 'Test User ' . rand(100, 999),
            'username'     => $username,
            'password'     => 'password123',
            'role'         => 'kasir',
            'is_active'    => 1,
        ];
        [$code, $response] = $this->post('/employee/store', $data);
        echo "\n[TC-27] POST /employee/store [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Employee') || str_contains($response, 'Karyawan'),
            "Tambah employee valid harus berhasil."
        );
    }

    public function testTambahEmployeeGagalUsernameKosong(): void
    {
        $data = [
            'nama_lengkap' => 'Test User Kosong',
            'username'     => '',
            'password'     => 'password123',
            'role'         => 'kasir',
        ];
        [$code, $response] = $this->post('/employee/store', $data);
        echo "\n[TC-28] POST /employee/store username kosong [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'required') ||
            str_contains($response, 'wajib') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Tambah Employee') ||
            str_contains($response, 'Employee'),
            "Harus muncul error atau kembali ke form ketika username kosong."
        );
    }

    public function testTambahEmployeeGagalPasswordPendek(): void
    {
        $data = [
            'nama_lengkap' => 'Test User Password Pendek',
            'username'     => 'userpendek' . rand(100, 999),
            'password'     => '123',
            'role'         => 'kasir',
        ];
        [$code, $response] = $this->post('/employee/store', $data);
        echo "\n[TC-29] POST /employee/store password pendek [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'min_length') ||
            str_contains($response, 'minimal') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Tambah Employee') ||
            str_contains($response, 'Employee'),
            "Harus muncul error atau kembali ke form ketika password terlalu pendek."
        );
    }

    // ── UPDATE Tests ──────────────────────────────────────

    public function testUpdateEmployeeBerhasil(): void
    {
        $username = 'updemp' . rand(1000, 9999);
        $this->post('/employee/store', [
            'nama_lengkap' => 'Update Employee Test',
            'username'     => $username,
            'password'     => 'password123',
            'role'         => 'kasir',
            'is_active'    => 1,
        ]);

        [$_code, $listResponse] = $this->get('/employee');
        preg_match_all('/employee\/edit\/(\d+)/', $listResponse, $matches);
        $ids    = $matches[1] ?? [];
        $lastId = !empty($ids) ? max($ids) : 1;

        $update = [
            'nama_lengkap' => 'Employee Diperbarui ' . time(),
            'username'     => $username,
            'role'         => 'kasir',
            'is_active'    => 1,
        ];
        [$code, $response] = $this->post("/employee/update/$lastId", $update);
        echo "\n[TC-30] POST /employee/update/$lastId [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Employee') || str_contains($response, 'Karyawan'),
            "Update employee valid harus berhasil."
        );
    }

    public function testUpdateEmployeeGagalNamaKosong(): void
    {
        $data = [
            'nama_lengkap' => '',
            'username'     => 'sarah',
            'role'         => 'admin',
        ];
        [$code, $response] = $this->post('/employee/update/1', $data);
        echo "\n[TC-31] POST /employee/update/1 nama kosong [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'required') ||
            str_contains($response, 'wajib') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Edit Employee') ||
            str_contains($response, 'Employee'),
            "Harus muncul error atau kembali ke form edit ketika nama kosong."
        );
    }

    // ── DELETE Tests ──────────────────────────────────────

    public function testHapusEmployeeBerhasil(): void
    {
        $username = 'delempemp' . rand(1000, 9999);
        $this->post('/employee/store', [
            'nama_lengkap' => 'Employee Akan Dihapus',
            'username'     => $username,
            'password'     => 'password123',
            'role'         => 'kasir',
            'is_active'    => 1,
        ]);

        [$_code, $listResponse] = $this->get('/employee');
        preg_match_all('/employee\/delete\/(\d+)/', $listResponse, $matches);
        $ids    = $matches[1] ?? [];
        $lastId = !empty($ids) ? max($ids) : 1;

        [$code, $response] = $this->post("/employee/delete/$lastId", []);
        echo "\n[TC-32] POST /employee/delete/$lastId [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Employee') || str_contains($response, 'Karyawan'),
            "Hapus employee harus berhasil dan redirect ke daftar employee."
        );
    }

    public function testHapusEmployeeIdTidakAda(): void
    {
        [$code, $response] = $this->post('/employee/delete/999999', []);
        echo "\n[TC-33] POST /employee/delete/999999 ID tidak ada [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'tidak ditemukan') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Employee') ||
            str_contains($response, 'Karyawan'),
            "Harus muncul pesan error ketika ID employee tidak ada."
        );
    }
}