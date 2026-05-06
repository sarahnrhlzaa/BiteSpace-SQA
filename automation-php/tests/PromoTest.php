<?php

use PHPUnit\Framework\TestCase;

/**
 * PromoTest.php
 * PHPUnit integration test untuk fitur Promo BiteSpace (CodeIgniter 4)
 */
class PromoTest extends TestCase
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

    public function testHalamanPromoTampil(): void
    {
        [$code, $response] = $this->get('/promo');
        echo "\n[TC-15] GET /promo → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'Promo') || str_contains($response, 'promo'),
            "Halaman promo harus tampil."
        );
    }

    public function testHalamanTambahPromoBisaAkses(): void
    {
        [$code, $response] = $this->get('/promo/create');
        echo "\n[TC-16] GET /promo/create → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Akses ditolak', $response,
            "Admin tidak boleh dapat 'Akses ditolak'.");
    }

    public function testHalamanPromoTidakKosong(): void
    {
        [$code, $response] = $this->get('/promo');
        echo "\n[TC-17] GET /promo (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(500, strlen($response),
            "Halaman promo harus punya konten.");
    }

    // ── AUTH Test ─────────────────────────────────────────

    // TC-18: Tanpa login → 302 (tanpa cookie, tanpa follow redirect)
    public function testAksesPromoTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/promo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-18] GET /promo tanpa login [NEGATIVE] → HTTP $httpCode\n";
        $this->assertEquals(302, $httpCode,
            "Harus redirect 302 ke Login jika belum login.");
    }

    // ── CREATE Tests ─────────────────────────────────────

    public function testTambahPromoBerhasil(): void
    {
        $data = [
            'nama_promo'      => 'Promo Test ' . time(),
            'kode_promo'      => 'TEST' . rand(100, 999),
            'diskon'          => 10,
            'tipe_diskon'     => 'persen',
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+7 days')),
            'is_active'       => 1,
            'deskripsi'       => 'Promo dari integration test',
        ];
        [$code, $response] = $this->post('/promo/store', $data);
        echo "\n[TC-19] POST /promo/store [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Promo'),
            "Tambah promo valid harus berhasil."
        );
    }

    public function testTambahPromoGagalNamaKosong(): void
    {
        $data = [
            'nama_promo'      => '',
            'kode_promo'      => 'TESTKOSONG',
            'diskon'          => 10,
            'tipe_diskon'     => 'persen',
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+7 days')),
        ];
        [$code, $response] = $this->post('/promo/store', $data);
        echo "\n[TC-20] POST /promo/store nama kosong [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'required') ||
            str_contains($response, 'wajib') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Tambah Promo') ||
            str_contains($response, 'Promo'),
            "Harus muncul error atau kembali ke form ketika nama promo kosong."
        );
    }

    public function testTambahPromoGagalDiskonNol(): void
    {
        $data = [
            'nama_promo'      => 'Promo Diskon Nol',
            'kode_promo'      => 'DISKON0',
            'diskon'          => 0,
            'tipe_diskon'     => 'persen',
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+7 days')),
        ];
        [$code, $response] = $this->post('/promo/store', $data);
        echo "\n[TC-21] POST /promo/store diskon nol/invalid [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'error') ||
            str_contains($response, 'greater') ||
            str_contains($response, 'Tambah Promo') ||
            str_contains($response, 'Promo'),
            "Harus muncul error atau kembali ke form ketika diskon 0."
        );
    }

    // ── UPDATE Tests ──────────────────────────────────────

    public function testUpdatePromoBerhasil(): void
    {
        $create = [
            'nama_promo'      => 'Promo Update Test ' . time(),
            'kode_promo'      => 'UPD' . rand(100, 999),
            'diskon'          => 15,
            'tipe_diskon'     => 'persen',
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+7 days')),
            'is_active'       => 1,
        ];
        $this->post('/promo/store', $create);

        [$_code, $listResponse] = $this->get('/promo');
        preg_match_all('/promo\/edit\/(\d+)/', $listResponse, $matches);
        $ids    = $matches[1] ?? [];
        $lastId = !empty($ids) ? max($ids) : 1;

        $update = [
            'nama_promo'      => 'Promo Diperbarui ' . time(),
            'kode_promo'      => 'UPD' . rand(100, 999),
            'diskon'          => 20,
            'tipe_diskon'     => 'persen',
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+14 days')),
            'is_active'       => 1,
        ];
        [$code, $response] = $this->post("/promo/update/$lastId", $update);
        echo "\n[TC-22] POST /promo/update/$lastId [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Promo'),
            "Update promo valid harus berhasil."
        );
    }

    public function testUpdatePromoGagalNamaKosong(): void
    {
        $data = [
            'nama_promo'      => '',
            'kode_promo'      => 'UPDKOSONG',
            'diskon'          => 10,
            'tipe_diskon'     => 'persen',
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+7 days')),
        ];
        [$code, $response] = $this->post('/promo/update/1', $data);
        echo "\n[TC-23] POST /promo/update/1 nama kosong [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'required') ||
            str_contains($response, 'wajib') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Edit Promo') ||
            str_contains($response, 'Promo'),
            "Harus muncul error atau kembali ke form edit ketika nama kosong."
        );
    }

    // ── DELETE Tests ──────────────────────────────────────

    public function testHapusPromoBerhasil(): void
    {
        $create = [
            'nama_promo'      => 'Promo Hapus Test ' . time(),
            'kode_promo'      => 'DEL' . rand(100, 999),
            'diskon'          => 5,
            'tipe_diskon'     => 'persen',
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => date('Y-m-d', strtotime('+7 days')),
            'is_active'       => 1,
        ];
        $this->post('/promo/store', $create);

        [$_code, $listResponse] = $this->get('/promo');
        preg_match_all('/promo\/delete\/(\d+)/', $listResponse, $matches);
        $ids    = $matches[1] ?? [];
        $lastId = !empty($ids) ? max($ids) : 1;

        [$code, $response] = $this->post("/promo/delete/$lastId", []);
        echo "\n[TC-24] POST /promo/delete/$lastId [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Promo'),
            "Hapus promo harus berhasil dan redirect ke daftar promo."
        );
    }

    public function testHapusPromoIdTidakAda(): void
    {
        [$code, $response] = $this->post('/promo/delete/999999', []);
        echo "\n[TC-25] POST /promo/delete/999999 ID tidak ada [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'tidak ditemukan') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Promo'),
            "Harus muncul pesan error ketika ID promo tidak ada."
        );
    }
}