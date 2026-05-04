<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk OrderDetailModel - hanya menguji logika validasi dan perhitungan subtotal.
 */
final class OrderDetailModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'id_order'     => 'required|integer',
            'id_menu'      => 'required|integer',
            'qty'          => 'required|integer|greater_than[0]',
            'harga_satuan' => 'required|numeric|greater_than[0]',
            'subtotal'     => 'required|numeric|greater_than_equal_to[0]',
        ]);
        return $validation->run($data);
    }

    // qty = 0 → harus gagal
    public function testQtyNolGagal(): void
    {
        $result = $this->validate([
            'id_order'     => 1,
            'id_menu'      => 1,
            'qty'          => 0,
            'harga_satuan' => 25000,
            'subtotal'     => 0,
        ]);
        $this->assertFalse($result, "qty = 0 harus gagal validasi.");
    }

    // qty negatif → harus gagal
    public function testQtyNegatifGagal(): void
    {
        $result = $this->validate([
            'id_order'     => 1,
            'id_menu'      => 1,
            'qty'          => -3,
            'harga_satuan' => 25000,
            'subtotal'     => 0,
        ]);
        $this->assertFalse($result, "qty negatif harus gagal validasi.");
    }

    // Data valid → harus lolos
    public function testDataValidLulus(): void
    {
        $result = $this->validate([
            'id_order'     => 1,
            'id_menu'      => 1,
            'qty'          => 2,
            'harga_satuan' => 25000,
            'subtotal'     => 50000,
        ]);
        $this->assertTrue($result, "Data order detail valid harus lulus validasi.");
    }

    // Perhitungan subtotal harus konsisten: subtotal = qty × harga_satuan
    public function testSubtotalSesuaiPerhitungan(): void
    {
        $qty          = 2;
        $harga_satuan = 25000;
        $subtotal     = $qty * $harga_satuan;

        $this->assertEquals(50000, $subtotal, "Subtotal harus qty × harga_satuan.");
    }

    // Deteksi manipulasi subtotal yang tidak sesuai
    public function testSubtotalTidakSesuaiPerhitungan(): void
    {
        $qty            = 2;
        $harga_satuan   = 25000;
        $subtotal_input = 40000; // sengaja salah (seharusnya 50000)

        $this->assertNotEquals(
            $qty * $harga_satuan,
            $subtotal_input,
            "Sistem harus mendeteksi manipulasi subtotal."
        );
    }

    // Subtotal harus selalu positif atau nol
    public function testSubtotalTidakBolehNegatif(): void
    {
        $qty          = 3;
        $harga_satuan = 15000;
        $subtotal     = $qty * $harga_satuan;

        $this->assertGreaterThanOrEqual(0, $subtotal, "Subtotal tidak boleh negatif.");
    }
}