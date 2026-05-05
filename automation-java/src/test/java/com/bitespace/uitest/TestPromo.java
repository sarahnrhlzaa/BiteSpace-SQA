package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;

/**
 * TestPromo.java — UI Test Promo BiteSpace CI4
 * Route: GET /promo → PromoController::index()
 *
 * TC-PRM-001: Halaman promo tampil
 * TC-PRM-002: Halaman tambah promo bisa diakses admin
 * TC-PRM-003: Form promo ada field kode_promo, nama_promo, nilai_diskon
 * TC-PRM-004: Submit promo kosong → validasi menolak
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestPromo extends BaseTest {

    // TC-PRM-001: Halaman promo tampil
    @Test @Order(1) @DisplayName("TC-PRM-001: Halaman promo tampil")
    void tcPrm001() {
        loginAsAdmin();
        driver.get(BASE + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("Promo") || src.contains("promo"),
            "Halaman promo harus menampilkan konten terkait promo");
        System.out.println("[TC-PRM-001] PASS");
    }

    // TC-PRM-002: Halaman tambah promo bisa diakses admin
    @Test @Order(2) @DisplayName("TC-PRM-002: Halaman /promo/create bisa diakses admin")
    void tcPrm002() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.urlContains("promo"));
        String src = driver.getPageSource();
        Assertions.assertFalse(src.contains("Akses ditolak"),
            "Admin tidak boleh mendapat pesan 'Akses ditolak'");
        Assertions.assertTrue(src.length() > 500,
            "Halaman /promo/create harus ada konten");
        System.out.println("[TC-PRM-002] PASS");
    }

    // TC-PRM-003: Form promo ada field kode_promo, nama_promo, nilai_diskon
    @Test @Order(3) @DisplayName("TC-PRM-003: Form promo ada field kode_promo & nama_promo")
    void tcPrm003() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kode_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("kode_promo")),
            "Field kode_promo harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("nama_promo")),
            "Field nama_promo harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("nilai_diskon")),
            "Field nilai_diskon harus ada");
        System.out.println("[TC-PRM-003] PASS");
    }

    // TC-PRM-004: Submit promo kosong → validasi menolak
    @Test @Order(4) @DisplayName("TC-PRM-004: Tambah promo field kosong → ditolak")
    void tcPrm004() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kode_promo")));

        // Klik submit tanpa mengisi field apapun
        WebElement btnSubmit = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-submit-bs")));
        jsClick(btnSubmit);

        // Tunggu validasi
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}

        // Harus tetap di halaman promo dan tidak menampilkan pesan sukses
        Assertions.assertTrue(driver.getCurrentUrl().contains("promo"),
            "Field kosong → harus ditolak dan tetap di halaman promo");
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"),
            "Tidak boleh ada pesan 'berhasil ditambahkan' saat field kosong");
        System.out.println("[TC-PRM-004] PASS");
    }
}
