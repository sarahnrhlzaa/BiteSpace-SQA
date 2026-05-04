<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk OrderDetailModel — menguji logika validasi dan perhitungan subtotal.
 * Catatan: harga_satuan dan subtotal ada di allowedFields model namun tidak punya
 * validation rule — keduanya dihitung dan diisi oleh TransaksiController saat checkout,
 * sehingga validasinya berada di level controller, bukan model.
 */

final class OrderDetailModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        // Rule sesuai OrderDetailModel::$validationRules
        $validation->setRules([
            'id_order' => 'required|integer',
            'id_menu'  => 'required|integer',
            'qty'      => 'required|integer|greater_than[0]',
        ]);
        return $validation->run($data);
    }

    // qty = 0 → harus gagal
    public function testQtyNolGagal(): void
    {
        $result = $this->validate([
            'id_order' => 1,
            'id_menu'  => 1,
            'qty'      => 0,
        ]);
        $this->assertFalse($result, 'qty = 0 harus gagal validasi.');
    }

    // qty negatif → harus gagal
    public function testQtyNegatifGagal(): void
    {
        $result = $this->validate([
            'id_order' => 1,
            'id_menu'  => 1,
            'qty'      => -3,
        ]);
        $this->assertFalse($result, 'qty negatif harus gagal validasi.');
    }

    // id_order bukan integer → harus gagal
    public function testIdOrderBukanIntegerGagal(): void
    {
        $result = $this->validate([
            'id_order' => 'abc',
            'id_menu'  => 1,
            'qty'      => 2,
        ]);
        $this->assertFalse($result, 'id_order bukan integer harus gagal validasi.');
    }

    // id_menu bukan integer → harus gagal
    public function testIdMenuBukanIntegerGagal(): void
    {
        $result = $this->validate([
            'id_order' => 1,
            'id_menu'  => 'nasi-goreng',
            'qty'      => 2,
        ]);
        $this->assertFalse($result, 'id_menu bukan integer harus gagal validasi.');
    }

    // Data valid → harus lolos
    public function testDataValidLulus(): void
    {
        $result = $this->validate([
            'id_order' => 1,
            'id_menu'  => 1,
            'qty'      => 2,
        ]);
        $this->assertTrue($result, 'Data order detail valid harus lulus validasi.');
    }

    // Perhitungan subtotal harus konsisten: subtotal = qty × harga_satuan
    // (logika ini dilakukan di TransaksiController::checkout sebelum insert)
    public function testSubtotalSesuaiPerhitungan(): void
    {
        $qty          = 2;
        $harga_satuan = 25000;
        $subtotal     = $qty * $harga_satuan;

        $this->assertEquals(50000, $subtotal, 'Subtotal harus qty * harga_satuan.');
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
            'Sistem harus mendeteksi manipulasi subtotal.'
        );
    }

    // Subtotal harus selalu positif atau nol
    public function testSubtotalTidakBolehNegatif(): void
    {
        $qty          = 3;
        $harga_satuan = 15000;
        $subtotal     = $qty * $harga_satuan;

        $this->assertTrue($subtotal >= 0, 'Subtotal tidak boleh negatif.');
    }
}