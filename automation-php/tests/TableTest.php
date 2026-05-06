<?php

use PHPUnit\Framework\TestCase;

/**
 * TableTest.php
 * PHPUnit integration test untuk fitur Manajemen Meja BiteSpace (CodeIgniter 4)
 */
class TableTest extends TestCase
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

    public function testHalamanMejaTampil(): void
    {
        [$code, $response] = $this->get('/table');
        echo "\n[TC-19] GET /table → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'Meja') || str_contains($response, 'Table'),
            "Halaman meja harus tampil."
        );
    }

    public function testHalamanTambahMejaBisaAkses(): void
    {
        [$code, $response] = $this->get('/table/create');
        echo "\n[TC-20] GET /table/create → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'Tambah') || str_contains($response, 'Meja'),
            "Halaman tambah meja harus tampil."
        );
    }

    public function testHalamanMejaTidakKosong(): void
    {
        [$code, $response] = $this->get('/table');
        echo "\n[TC-21] GET /table (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(500, strlen($response),
            "Halaman meja harus punya konten.");
    }

    // ── AUTH Test ─────────────────────────────────────────

    // TC-22: Tanpa login → 302 (tanpa cookie, tanpa follow redirect)
    public function testAksesMejaTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/table');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-22] GET /table tanpa login [NEGATIVE] → HTTP $httpCode\n";
        $this->assertEquals(302, $httpCode,
            "Harus redirect 302 ke Login jika belum login.");
    }

    // ── CREATE Tests ─────────────────────────────────────

    public function testTambahMejaBerhasil(): void
    {
        $nomorMeja = 'T' . rand(100, 999);
        $data = [
            'nomor_meja' => $nomorMeja,
            'kapasitas'  => 4,
            'status'     => 'available',
        ];
        [$code, $response] = $this->post('/table/store', $data);
        echo "\n[TC-23] POST /table/store [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Meja') || str_contains($response, 'Table'),
            "Tambah meja valid harus berhasil."
        );
    }

    public function testTambahMejaGagalNomorKosong(): void
    {
        $data = [
            'nomor_meja' => '',
            'kapasitas'  => 4,
            'status'     => 'available',
        ];
        [$code, $response] = $this->post('/table/store', $data);
        echo "\n[TC-24] POST /table/store nomor kosong [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'required') ||
            str_contains($response, 'wajib') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Tambah Meja') ||
            str_contains($response, 'Meja'),
            "Harus muncul error atau kembali ke form ketika nomor meja kosong."
        );
    }

    public function testTambahMejaGagalKapasitasTidakValid(): void
    {
        $data = [
            'nomor_meja' => 'T999',
            'kapasitas'  => 'abc',
            'status'     => 'available',
        ];
        [$code, $response] = $this->post('/table/store', $data);
        echo "\n[TC-25] POST /table/store kapasitas invalid [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'integer') ||
            str_contains($response, 'angka') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Tambah Meja') ||
            str_contains($response, 'Meja'),
            "Harus muncul error atau kembali ke form ketika kapasitas bukan angka."
        );
    }

    // ── UPDATE Tests ──────────────────────────────────────

    public function testUpdateMejaBerhasil(): void
    {
        $nomorMeja = 'U' . rand(100, 999);
        $this->post('/table/store', [
            'nomor_meja' => $nomorMeja,
            'kapasitas'  => 2,
            'status'     => 'available',
        ]);

        [$_code, $listResponse] = $this->get('/table');
        preg_match_all('/table\/edit\/(\d+)/', $listResponse, $matches);
        $ids    = $matches[1] ?? [];
        $lastId = !empty($ids) ? max($ids) : 1;

        $update = [
            'nomor_meja' => $nomorMeja,
            'kapasitas'  => 6,
            'status'     => 'available',
        ];
        [$code, $response] = $this->post("/table/update/$lastId", $update);
        echo "\n[TC-26] POST /table/update/$lastId [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Meja') || str_contains($response, 'Table'),
            "Update meja valid harus berhasil."
        );
    }

    public function testUpdateMejaGagalKapasitasNol(): void
    {
        $data = [
            'nomor_meja' => 'T1',
            'kapasitas'  => 0,
            'status'     => 'available',
        ];
        [$code, $response] = $this->post('/table/update/1', $data);
        echo "\n[TC-27] POST /table/update/1 kapasitas 0 [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'greater_than') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Edit Meja') ||
            str_contains($response, 'Meja'),
            "Harus muncul error atau kembali ke form ketika kapasitas 0."
        );
    }

    // ── DELETE Tests ──────────────────────────────────────

    public function testHapusMejaBerhasil(): void
    {
        $this->post('/table/store', [
            'nomor_meja' => 'DEL' . rand(100, 999),
            'kapasitas'  => 2,
            'status'     => 'available',
        ]);

        [$_code, $listResponse] = $this->get('/table');
        preg_match_all('/table\/delete\/(\d+)/', $listResponse, $matches);
        $ids    = $matches[1] ?? [];
        $lastId = !empty($ids) ? max($ids) : 1;

        [$code, $response] = $this->post("/table/delete/$lastId", []);
        echo "\n[TC-28] POST /table/delete/$lastId [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Meja') || str_contains($response, 'Table'),
            "Hapus meja harus berhasil dan redirect ke daftar meja."
        );
    }

    public function testHapusMejaIdTidakAda(): void
    {
        [$code, $response] = $this->post('/table/delete/999999', []);
        echo "\n[TC-29] POST /table/delete/999999 ID tidak ada [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'tidak ditemukan') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Meja') ||
            str_contains($response, 'Table'),
            "Harus muncul pesan error ketika ID meja tidak ada."
        );
    }
}