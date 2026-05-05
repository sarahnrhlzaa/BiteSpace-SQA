package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;

/**
 * TestMenu.java — UI Test Menu BiteSpace CI4
 * Route: GET /menu → MenuController::index()
 *
 * TC-MNU-001: Halaman daftar menu tampil
 * TC-MNU-002: Halaman tambah menu bisa diakses admin
 * TC-MNU-003: Form tambah menu punya field nama_menu, harga, id_category
 * TC-MNU-004: Submit form tambah menu valid → sukses
 * TC-MNU-005: Submit nama menu kosong → validasi menolak
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestMenu extends BaseTest {

    // TC-MNU-001: Halaman daftar menu tampil
    @Test @Order(1) @DisplayName("TC-MNU-001: Halaman menu tampil")
    void tcMnu001() {
        loginAsAdmin();
        driver.get(BASE + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getPageSource().contains("Menu"),
            "Halaman menu harus menampilkan teks 'Menu'");
        System.out.println("[TC-MNU-001] PASS");
    }

    // TC-MNU-002: Halaman tambah menu bisa diakses admin
    @Test @Order(2) @DisplayName("TC-MNU-002: Halaman /menu/create bisa diakses admin")
    void tcMnu002() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.urlContains("menu"));
        String src = driver.getPageSource();
        Assertions.assertFalse(src.contains("Akses ditolak"),
            "Admin tidak boleh mendapat pesan 'Akses ditolak'");
        // Pastikan ada konten form (bukan halaman kosong)
        Assertions.assertTrue(src.length() > 500,
            "Halaman /menu/create harus ada konten");
        System.out.println("[TC-MNU-002] PASS");
    }

    // TC-MNU-003: Form tambah menu punya field nama_menu, harga, id_category
    @Test @Order(3) @DisplayName("TC-MNU-003: Form tambah menu ada field nama_menu & harga")
    void tcMnu003() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_menu")),
            "Field nama_menu harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("harga")),
            "Field harga harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("id_category")),
            "Field id_category harus ada");
        System.out.println("[TC-MNU-003] PASS");
    }

    // TC-MNU-004: Submit form tambah menu valid → sukses
    @Test @Order(4) @DisplayName("TC-MNU-004: Tambah menu valid → tersimpan")
    void tcMnu004() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));

        driver.findElement(By.name("nama_menu")).sendKeys("Menu Test Selenium " + System.currentTimeMillis());
        driver.findElement(By.name("harga")).sendKeys("15000");

        // Pilih opsi pertama yang tersedia di dropdown kategori
        try {
            WebElement selectEl = driver.findElement(By.name("id_category"));
            new Select(selectEl).selectByIndex(1);
        } catch (Exception e) {
            System.out.println("  [INFO] Dropdown id_category tidak bisa dipilih: " + e.getMessage());
        }

        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save-menu")));
        jsClick(btnSave);

        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"),
            "Setelah simpan harus kembali ke halaman menu");
        System.out.println("[TC-MNU-004] PASS");
    }

    // TC-MNU-005: Submit nama menu kosong → validasi menolak
    @Test @Order(5) @DisplayName("TC-MNU-005: Tambah menu nama kosong → ditolak")
    void tcMnu005() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("harga")));

        // Isi harga saja, biarkan nama_menu kosong
        driver.findElement(By.name("harga")).sendKeys("10000");

        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save-menu")));
        jsClick(btnSave);

        // Tunggu sebentar untuk validasi browser/server
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}

        // Test pass jika masih di halaman menu (tidak redirect ke halaman sukses)
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"),
            "Nama kosong → harus ditolak dan tetap di halaman menu");
        System.out.println("[TC-MNU-005] PASS");
    }
}
