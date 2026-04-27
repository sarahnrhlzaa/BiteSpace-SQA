<?php

use PHPUnit\Framework\TestCase;

/**
 * PromoTest.php
 * PHPUnit automation test untuk fitur Promo BiteSpace (CodeIgniter 4)
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

    // TC-15: Halaman daftar promo tampil
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

    // TC-16: Halaman tambah promo bisa diakses admin
    public function testHalamanTambahPromoBisaAkses(): void
    {
        [$code, $response] = $this->get('/promo/create');
        echo "\n[TC-16] GET /promo/create → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Akses ditolak', $response,
            "Admin tidak boleh dapat 'Akses ditolak'.");
    }

    // TC-17: Halaman promo tidak kosong
    public function testHalamanPromoTidakKosong(): void
    {
        [$code, $response] = $this->get('/promo');
        echo "\n[TC-17] GET /promo (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(500, strlen($response),
            "Halaman promo harus punya konten.");
    }

    // TC-18: Akses promo tanpa login → redirect ke login
    public function testAksesPromoTanpaLogin(): void
    {
        // Request tanpa cookie
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/promo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-18] GET /promo tanpa login → HTTP $httpCode\n";
        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('Login', $response,
            "Harus diarahkan ke Login jika belum login.");
    }
}
