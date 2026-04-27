<?php

use PHPUnit\Framework\TestCase;

/**
 * TableTest.php
 * PHPUnit automation test untuk fitur Manajemen Meja BiteSpace (CodeIgniter 4)
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

    // TC-19: Halaman daftar meja tampil
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

    // TC-20: Halaman tambah meja bisa diakses
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

    // TC-21: Halaman meja tidak kosong
    public function testHalamanMejaTidakKosong(): void
    {
        [$code, $response] = $this->get('/table');
        echo "\n[TC-21] GET /table (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(500, strlen($response),
            "Halaman meja harus punya konten.");
    }

    // TC-22: Akses meja tanpa login → redirect ke login
    public function testAksesMejaTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/table');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-22] GET /table tanpa login → HTTP $httpCode\n";
        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('Login', $response,
            "Harus diarahkan ke Login jika belum login.");
    }
}
