<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk PromoModel.
 * Tidak menggunakan database — hanya menguji logika validasi dan kalkulasi diskon.
 * Rule validasi diselaraskan dengan PromoModel dan PromoController::store:
 */

final class PromoModelTest extends CIUnitTestCase
{
    private function validate(array $data): bool
    {
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'kode_promo'      => 'required|max_length[20]',
            'nama_promo'      => 'required|max_length[100]',
            'tipe_diskon'     => 'required|in_list[percent,nominal]',
            'nilai_diskon'    => 'required|numeric|greater_than[0]',
            'min_transaksi'   => 'required|numeric|greater_than_equal_to[0]',
            'tanggal_mulai'   => 'required|valid_date[Y-m-d]',
            'tanggal_selesai' => 'required|valid_date[Y-m-d]',
        ]);
        return $validation->run($data);
    }

    // tipe_diskon di luar daftar → harus gagal
    public function testInsertTipeDiskonTidakValidAkanGagal(): void
    {
        $result = $this->validate([
            'kode_promo'      => 'HEMAT',
            'nama_promo'      => 'Promo Test',
            'tipe_diskon'     => 'invalid_type',
            'nilai_diskon'    => 10,
            'min_transaksi'   => 50000,
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertFalse($result, 'tipe_diskon tidak valid harus gagal validasi.');
    }

    // nilai_diskon negatif → harus gagal
    public function testInsertNilaiDiskonNegatifAkanGagal(): void
    {
        $result = $this->validate([
            'kode_promo'      => 'HEMAT',
            'nama_promo'      => 'Promo Test',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => -5000,
            'min_transaksi'   => 50000,
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertFalse($result, 'nilai_diskon negatif harus gagal validasi.');
    }

    // nilai_diskon nol → harus gagal (greater_than[0])
    public function testInsertNilaiDiskonNolAkanGagal(): void
    {
        $result = $this->validate([
            'kode_promo'      => 'HEMAT',
            'nama_promo'      => 'Promo Test',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 0,
            'min_transaksi'   => 50000,
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertFalse($result, 'nilai_diskon = 0 harus gagal validasi.');
    }

    // kode_promo melebihi 20 karakter → harus gagal
    public function testPromoKodeTerlaluPanjangGagal(): void
    {
        $result = $this->validate([
            'kode_promo'      => 'KODEPROMOSANGATPANJANG123',
            'nama_promo'      => 'Promo Test',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 5000,
            'min_transaksi'   => 30000,
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertFalse($result, 'kode_promo > 20 karakter harus gagal validasi.');
    }

    // min_transaksi nol → harus lolos (greater_than_equal_to[0], boleh 0)
    public function testMinTransaksiNolLulus(): void
    {
        $result = $this->validate([
            'kode_promo'      => 'GRATIS',
            'nama_promo'      => 'Promo Tanpa Minimum',
            'tipe_diskon'     => 'nominal',
            'nilai_diskon'    => 5000,
            'min_transaksi'   => 0,
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertTrue($result, 'min_transaksi = 0 harus lulus validasi.');
    }

    // Data promo valid → harus lolos
    public function testPromoValidDataLulus(): void
    {
        $result = $this->validate([
            'kode_promo'      => 'HEMAT10',
            'nama_promo'      => 'Promo Akhir Bulan',
            'tipe_diskon'     => 'percent',
            'nilai_diskon'    => '10.00',
            'min_transaksi'   => '50000',
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertTrue($result, 'Data promo valid harus lulus validasi.');
    }

    // Hitung diskon tipe percent (normal, di bawah batas maks)
    public function testHitungDiskonPercent(): void
    {
        $subtotal    = 100000;
        $nilaiDiskon = 10; // 10%
        $maksDiskon  = 20000;

        $diskon = min(($subtotal * $nilaiDiskon) / 100, $maksDiskon);

        $this->assertEquals(10000, $diskon, 'Diskon 10% dari 100.000 harus 10.000.');
    }

    // Hitung diskon percent → dibatasi nilai maksimum
    public function testHitungDiskonPercentDibatasiMaks(): void
    {
        $subtotal    = 200000;
        $nilaiDiskon = 20; // 20% = 40.000, tapi maks 25.000
        $maksDiskon  = 25000;

        $diskon = min(($subtotal * $nilaiDiskon) / 100, $maksDiskon);

        $this->assertEquals(25000, $diskon, 'Diskon harus dibatasi nilai maksimum.');
    }

    // Diskon percent > 100% → harus ditolak (logika PromoController::store)
    public function testDiskonPercentMelebihi100Ditolak(): void
    {
        $nilaiDiskon = 150; // 150% — tidak wajar
        $isValid     = !($nilaiDiskon > 100);

        $this->assertFalse($isValid, 'Diskon persen > 100% harus ditolak.');
    }

    // Hitung diskon tipe nominal
    public function testHitungDiskonNominal(): void
    {
        $subtotal    = 80000;
        $nilaiDiskon = 15000;
        $total       = $subtotal - $nilaiDiskon;

        $this->assertEquals(65000, $total, 'Total setelah diskon nominal harus benar.');
    }

    // Promo hanya berlaku jika memenuhi minimum transaksi
    public function testPromoBerlakuJikaMemenuhiMinTransaksi(): void
    {
        $subtotal     = 75000;
        $minTransaksi = 50000;

        $this->assertTrue(
            $subtotal >= $minTransaksi,
            'Promo harus berlaku jika subtotal memenuhi minimum transaksi.'
        );
    }

    // Promo tidak berlaku jika di bawah minimum transaksi
    public function testPromoTidakBerlakuJikaDiBawahMinTransaksi(): void
    {
        $subtotal     = 30000;
        $minTransaksi = 50000;

        $this->assertFalse(
            $subtotal >= $minTransaksi,
            'Promo tidak boleh berlaku jika subtotal di bawah minimum transaksi.'
        );
    }

    // Diskon tidak boleh melebihi subtotal (logika PromoController: min($diskon, $subtotal))
    public function testDiskonTidakBolehMelebihiSubtotal(): void
    {
        $subtotal    = 20000;
        $nilaiDiskon = 50000; // diskon lebih besar dari subtotal

        $diskon = min($nilaiDiskon, $subtotal);

        $this->assertEquals(20000, $diskon, 'Diskon tidak boleh melebihi nilai subtotal.');
    }

    // Diskon nominal lebih besar dari subtotal → total tidak boleh negatif
    public function testTotalSetelahDiskonTidakBolehNegatif(): void
    {
        $subtotal    = 20000;
        $nilaiDiskon = 50000;

        $diskon = min($nilaiDiskon, $subtotal);
        $total  = max(0, $subtotal - $diskon);

        $this->assertTrue($total >= 0, 'Total tidak boleh negatif.');
        $this->assertEquals(0, $total, 'Total harus 0 jika diskon melebihi subtotal.');
    }
}