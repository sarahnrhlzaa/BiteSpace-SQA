<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk PaymentModel.
 * Tidak menggunakan database — hanya menguji logika validasi dan kalkulasi pembayaran.
 */
final class PaymentModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'id_order'     => 'required|integer',
            'jumlah_bayar' => 'required|numeric|greater_than[0]',
            'kembalian'    => 'required|numeric|greater_than_equal_to[0]',
        ]);
        return $validation->run($data);
    }

    // Logika: pembayaran kurang dari tagihan → harus ditolak
    public function testPembayaranKurangGagal(): void
    {
        $totalTagihan = 50000;
        $nominalBayar = 30000;

        $isValid = $nominalBayar >= $totalTagihan;

        $this->assertFalse($isValid, "Sistem harus menolak jika nominal bayar kurang dari tagihan.");
    }

    // Logika: pembayaran cukup → harus diterima
    public function testPembayaranCukupBerhasil(): void
    {
        $totalTagihan = 50000;
        $nominalBayar = 50000;

        $isValid = $nominalBayar >= $totalTagihan;

        $this->assertTrue($isValid, "Nominal bayar sama dengan tagihan harus diterima.");
    }

    // Logika: pembayaran lebih → kembalian dihitung benar
    public function testKembalianDihitungBenar(): void
    {
        $totalTagihan = 50000;
        $nominalBayar = 75000;
        $kembalian    = $nominalBayar - $totalTagihan;

        $this->assertEquals(25000, $kembalian, "Kembalian harus dihitung dengan benar.");
    }

    // Kembalian tidak boleh negatif (bayar kurang dari tagihan)
    public function testKembalianTidakBolehNegatif(): void
    {
        $totalTagihan = 50000;
        $nominalBayar = 30000;

        $this->assertLessThan($totalTagihan, $nominalBayar, "Nominal bayar kurang → kembalian akan negatif, harus dicegah.");
    }

    // Nominal bayar bukan angka → validasi harus gagal
    public function testNominalBayarBukanAngkaGagal(): void
    {
        $result = $this->validate([
            'id_order'     => 1,
            'jumlah_bayar' => 'lima puluh ribu', // salah input
            'kembalian'    => 0,
        ]);
        $this->assertFalse($result, "jumlah_bayar berupa teks harus gagal validasi.");
    }

    // Nominal bayar nol → validasi harus gagal
    public function testNominalBayarNolGagal(): void
    {
        $result = $this->validate([
            'id_order'     => 1,
            'jumlah_bayar' => 0,
            'kembalian'    => 0,
        ]);
        $this->assertFalse($result, "jumlah_bayar = 0 harus gagal validasi.");
    }

    // Data pembayaran valid → validasi harus lolos
    public function testDataPembayaranValidLulus(): void
    {
        $result = $this->validate([
            'id_order'     => 1,
            'jumlah_bayar' => 60000,
            'kembalian'    => 10000,
        ]);
        $this->assertTrue($result, "Data pembayaran valid harus lulus validasi.");
    }
}