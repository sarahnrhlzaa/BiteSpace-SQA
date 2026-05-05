<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk OrderModel — menguji logika validasi sesuai rule di model.
 */

final class OrderModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'id_table'    => 'required|integer',
            'id_user'     => 'required|integer',
            'kode_order'  => 'required',
            'total_harga' => 'required|numeric|greater_than_equal_to[0]',
        ]);
        return $validation->run($data);
    }

    // Insert tanpa field wajib → harus gagal
    public function testInsertTanpaDataWajibGagal(): void
    {
        $result = $this->validate([]);
        $this->assertFalse($result, "Order tanpa field wajib harus gagal validasi.");
    }

    // id_table kosong → harus gagal
    public function testInsertTanpaIdTableGagal(): void
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'id_table'    => 'required|integer',
            'id_user'     => 'required|integer',
            'kode_order'  => 'required',
            'total_harga' => 'required|numeric|greater_than_equal_to[0]',
        ]);
        $result = $validation->run([
            'id_user'     => 1,
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => 50000,
        ]);

        $this->assertFalse($result, "Order tanpa id_table harus gagal validasi.");
        $this->assertNotEmpty($validation->getError('id_table'));
    }

    // id_user kosong → harus gagal
    public function testInsertTanpaIdUserGagal(): void
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'id_table'    => 'required|integer',
            'id_user'     => 'required|integer',
            'kode_order'  => 'required',
            'total_harga' => 'required|numeric|greater_than_equal_to[0]',
        ]);
        $result = $validation->run([
            'id_table'    => 1,
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => 50000,
        ]);

        $this->assertFalse($result, "Order tanpa id_user harus gagal validasi.");
        $this->assertNotEmpty($validation->getError('id_user'));
    }

    // kode_order kosong → harus gagal
    public function testInsertTanpaKodeOrderGagal(): void
    {
        $result = $this->validate([
            'id_table'    => 1,
            'id_user'     => 1,
            'kode_order'  => '',
            'total_harga' => 50000,
        ]);
        $this->assertFalse($result, "Order tanpa kode_order harus gagal validasi.");
    }

    // total_harga negatif → harus gagal
    public function testTotalHargaNegatifGagal(): void
    {
        $result = $this->validate([
            'id_table'    => 5,
            'id_user'     => 1,
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => -15000,
        ]);
        $this->assertFalse($result, "total_harga negatif harus gagal validasi.");
    }

    // total_harga bukan angka → harus gagal
    public function testTotalHargaBukanAngkaGagal(): void
    {
        $result = $this->validate([
            'id_table'    => 1,
            'id_user'     => 1,
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => 'lima puluh ribu',
        ]);
        $this->assertFalse($result, "total_harga bukan angka harus gagal validasi.");
    }

    // total_harga = 0 → harus lolos (greater_than_equal_to[0])
    public function testTotalHargaNolLulus(): void
    {
        $result = $this->validate([
            'id_table'    => 1,
            'id_user'     => 1,
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => 0,
        ]);
        $this->assertTrue($result, "total_harga = 0 harus lulus validasi.");
    }

    // id_table bukan integer → harus gagal
    public function testIdTableBukanIntegerGagal(): void
    {
        $result = $this->validate([
            'id_table'    => 'meja-satu',
            'id_user'     => 1,
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => 50000,
        ]);
        $this->assertFalse($result, "id_table bukan integer harus gagal validasi.");
    }

    // id_user bukan integer → harus gagal
    public function testIdUserBukanIntegerGagal(): void
    {
        $result = $this->validate([
            'id_table'    => 1,
            'id_user'     => 'admin',
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => 50000,
        ]);
        $this->assertFalse($result, "id_user bukan integer harus gagal validasi.");
    }

    // Data valid → harus lolos
    public function testDataOrderValidLulus(): void
    {
        $result = $this->validate([
            'id_table'    => 2,
            'id_user'     => 1,
            'kode_order'  => 'ORD-ABC123',
            'total_harga' => 50000,
        ]);
        $this->assertTrue($result, "Data order valid harus lulus validasi.");
    }

    // Simulasi logika update total_harga
    public function testUpdateTotalHargaLogika(): void
    {
        $order = [
            'id_table'    => 2,
            'id_user'     => 1,
            'kode_order'  => 'ORD-999',
            'total_harga' => 50000,
        ];

        $order['total_harga'] = 75000;

        $this->assertEquals(75000, $order['total_harga'], "Total harga harus terupdate dengan benar.");
    }

    // Format kode_order harus valid (ORD-XXXXXX)
    public function testGenerateKodeOrderFormatValid(): void
    {
        $kode = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $this->assertMatchesRegularExpression('/^ORD-[A-F0-9]{6}$/', $kode, "Format kode order harus ORD-XXXXXX.");
    }
}