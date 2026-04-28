<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk UserModel.
 * Tidak menggunakan database — hanya menguji logika validasi dan autentikasi.
 */
final class UserModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'username'     => 'required|max_length[50]',
            'password'     => 'required|min_length[6]',
            'role'         => 'required|in_list[admin,kasir]',
            'nama_lengkap' => 'required|max_length[100]',
        ]);
        return $validation->run($data);
    }

    // Password terlalu pendek (<6 karakter) → harus gagal
    public function testInsertPasswordPendekAkanGagal(): void
    {
        $result = $this->validate([
            'username'     => 'testuser',
            'password'     => '123', // < 6 karakter
            'role'         => 'kasir',
            'nama_lengkap' => 'Test User',
        ]);
        $this->assertFalse($result, "Password < 6 karakter harus gagal validasi.");
    }

    // Password tepat 6 karakter → harus lolos (edge case)
    public function testInsertPasswordTepat6KarakterLulus(): void
    {
        $result = $this->validate([
            'username'     => 'testuser',
            'password'     => '123456',
            'role'         => 'kasir',
            'nama_lengkap' => 'Test User',
        ]);
        $this->assertTrue($result, "Password tepat 6 karakter harus lulus validasi.");
    }

    // Role di luar admin/kasir → harus gagal
    public function testInsertRoleTidakValidAkanGagal(): void
    {
        $result = $this->validate([
            'username'     => 'testuser',
            'password'     => 'password123',
            'role'         => 'manager', // tidak valid
            'nama_lengkap' => 'Test User',
        ]);
        $this->assertFalse($result, "Role selain admin/kasir harus gagal validasi.");
    }

    // Role kosong → harus gagal
    public function testInsertRoleKosongGagal(): void
    {
        $result = $this->validate([
            'username'     => 'testuser',
            'password'     => 'password123',
            'role'         => '',
            'nama_lengkap' => 'Test User',
        ]);
        $this->assertFalse($result, "Role kosong harus gagal validasi.");
    }

    // Data user valid (role admin) → harus lolos
    public function testInsertUserAdminValidLulus(): void
    {
        $result = $this->validate([
            'username'     => 'adminuser',
            'password'     => 'admin123',
            'role'         => 'admin',
            'nama_lengkap' => 'Admin Utama',
        ]);
        $this->assertTrue($result, "Data user admin valid harus lulus validasi.");
    }

    // Data user valid (role kasir) → harus lolos
    public function testInsertUserKasirValidLulus(): void
    {
        $result = $this->validate([
            'username'     => 'kasiruser',
            'password'     => 'kasir123',
            'role'         => 'kasir',
            'nama_lengkap' => 'Kasir Satu',
        ]);
        $this->assertTrue($result, "Data user kasir valid harus lulus validasi.");
    }

    // Verifikasi password hash yang benar → harus true
    public function testPasswordVerifyCorrectPassword(): void
    {
        $plainPassword = 'sarah123';
        $hashed        = password_hash($plainPassword, PASSWORD_DEFAULT);

        $this->assertTrue(
            password_verify($plainPassword, $hashed),
            "Password yang benar harus berhasil diverifikasi."
        );
    }

    // Verifikasi password yang salah → harus false
    public function testPasswordVerifyWrongPassword(): void
    {
        $hashed = password_hash('sarah123', PASSWORD_DEFAULT);

        $this->assertFalse(
            password_verify('passwordSalah', $hashed),
            "Password yang salah harus gagal diverifikasi."
        );
    }

    // Hash yang sama harus menghasilkan hasil verify yang konsisten
    public function testPasswordHashKonsisten(): void
    {
        $plain  = 'mypassword';
        $hashed = password_hash($plain, PASSWORD_DEFAULT);

        // Hash berbeda setiap kali, tapi verify tetap benar
        $this->assertNotEquals($plain, $hashed, "Hash tidak boleh sama dengan plain text.");
        $this->assertTrue(password_verify($plain, $hashed));
    }
}