<?php

use PHPUnit\Framework\TestCase;

/**
 * DashboardTest.php
 * PHPUnit automation test untuk fitur Dashboard BiteSpace (CodeIgniter 4)
 */
class DashboardTest extends TestCase
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

    // Login dan follow redirect supaya session cookie tersimpan dengan benar
    private function loginAsAdmin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/login');
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query(['username' => 'sarah', 'password' => 'sarah123']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);   // ← follow 303 ke dashboard
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

    // TC-30: Dashboard tampil setelah login
    public function testDashboardTampilSetelahLogin(): void
    {
        [$code, $response] = $this->get('/dashboard');
        echo "\n[TC-30] GET /dashboard → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertStringContainsString('Dashboard', $response,
            "Halaman dashboard harus tampil setelah login.");
    }

    // TC-31: Dashboard tanpa login → redirect 302 (tidak follow redirect)
    public function testDashboardTanpaLogin(): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . '/dashboard');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);  // ← tangkap 302 aslinya
        curl_setopt($ch, CURLOPT_HEADER,         true);
        // Sengaja TANPA cookie jar supaya simulasi belum login
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "\n[TC-31] GET /dashboard tanpa login → HTTP $httpCode\n";
        $this->assertEquals(302, $httpCode,
            "Harus redirect 302 jika belum login.");
    }

    // TC-32: Dashboard punya konten statistik
    public function testDashboardAdaKonten(): void
    {
        [$code, $response] = $this->get('/dashboard');
        echo "\n[TC-32] GET /dashboard (cek konten) → HTTP $code\n";
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(1000, strlen($response),
            "Dashboard harus memiliki konten statistik.");
    }
}