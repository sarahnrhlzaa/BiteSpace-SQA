<?php

use PHPUnit\Framework\TestCase;

/**
 * LoginTest.php
 * PHPUnit automation test untuk fitur Login BiteSpace (CodeIgniter 4)
 *
 * Cara menjalankan:
 *   1. php spark serve --port=8081  (di folder BiteSpace)
 *   2. cd automation-php
 *   3. composer install
 *   4. composer test
 *
 * Kredensial default (UserSeeder.php):
 *   username: sarah  | password: sarah123
 *   username: neyza  | password: neyza123
 */
class LoginTest extends TestCase
{
    private string $baseUrl = 'http://localhost:8081';

    // ─────────────────────────────────────────────────────
    // Helper: POST request dengan cookie jar
    // ─────────────────────────────────────────────────────
    private function post(string $path, array $data, string $cookieJar = ''): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        if ($cookieJar) {
            curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieJar);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$httpCode, $response];
    }

    // ─────────────────────────────────────────────────────
    // Helper: GET request dengan cookie jar
    // ─────────────────────────────────────────────────────
    private function get(string $path, string $cookieJar = ''): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER,         true);
        if ($cookieJar) {
            curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieJar);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$httpCode, $response];
    }

    // ─────────────────────────────────────────────────────
    // Sanity check
    // ─────────────────────────────────────────────────────
    public function testTrue(): void
    {
        $this->assertTrue(true);
    }

    // TC-01: Server berjalan
    public function testServerBerjalan(): void
    {
        [$code, $response] = $this->get('/login');
        echo "\n[TC-01] GET /login → HTTP $code\n";
        $this->assertEquals(200, $code, "Server harus berjalan di port 8081.");
    }

    // TC-02: Login valid → masuk dashboard
    public function testLoginWithValidCredentials(): void
    {
        $cookieJar = tempnam(sys_get_temp_dir(), 'ci4_');
        [$code, $response] = $this->post('/login', [
            'username' => 'sarah',
            'password' => 'sarah123',
        ], $cookieJar);
        @unlink($cookieJar);

        echo "\n[TC-02] Login sarah/sarah123 → HTTP $code\n";
        echo "Ada 'Dashboard': " . (str_contains($response, 'Dashboard') ? 'Ya' : 'Tidak') . "\n";

        $this->assertEquals(200, $code);
        $this->assertStringContainsString('Dashboard', $response,
            "Setelah login harus tampil Dashboard.");
    }

    // TC-03: Login password salah → ditolak
    public function testLoginWithInvalidPassword(): void
    {
        [$code, $response] = $this->post('/login', [
            'username' => 'sarah',
            'password' => 'passwordSalah',
        ]);

        echo "\n[TC-03] Login password salah → HTTP $code\n";

        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Dashboard', $response,
            "Tidak boleh masuk Dashboard dengan password salah.");
    }

    // TC-04: Login username tidak ada → ditolak
    public function testLoginWithUnknownUsername(): void
    {
        [$code, $response] = $this->post('/login', [
            'username' => 'userTidakAda999',
            'password' => 'apapun',
        ]);

        echo "\n[TC-04] Login username tidak ada → HTTP $code\n";

        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Dashboard', $response,
            "Username tidak terdaftar tidak boleh masuk Dashboard.");
    }

    // TC-05: Login field kosong → ditolak
    public function testLoginWithEmptyFields(): void
    {
        [$code, $response] = $this->post('/login', [
            'username' => '',
            'password' => '',
        ]);

        echo "\n[TC-05] Login field kosong → HTTP $code\n";

        $this->assertEquals(200, $code);
        $this->assertStringNotContainsString('Dashboard', $response,
            "Field kosong tidak boleh masuk Dashboard.");
    }

    // TC-06: Akses dashboard tanpa login → redirect ke login
    public function testAccessDashboardWithoutLogin(): void
    {
        [$code, $response] = $this->get('/dashboard');

        echo "\n[TC-06] Akses /dashboard tanpa login → HTTP $code\n";
        echo "Ada 'Login': " . (str_contains($response, 'Login') ? 'Ya' : 'Tidak') . "\n";

        $this->assertEquals(200, $code);
        $this->assertStringContainsString('Login', $response,
            "Harus diarahkan ke halaman Login.");
    }

    // TC-07: Logout → kembali ke login
    public function testLogout(): void
    {
        $cookieJar = tempnam(sys_get_temp_dir(), 'ci4_');

        // Login dulu
        [$loginCode, $loginResponse] = $this->post('/login', [
            'username' => 'sarah',
            'password' => 'sarah123',
        ], $cookieJar);
        $this->assertStringContainsString('Dashboard', $loginResponse,
            "Harus login dulu sebelum test logout.");

        // Logout
        [$code, $response] = $this->get('/logout', $cookieJar);
        @unlink($cookieJar);

        echo "\n[TC-07] Logout → HTTP $code\n";
        echo "Ada 'Login': " . (str_contains($response, 'Login') ? 'Ya' : 'Tidak') . "\n";

        $this->assertEquals(200, $code);
        $this->assertStringContainsString('Login', $response,
            "Setelah logout harus kembali ke halaman Login.");
    }

    // TC-08: Akses menu tanpa login → redirect ke login
    public function testAccessMenuWithoutLogin(): void
    {
        [$code, $response] = $this->get('/menu');

        echo "\n[TC-08] Akses /menu tanpa login → HTTP $code\n";

        $this->assertEquals(200, $code);
        $this->assertStringContainsString('Login', $response,
            "Harus diarahkan ke Login jika belum login.");
    }

    // TC-09: Akses transaksi tanpa login → redirect ke login
    public function testAccessTransaksiWithoutLogin(): void
    {
        [$code, $response] = $this->get('/transaksi');

        echo "\n[TC-09] Akses /transaksi tanpa login → HTTP $code\n";

        $this->assertEquals(200, $code);
        $this->assertStringContainsString('Login', $response,
            "Harus diarahkan ke Login jika belum login.");
    }

    // TC-10: Login neyza valid → masuk dashboard
    public function testLoginNeyzaValid(): void
    {
        $cookieJar = tempnam(sys_get_temp_dir(), 'ci4_');
        [$code, $response] = $this->post('/login', [
            'username' => 'neyza',
            'password' => 'neyza123',
        ], $cookieJar);
        @unlink($cookieJar);

        echo "\n[TC-10] Login neyza/neyza123 → HTTP $code\n";
        echo "Ada 'Dashboard': " . (str_contains($response, 'Dashboard') ? 'Ya' : 'Tidak') . "\n";

        $this->assertEquals(200, $code);
        $this->assertStringContainsString('Dashboard', $response,
            "Login neyza harus berhasil masuk Dashboard.");
    }
}
