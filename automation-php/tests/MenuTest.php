<?php

use PHPUnit\Framework\TestCase;

/**
 * MenuTest.php
 * PHPUnit automation test untuk fitur Menu BiteSpace (CodeIgniter 4)
 */
class MenuTest extends TestCase
{
    private string $baseUrl  = 'http://localhost:8081';
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

    // TC-11: Halaman daftar menu tampil
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

    // TC-12: Halaman tambah menu bisa diakses admin
    public function testHalamanTambahMenuAdminBisaAkses(): void
    {
        [$code, $response] = $this->get('/menu/create');
        echo "\n[TC-12] GET /menu/create → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Akses ditolak', $response,
            "Admin tidak boleh dapat pesan 'Akses ditolak'.");
    }

    // TC-13: Halaman menu tidak kosong
    public function testHalamanMenuTidakKosong(): void
    {
        [$code, $response] = $this->get('/menu');
        echo "\n[TC-13] GET /menu (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(500, strlen($response),
            "Halaman menu harus punya konten.");
    }

    // TC-14: Halaman edit menu bisa diakses admin (id=1)
    public function testHalamanEditMenuAdminBisaAkses(): void
    {
        [$code, $response] = $this->get('/menu/edit/1');
        echo "\n[TC-14] GET /menu/edit/1 → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Akses ditolak', $response,
            "Admin harus bisa akses halaman edit menu.");
    }
}
