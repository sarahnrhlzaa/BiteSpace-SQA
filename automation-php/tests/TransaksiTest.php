<?php

use PHPUnit\Framework\TestCase;

/**
 * TransaksiTest.php
 * PHPUnit automation test untuk fitur Transaksi BiteSpace (CodeIgniter 4)
 */
class TransaksiTest extends TestCase
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

    // TC-27: Halaman transaksi tampil
    public function testHalamanTransaksiTampil(): void
    {
        [$code, $response] = $this->get('/transaksi');
        echo "\n[TC-27] GET /transaksi → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertTrue(
            str_contains($response, 'Transaksi') || str_contains($response, 'POS') || str_contains($response, 'Menu'),
            "Halaman transaksi harus tampil."
        );
    }

    // TC-28: Halaman transaksi punya konten menu
    public function testHalamanTransaksiAdaMenu(): void
    {
        [$code, $response] = $this->get('/transaksi');
        echo "\n[TC-28] GET /transaksi (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(1000, strlen($response),
            "Halaman transaksi harus memiliki konten daftar menu.");
    }

    // TC-29: Tanpa login → redirect 302 (tanpa cookie, tanpa follow redirect)
    public function testAksesTransaksiTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/transaksi');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        // Sengaja tanpa cookie jar
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-29] GET /transaksi tanpa login → HTTP $httpCode\n";
        $this->assertEquals(302, $httpCode,
            "Harus redirect 302 ke Login jika belum login.");
    }
}