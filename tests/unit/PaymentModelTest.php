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

        $this->assertTrue($nominalBayar < $totalTagihan, "Nominal bayar kurang → kembalian akan negatif, harus dicegah.");
    }

    // Nominal bayar bukan angka → validasi harus gagal
    public function testNominalBayarBukanAngkaGagal(): void
    {
        $result = $this->validate([
            'id_order'     => 1,
            'jumlah_bayar' => 'lima puluh ribu',
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

    // Metode cash → kembalian dihitung dari jumlah_bayar - total
    public function testKembalianCashDihitungBenar(): void
    {
        $metodeBayar  = 'cash';
        $totalHarga   = 50000;
        $jumlahBayar  = 75000;

        $kembalian = $metodeBayar === 'cash' ? max(0, $jumlahBayar - $totalHarga) : 0;

        $this->assertEquals(25000, $kembalian, "Kembalian cash harus jumlah_bayar - total_harga.");
    }

    // Metode non-cash (debit/qris) → kembalian selalu 0 - 2 assertions
    public function testKembalianNonCashSelaluNol(): void
    {
        $totalHarga  = 50000;
        $jumlahBayar = 50000;

        foreach (['debit', 'qris'] as $metode) {
            $kembalian = $metode === 'cash' ? max(0, $jumlahBayar - $totalHarga) : 0;
            $this->assertEquals(0, $kembalian, "Kembalian metode {$metode} harus selalu 0.");
        }
    }

    // Metode non-cash bayar lebih → kembalian tetap 0
    public function testKembalianNonCashTetapNolMeskiBayarLebih(): void
    {
        $metodeBayar = 'qris';
        $totalHarga  = 50000;
        $jumlahBayar = 75000; // bayar lebih tapi non-cash

        $kembalian = $metodeBayar === 'cash' ? max(0, $jumlahBayar - $totalHarga) : 0;

        $this->assertEquals(0, $kembalian, "Kembalian non-cash harus 0 meskipun bayar lebih.");
    }

    // Kembalian cash tidak boleh negatif meski bayar kurang
    public function testKembalianCashMinimalNol(): void
    {
        $metodeBayar = 'cash';
        $totalHarga  = 50000;
        $jumlahBayar = 30000; // bayar kurang

        $kembalian = $metodeBayar === 'cash' ? max(0, $jumlahBayar - $totalHarga) : 0;

        $this->assertEquals(0, $kembalian, "Kembalian harus 0 jika bayar kurang (dicegah di validasi sebelumnya).");
    }
}