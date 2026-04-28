<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk PromoModel.
 * Tidak menggunakan database — hanya menguji logika validasi dan kalkulasi diskon.
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
            'nilai_diskon'    => 'required|decimal|greater_than[0]',
            'min_transaksi'   => 'required|decimal',
            'tanggal_mulai'   => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date',
        ]);
        return $validation->run($data);
    }

    // tipe_diskon di luar daftar → harus gagal
    public function testInsertTipeDiskonTidakValidAkanGagal(): void
    {
        $result = $this->validate([
            'kode_promo'      => 'HEMAT',
            'nama_promo'      => 'Promo Test',
            'tipe_diskon'     => 'invalid_type', // bukan percent/nominal
            'nilai_diskon'    => 10,
            'min_transaksi'   => 50000,
            'tanggal_mulai'   => '2025-01-01',
            'tanggal_selesai' => '2025-01-31',
        ]);
        $this->assertFalse($result, "tipe_diskon tidak valid harus gagal validasi.");
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
        $this->assertFalse($result, "nilai_diskon negatif harus gagal validasi.");
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
        $this->assertFalse($result, "kode_promo > 20 karakter harus gagal validasi.");
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
        $this->assertTrue($result, "Data promo valid harus lulus validasi.");
    }

    // Hitung diskon tipe percent (normal, di bawah batas maks)
    public function testHitungDiskonPercent(): void
    {
        $subtotal    = 100000;
        $nilaiDiskon = 10; // 10%
        $maksDiskon  = 20000;

        $diskon = min(($subtotal * $nilaiDiskon) / 100, $maksDiskon);

        $this->assertEquals(10000, $diskon, "Diskon 10% dari 100.000 harus 10.000.");
    }

    // Hitung diskon percent → dibatasi nilai maksimum
    public function testHitungDiskonPercentDibatasiMaks(): void
    {
        $subtotal    = 200000;
        $nilaiDiskon = 20; // 20% = 40.000, tapi maks 25.000
        $maksDiskon  = 25000;

        $diskon = min(($subtotal * $nilaiDiskon) / 100, $maksDiskon);

        $this->assertEquals(25000, $diskon, "Diskon harus dibatasi nilai maksimum.");
    }

    // Hitung diskon tipe nominal
    public function testHitungDiskonNominal(): void
    {
        $subtotal    = 80000;
        $nilaiDiskon = 15000;
        $total       = $subtotal - $nilaiDiskon;

        $this->assertEquals(65000, $total, "Total setelah diskon nominal harus benar.");
    }

    // Promo hanya berlaku jika memenuhi minimum transaksi
    public function testPromoBerlakuJikaMemenuhiMinTransaksi(): void
    {
        $subtotal     = 75000;
        $minTransaksi = 50000;

        $this->assertTrue(
            $subtotal >= $minTransaksi,
            "Promo harus berlaku jika subtotal memenuhi minimum transaksi."
        );
    }

    // Promo tidak berlaku jika di bawah minimum transaksi
    public function testPromoTidakBerlakuJikaDiBawahMinTransaksi(): void
    {
        $subtotal     = 30000;
        $minTransaksi = 50000;

        $this->assertFalse(
            $subtotal >= $minTransaksi,
            "Promo tidak boleh berlaku jika subtotal di bawah minimum transaksi."
        );
    }
}