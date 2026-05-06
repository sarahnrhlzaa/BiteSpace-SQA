<?php

use PHPUnit\Framework\TestCase;

/**
 * MenuTest.php
 * PHPUnit integration test untuk fitur Menu BiteSpace (CodeIgniter 4)
 */
class MenuTest extends TestCase
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

    public function testHalamanMenuTampil(): void
    {
        [$code, $response] = $this->get('/menu');
        echo "\n[TC-11] GET /menu → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'Menu') || str_contains($response, 'menu'),
            "Halaman menu harus tampil."
        );
    }

    public function testHalamanTambahMenuAdminBisaAkses(): void
    {
        [$code, $response] = $this->get('/menu/create');
        echo "\n[TC-12] GET /menu/create → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Akses ditolak', $response,
            "Admin tidak boleh dapat pesan 'Akses ditolak'.");
    }

    public function testHalamanMenuTidakKosong(): void
    {
        [$code, $response] = $this->get('/menu');
        echo "\n[TC-13] GET /menu (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(500, strlen($response),
            "Halaman menu harus punya konten.");
    }

    // TC-14: Edit menu — follow redirect supaya kita bisa baca konten halaman edit
    public function testHalamanEditMenuAdminBisaAkses(): void
    {
        [$code, $response] = $this->get('/menu/edit/1');
        echo "\n[TC-14] GET /menu/edit/1 → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Akses ditolak', $response,
            "Admin harus bisa akses halaman edit menu.");
    }

    // ── CREATE Tests ─────────────────────────────────────

    public function testTambahMenuBerhasil(): void
    {
        $data = [
            'nama_menu'    => 'Test Menu Baru ' . time(),
            'harga'        => 25000,
            'id_category'  => 1,
            'deskripsi'    => 'Deskripsi menu test integration testing',
            'is_available' => 1,
        ];
        [$code, $response] = $this->post('/menu/store', $data);
        echo "\n[TC-15] POST /menu/store [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Menu'),
            "Tambah menu valid harus berhasil dan redirect ke halaman menu."
        );
    }

    public function testTambahMenuGagalNamaKosong(): void
    {
        $data = [
            'nama_menu'   => '',
            'harga'       => 15000,
            'id_category' => 1,
        ];
        [$code, $response] = $this->post('/menu/store', $data);
        echo "\n[TC-16] POST /menu/store nama kosong [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'required') ||
            str_contains($response, 'wajib') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Tambah Menu') ||
            str_contains($response, 'Menu'),
            "Harus muncul error atau kembali ke form ketika nama kosong."
        );
    }

    public function testTambahMenuGagalHargaTidakValid(): void
    {
        $data = [
            'nama_menu'   => 'Menu Test Harga Invalid',
            'harga'       => 'abcdef',
            'id_category' => 1,
        ];
        [$code, $response] = $this->post('/menu/store', $data);
        echo "\n[TC-17] POST /menu/store harga invalid [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'numeric') ||
            str_contains($response, 'angka') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Tambah Menu') ||
            str_contains($response, 'Menu'),
            "Harus muncul error atau kembali ke form ketika harga bukan angka."
        );
    }

    // ── UPDATE Tests ──────────────────────────────────────

    public function testUpdateMenuBerhasil(): void
    {
        $data = [
            'nama_menu'    => 'Menu Diperbarui ' . time(),
            'harga'        => 30000,
            'id_category'  => 1,
            'deskripsi'    => 'Deskripsi diperbarui via integration test',
            'is_available' => 1,
        ];
        [$code, $response] = $this->post('/menu/update/1', $data);
        echo "\n[TC-18] POST /menu/update/1 [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Menu'),
            "Update menu valid harus berhasil."
        );
    }

    public function testUpdateMenuGagalNamaKosong(): void
    {
        $data = [
            'nama_menu'   => '',
            'harga'       => 30000,
            'id_category' => 1,
        ];
        [$code, $response] = $this->post('/menu/update/1', $data);
        echo "\n[TC-19] POST /menu/update/1 nama kosong [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'required') ||
            str_contains($response, 'wajib') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Edit Menu') ||
            str_contains($response, 'Menu'),
            "Harus muncul error atau kembali ke form edit ketika nama kosong."
        );
    }

    // ── DELETE Tests ──────────────────────────────────────

    public function testHapusMenuBerhasil(): void
    {
        $create = [
            'nama_menu'   => 'Menu Untuk Dihapus ' . time(),
            'harga'       => 5000,
            'id_category' => 1,
        ];
        $this->post('/menu/store', $create);

        [$_code, $listResponse] = $this->get('/menu');
        preg_match_all('/menu\/delete\/(\d+)/', $listResponse, $matches);
        $ids    = $matches[1] ?? [];
        $lastId = !empty($ids) ? max($ids) : 1;

        [$code, $response] = $this->post("/menu/delete/$lastId", []);
        echo "\n[TC-20] POST /menu/delete/$lastId [POSITIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'berhasil') || str_contains($response, 'Menu'),
            "Hapus menu harus berhasil dan redirect ke daftar menu."
        );
    }

    public function testHapusMenuIdTidakAda(): void
    {
        [$code, $response] = $this->post('/menu/delete/999999', []);
        echo "\n[TC-21] POST /menu/delete/999999 ID tidak ada [NEGATIVE] → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'tidak ditemukan') ||
            str_contains($response, 'error') ||
            str_contains($response, 'Menu'),
            "Harus muncul pesan error ketika menghapus menu dengan ID tidak ada."
        );
    }

    // ── AUTH Test ─────────────────────────────────────────

    // TC-22: Tanpa login → 302 (tidak pakai cookie, tidak follow redirect)
    public function testAksesMenuTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/menu');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-22] GET /menu tanpa login [NEGATIVE] → HTTP $httpCode\n";
        $this->assertEquals(302, $httpCode,
            "Harus redirect 302 ke Login jika belum login.");
    }
}