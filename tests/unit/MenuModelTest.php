<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk MenuModel — menguji logika validasi dan pengelompokan menu.
 */

final class MenuModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'nama_menu'   => 'required|max_length[100]',
            'harga'       => 'required|decimal|greater_than[0]',
            'id_category' => 'required|integer',
        ]);
        return $validation->run($data);
    }

    // Validasi nama_menu required → harus gagal jika kosong
    public function testInsertMenuTanpaNamaAkanGagal(): void
    {
        $result = $this->validate([
            'harga'       => 25000,
            'id_category' => 1,
        ]);
        $this->assertFalse($result, 'Harus gagal karena nama_menu kosong.');
    }

    // Harga bukan angka → harus gagal
    public function testInsertMenuHargaBukanAngkaAkanGagal(): void
    {
        $result = $this->validate([
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => 'dua puluh ribu',
            'id_category' => 1,
        ]);
        $this->assertFalse($result, 'Harus gagal karena harga bukan angka.');
    }

    // Harga negatif → harus gagal
    public function testInsertMenuHargaNegatifAkanGagal(): void
    {
        $result = $this->validate([
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => -5000,
            'id_category' => 1,
        ]);
        $this->assertFalse($result, 'Harus gagal karena harga negatif.');
    }

    // Harga nol → harus gagal (greater_than[0], bukan greater_than_equal_to[0])
    public function testInsertMenuHargaNolAkanGagal(): void
    {
        $result = $this->validate([
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => 0,
            'id_category' => 1,
        ]);
        $this->assertFalse($result, 'Harga = 0 harus gagal validasi (model mewajibkan greater_than[0]).');
    }

    // id_category bukan integer → harus gagal
    public function testInsertMenuIdCategoryBukanIntegerAkanGagal(): void
    {
        $result = $this->validate([
            'nama_menu'   => 'Nasi Goreng',
            'harga'       => 25000,
            'id_category' => 'makanan',
        ]);
        $this->assertFalse($result, 'Harus gagal karena id_category bukan integer.');
    }

    // nama_menu melebihi 100 karakter → harus gagal (max_length[100] di MenuModel)
    public function testInsertMenuNamaMelebihi100KarakterAkanGagal(): void
    {
        $result = $this->validate([
            'nama_menu'   => str_repeat('A', 101),
            'harga'       => 25000,
            'id_category' => 1,
        ]);
        $this->assertFalse($result, 'Harus gagal karena nama_menu > 100 karakter.');
    }

    // Tepat 100 karakter → harus lolos (edge case max_length[100])
    public function testInsertMenuNamaTepat100KarakterLulus(): void
    {
        $result = $this->validate([
            'nama_menu'   => str_repeat('A', 100),
            'harga'       => 25000,
            'id_category' => 1,
        ]);
        $this->assertTrue($result, 'nama_menu tepat 100 karakter harus lulus validasi.');
    }

    // Data valid → harus lolos
    public function testInsertMenuDataValidLulus(): void
    {
        $result = $this->validate([
            'nama_menu'   => 'Nasi Bakar',
            'harga'       => 30000,
            'id_category' => 1,
        ]);
        $this->assertTrue($result, 'Data menu valid harus lulus validasi.');
    }

    // Logika pengelompokan menu berdasarkan kategori (mencerminkan getMenuGroupedByCategory di MenuModel)
    public function testGetMenuGroupedByCategoryReturnStructuredArray(): void
    {
        $rawMenus = [
            ['nama_category' => 'Makanan', 'nama_menu' => 'Nasi Goreng', 'harga' => 25000, 'is_available' => 1],
            ['nama_category' => 'Makanan', 'nama_menu' => 'Mie Ayam',    'harga' => 20000, 'is_available' => 1],
            ['nama_category' => 'Minuman', 'nama_menu' => 'Es Teh',      'harga' => 5000,  'is_available' => 1],
        ];

        $grouped = [];
        foreach ($rawMenus as $menu) {
            $grouped[$menu['nama_category']][] = $menu;
        }

        $this->assertArrayHasKey('Makanan', $grouped);
        $this->assertArrayHasKey('Minuman', $grouped);
        $this->assertCount(2, $grouped['Makanan']);
        $this->assertCount(1, $grouped['Minuman']);
    }

    // Hanya menu is_available=1 yang boleh tampil (mencerminkan getMenuAvailable di MenuModel)
    public function testMenuHanyaTampilkanYangAvailable(): void
    {
        $allMenus = [
            ['nama_menu' => 'Nasi Goreng', 'is_available' => 1],
            ['nama_menu' => 'Soto Ayam',   'is_available' => 0],
            ['nama_menu' => 'Es Teh',      'is_available' => 1],
        ];

        $available = array_filter($allMenus, fn($m) => $m['is_available'] === 1);

        $this->assertCount(2, $available);
    }
}