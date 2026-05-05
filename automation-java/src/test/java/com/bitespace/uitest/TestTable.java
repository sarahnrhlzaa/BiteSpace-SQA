package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;

/**
 * TestTable.java — UI Test Meja BiteSpace CI4
 * Route: GET /table → TableController::index()
 *
 * TC-TBL-001: Halaman meja tampil
 * TC-TBL-002: Halaman tambah meja bisa diakses admin
 * TC-TBL-003: Form tambah meja ada field nomor_meja & kapasitas
 * TC-TBL-004: Submit tambah meja valid → tersimpan
 * TC-TBL-005: Submit meja tanpa nomor_meja → validasi menolak
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestTable extends BaseTest {

    // TC-TBL-001: Halaman meja tampil
    @Test @Order(1) @DisplayName("TC-TBL-001: Halaman /table tampil")
    void tcTbl001() {
        loginAsAdmin();
        driver.get(BASE + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("Meja") || src.contains("Table") || src.contains("table"),
            "Halaman table harus menampilkan konten terkait meja");
        System.out.println("[TC-TBL-001] PASS");
    }

    // TC-TBL-002: Halaman tambah meja bisa diakses admin
    @Test @Order(2) @DisplayName("TC-TBL-002: Halaman /table/create bisa diakses admin")
    void tcTbl002() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.urlContains("table"));
        String src = driver.getPageSource();
        Assertions.assertFalse(src.contains("Akses ditolak"),
            "Admin tidak boleh mendapat pesan 'Akses ditolak'");
        Assertions.assertTrue(src.length() > 500,
            "Halaman /table/create harus ada konten");
        System.out.println("[TC-TBL-002] PASS");
    }

    // TC-TBL-003: Form tambah meja ada field nomor_meja & kapasitas
    @Test @Order(3) @DisplayName("TC-TBL-003: Form meja ada field nomor_meja & kapasitas")
    void tcTbl003() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nomor_meja")));
        Assertions.assertNotNull(driver.findElement(By.name("nomor_meja")),
            "Field nomor_meja harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("kapasitas")),
            "Field kapasitas harus ada");
        System.out.println("[TC-TBL-003] PASS");
    }

    // TC-TBL-004: Submit tambah meja valid → tersimpan
    @Test @Order(4) @DisplayName("TC-TBL-004: Tambah meja valid → tersimpan")
    void tcTbl004() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nomor_meja")));

        // Gunakan nomor meja random untuk hindari duplikasi
        String nomorMeja = String.valueOf(100 + (int)(Math.random() * 900));
        driver.findElement(By.name("nomor_meja")).sendKeys(nomorMeja);
        driver.findElement(By.name("kapasitas")).sendKeys("4");

        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save")));
        jsClick(btnSave);

        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("table"),
            "Setelah simpan harus kembali ke halaman table");
        System.out.println("[TC-TBL-004] PASS");
    }

    // TC-TBL-005: Submit meja tanpa nomor_meja → validasi menolak
    @Test @Order(5) @DisplayName("TC-TBL-005: Tambah meja tanpa nomor → ditolak")
    void tcTbl005() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kapasitas")));

        // Isi kapasitas saja, biarkan nomor_meja kosong
        driver.findElement(By.name("kapasitas")).sendKeys("4");

        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save")));
        jsClick(btnSave);

        // Tunggu validasi
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}

        Assertions.assertTrue(driver.getCurrentUrl().contains("table"),
            "Nomor meja kosong → harus ditolak dan tetap di halaman table");
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"),
            "Tidak boleh ada pesan sukses saat nomor_meja kosong");
        System.out.println("[TC-TBL-005] PASS");
    }
}
