<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk TableModel.
 * Tidak menggunakan database — hanya menguji logika validasi data meja.
 */
final class TableModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'nomor_meja' => 'required|max_length[20]',
            'kapasitas'  => 'required|integer|greater_than[0]',
            'status'     => 'required|in_list[available,occupied,reserved]',
        ]);
        return $validation->run($data);
    }

    // Kapasitas bukan angka → harus gagal
    public function testInsertKapasitasBukanAngkaGagal(): void
    {
        $result = $this->validate([
            'nomor_meja' => 'T-99',
            'kapasitas'  => 'Empat', // salah input
            'status'     => 'available',
        ]);
        $this->assertFalse($result, "Kapasitas berupa teks harus gagal validasi.");
    }

    // Kapasitas nol → harus gagal
    public function testInsertKapasitasNolGagal(): void
    {
        $result = $this->validate([
            'nomor_meja' => 'T-01',
            'kapasitas'  => 0,
            'status'     => 'available',
        ]);
        $this->assertFalse($result, "Kapasitas = 0 harus gagal validasi.");
    }

    // Kapasitas negatif → harus gagal
    public function testInsertKapasitasNegatifGagal(): void
    {
        $result = $this->validate([
            'nomor_meja' => 'T-01',
            'kapasitas'  => -2,
            'status'     => 'available',
        ]);
        $this->assertFalse($result, "Kapasitas negatif harus gagal validasi.");
    }

    // Status di luar enum → harus gagal
    public function testInsertStatusTidakValidGagal(): void
    {
        $result = $this->validate([
            'nomor_meja' => 'T-01',
            'kapasitas'  => 4,
            'status'     => 'rusak', // bukan dari enum
        ]);
        $this->assertFalse($result, "Status tidak valid harus gagal validasi.");
    }

    // Data meja valid → harus lolos
    public function testInsertMejaBerhasil(): void
    {
        $result = $this->validate([
            'nomor_meja' => 'T-100',
            'kapasitas'  => 4,
            'status'     => 'available',
        ]);
        $this->assertTrue($result, "Data meja valid harus lulus validasi.");
    }

    // Logika cek ketersediaan meja (simulasi, tanpa DB)
    public function testMejaAvailableLogika(): void
    {
        $meja = ['nomor_meja' => 'T-05', 'kapasitas' => 4, 'status' => 'available'];

        $isAvailable = $meja['status'] === 'available';

        $this->assertTrue($isAvailable, "Meja dengan status 'available' harus dianggap tersedia.");
    }

    // Meja occupied tidak tersedia
    public function testMejaOccupiedTidakTersedia(): void
    {
        $meja = ['nomor_meja' => 'T-03', 'kapasitas' => 6, 'status' => 'occupied'];

        $isAvailable = $meja['status'] === 'available';

        $this->assertFalse($isAvailable, "Meja dengan status 'occupied' tidak boleh dianggap tersedia.");
    }
}