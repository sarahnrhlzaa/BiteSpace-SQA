<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk CustomerModel — hanya menguji logika validasi rule secara murni.
 */
final class CustomerModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'nama_customer' => 'required|max_length[100]',
            'telp'          => 'permit_empty|max_length[15]',
        ]);
        return $validation->run($data);
    }

    // No. telp melebihi 15 digit → harus gagal
    public function testNoTelpTerlaluPanjangGagal(): void
    {
        $result = $this->validate([
            'nama_customer' => 'Sarah Nurhaliza',
            'telp'          => '08123456789092832003921234567', // > 15 digit
        ]);
        $this->assertFalse($result, "Nomor telepon > 15 karakter harus gagal validasi.");
    }

    // No. telp tepat 15 digit → harus lolos (edge case)
    public function testNoTelpTepat15DigitLulus(): void
    {
        $result = $this->validate([
            'nama_customer' => 'Sarah Nurhaliza',
            'telp'          => '081234567890123', // tepat 15
        ]);
        $this->assertTrue($result, "Nomor telepon tepat 15 karakter harus lulus validasi.");
    }

    // Nama customer kosong → harus gagal
    public function testNamaCustomerKosongGagal(): void
    {
        $result = $this->validate([
            'nama_customer' => '',
            'telp'          => '0811234567',
        ]);
        $this->assertFalse($result, "nama_customer kosong harus gagal validasi.");
    }

    // Data lengkap valid → harus lolos
    public function testDataCustomerValidLulus(): void
    {
        $result = $this->validate([
            'nama_customer' => 'Naufalnadi',
            'telp'          => '0811',
        ]);
        $this->assertTrue($result, "Data customer valid harus lulus validasi.");
    }

    // Pencarian customer berdasarkan nama (simulasi logika filter)
    public function testCustomerSearchLogic(): void
    {
        // Simulasi array hasil query (seperti dari model->findAll())
        $customers = [
            ['nama_customer' => 'Neyza', 'telp' => '0811'],
            ['nama_customer' => 'Sarah Nurhaliza', 'telp' => '0822'],
        ];

        $keyword = 'Neyza';
        $found   = array_filter($customers, fn($c) => $c['nama_customer'] === $keyword);

        $this->assertCount(1, $found);
        $this->assertSame('0811', array_values($found)[0]['telp']);
    }
}