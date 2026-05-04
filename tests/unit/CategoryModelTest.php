<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk CategoryModel — hanya menguji logika validasi rule secara murni.
 */
final class CategoryModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'nama_category' => 'required|max_length[100]',
        ]);
        return $validation->run($data);
    }

    // Nama kategori kosong → harus gagal
    public function testNamaKategoriKosongGagal(): void
    {
        $result = $this->validate(['nama_category' => '']);
        $this->assertFalse($result, "nama_category kosong harus gagal validasi.");
    }

    // Nama kategori terlalu panjang (>100 karakter) → harus gagal
    public function testNamaKategoriTerlaluPanjangGagal(): void
    {
        $result = $this->validate(['nama_category' => str_repeat('A', 101)]);
        $this->assertFalse($result, "nama_category > 100 karakter harus gagal validasi.");
    }

    // Nama kategori valid → harus lolos
    public function testNamaKategoriValidLulus(): void
    {
        $result = $this->validate(['nama_category' => 'Makanan Utama']);
        $this->assertTrue($result, "nama_category valid harus lulus validasi.");
    }

    // Tepat 100 karakter → harus lolos (edge case)
    public function testNamaKategoriTepat100KarakterLulus(): void
    {
        $result = $this->validate(['nama_category' => str_repeat('A', 100)]);
        $this->assertTrue($result, "nama_category tepat 100 karakter harus lulus validasi.");
    }
}