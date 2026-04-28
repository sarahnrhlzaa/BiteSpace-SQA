<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk logika validasi form dan logika sesi/role.
 * Tidak menggunakan database — murni menguji rule validasi CodeIgniter dan logika PHP.
 */
final class ValidationLogicTest extends CIUnitTestCase
{
    private function validate(array $rules, array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules($rules);
        return $validation->run($data);
    }

    // =========================================================
    // LOGIN VALIDATION
    // =========================================================

    private function loginRules(): array
    {
        return [
            'username' => 'required',
            'password' => 'required',
        ];
    }

    public function testLoginValidData(): void
    {
        $result = $this->validate($this->loginRules(), [
            'username' => 'sarah',
            'password' => 'sarah123',
        ]);
        $this->assertTrue($result, "Data login valid harus lulus validasi.");
    }

    public function testLoginUsernameKosong(): void
    {
        $result = $this->validate($this->loginRules(), [
            'username' => '',
            'password' => 'sarah123',
        ]);
        $this->assertFalse($result, "Username kosong harus gagal validasi.");
    }

    public function testLoginPasswordKosong(): void
    {
        $result = $this->validate($this->loginRules(), [
            'username' => 'sarah',
            'password' => '',
        ]);
        $this->assertFalse($result, "Password kosong harus gagal validasi.");
    }

    public function testLoginSemuaFieldKosong(): void
    {
        $result = $this->validate($this->loginRules(), [
            'username' => '',
            'password' => '',
        ]);
        $this->assertFalse($result, "Semua field kosong harus gagal validasi.");
    }

    // =========================================================
    // MENU VALIDATION
    // =========================================================

    private function menuRules(): array
    {
        return [
            'nama_menu'   => 'required|max_length[150]',
            'harga'       => 'required|numeric|greater_than_equal_to[0]',
            'id_category' => 'required|integer',
        ];
    }

    public function testMenuValidData(): void
    {
        $result = $this->validate($this->menuRules(), [
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => 25000,
            'id_category' => 1,
        ]);
        $this->assertTrue($result, "Data menu valid harus lulus validasi.");
    }

    public function testMenuNamaKosong(): void
    {
        $result = $this->validate($this->menuRules(), [
            'nama_menu'   => '',
            'harga'       => 25000,
            'id_category' => 1,
        ]);
        $this->assertFalse($result, "nama_menu kosong harus gagal validasi.");
    }

    public function testMenuHargaNegatif(): void
    {
        $result = $this->validate($this->menuRules(), [
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => -5000,
            'id_category' => 1,
        ]);
        $this->assertFalse($result, "Harga negatif harus gagal validasi.");
    }

    public function testMenuHargaBukanAngka(): void
    {
        $result = $this->validate($this->menuRules(), [
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => 'dua-puluh-ribu',
            'id_category' => 1,
        ]);
        $this->assertFalse($result, "Harga berupa teks harus gagal validasi.");
    }

    public function testMenuIdCategoryBukanInteger(): void
    {
        $result = $this->validate($this->menuRules(), [
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => 25000,
            'id_category' => 'abc',
        ]);
        $this->assertFalse($result, "id_category bukan integer harus gagal validasi.");
    }

    public function testMenuNamaMelebihi150Karakter(): void
    {
        $result = $this->validate($this->menuRules(), [
            'nama_menu'   => str_repeat('a', 151),
            'harga'       => 25000,
            'id_category' => 1,
        ]);
        $this->assertFalse($result, "nama_menu > 150 karakter harus gagal validasi.");
    }

    // =========================================================
    // ROLE / SESSION LOGIC
    // =========================================================

    public function testIsAdminReturnTrueForAdmin(): void
    {
        $role    = 'Admin'; // case berbeda
        $isAdmin = strtolower($role) === 'admin';

        $this->assertTrue($isAdmin, "Role 'Admin' (kapital) harus dikenali sebagai admin.");
    }

    public function testIsAdminReturnFalseForKasir(): void
    {
        $role    = 'kasir';
        $isAdmin = strtolower($role) === 'admin';

        $this->assertFalse($isAdmin, "Role 'kasir' tidak boleh dianggap admin.");
    }

    public function testIsAdminReturnFalseForEmptyRole(): void
    {
        $role    = '';
        $isAdmin = strtolower((string) $role) === 'admin';

        $this->assertFalse($isAdmin, "Role kosong tidak boleh dianggap admin.");
    }

    // =========================================================
    // PROMO VALIDATION
    // =========================================================

    private function promoRules(): array
    {
        return [
            'kode_promo'      => 'required|max_length[20]',
            'nama_promo'      => 'required|max_length[100]',
            'tipe_diskon'     => 'required|in_list[percent,nominal]',
            'nilai_diskon'    => 'required|decimal|greater_than[0]',
            'min_transaksi'   => 'required|decimal',
            'tanggal_mulai'   => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
        ];
    }

    public function testPromoValidData(): void
    {
        $result = $this->validate($this->promoRules(), [
            'kode_promo'      => 'HEMAT10',
            'nama_promo'      => 'Promo Akhir Bulan',
            'tipe_diskon'     => 'percent',
            'nilai_diskon'    => '10.00',
            'min_transaksi'   => '50000',
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertTrue($result, "Data promo valid harus lulus validasi.");
    }

    public function testPromoTipeDiskonTidakValid(): void
    {
        $result = $this->validate($this->promoRules(), [
            'kode_promo'      => 'HEMAT10',
            'nama_promo'      => 'Promo Akhir Bulan',
            'tipe_diskon'     => 'gratis', // tidak ada di in_list
            'nilai_diskon'    => '10.00',
            'min_transaksi'   => '50000',
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertFalse($result, "tipe_diskon 'gratis' harus gagal validasi.");
    }

    public function testPromoKodeTerlaluPanjang(): void
    {
        $result = $this->validate($this->promoRules(), [
            'kode_promo'      => 'KODEPROMOSANGATPANJANG123',
            'nama_promo'      => 'Promo Test',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => '5000',
            'min_transaksi'   => '30000',
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertFalse($result, "kode_promo > 20 karakter harus gagal validasi.");
    }
}