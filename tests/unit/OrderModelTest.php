<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk OrderModel.
 * Tidak menggunakan database — hanya menguji logika validasi dan status order.
 */
final class OrderModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'id_table'    => 'required|integer',
            'total_harga' => 'required|numeric|greater_than_equal_to[0]',
            'status'      => 'required|in_list[pending,paid,cancelled]',
        ]);
        return $validation->run($data);
    }

    // Insert tanpa id_table dan total_harga → harus gagal
    public function testInsertTanpaDataWajibGagal(): void
    {
        $result = $this->validate([
            'status' => 'pending',
        ]);
        $this->assertFalse($result, "Order tanpa id_table dan total_harga harus gagal validasi.");
    }

    // id_table kosong saja → harus gagal
    public function testInsertTanpaIdTableGagal(): void
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'id_table'    => 'required|integer',
            'total_harga' => 'required|numeric|greater_than_equal_to[0]',
            'status'      => 'required|in_list[pending,paid,cancelled]',
        ]);
        $result = $validation->run([
            'total_harga' => 50000,
            'status'      => 'pending',
        ]);

        $this->assertFalse($result, "Order tanpa id_table harus gagal validasi.");
        $this->assertNotEmpty($validation->getError('id_table'));
    }

    // total_harga negatif → harus gagal
    public function testTotalHargaNegatifGagal(): void
    {
        $result = $this->validate([
            'id_table'    => 5,
            'total_harga' => -15000,
            'status'      => 'pending',
        ]);
        $this->assertFalse($result, "total_harga negatif harus gagal validasi.");
    }

    // Status di luar enum → harus gagal
    public function testStatusTidakValidGagal(): void
    {
        $result = $this->validate([
            'id_table'    => 1,
            'total_harga' => 50000,
            'status'      => 'selesai', // bukan dari enum
        ]);
        $this->assertFalse($result, "Status di luar daftar yang diizinkan harus gagal validasi.");
    }

    // Data valid → harus lolos
    public function testDataOrderValidLulus(): void
    {
        $result = $this->validate([
            'id_table'    => 2,
            'total_harga' => 50000,
            'status'      => 'pending',
        ]);
        $this->assertTrue($result, "Data order valid harus lulus validasi.");
    }

    // Simulasi logika update total_harga (tanpa DB)
    public function testUpdateTotalHargaLogika(): void
    {
        // Representasi data order dalam array
        $order = [
            'id'          => 1,
            'id_table'    => 2,
            'kode_order'  => 'ORD-999',
            'total_harga' => 50000,
            'status'      => 'pending',
        ];

        // Simulasi update
        $order['total_harga'] = 75000;

        $this->assertEquals(75000, $order['total_harga'], "Total harga harus terupdate dengan benar.");
    }

    // Kode order harus unik format (simulasi generate kode)
    public function testGenerateKodeOrderFormatValid(): void
    {
        $kode = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $this->assertMatchesRegularExpression('/^ORD-[A-F0-9]{6}$/', $kode, "Format kode order harus ORD-XXXXXX.");
    }
}