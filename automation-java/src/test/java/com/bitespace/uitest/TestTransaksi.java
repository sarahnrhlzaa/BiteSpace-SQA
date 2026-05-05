package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.support.ui.*;

/**
 * TestTransaksi.java — UI Test Transaksi/POS BiteSpace CI4
 * Route: GET /transaksi → TransaksiController::index()
 *
 * TC-TRX-001: Halaman transaksi tampil setelah login
 * TC-TRX-002: Halaman transaksi memuat konten yang tidak kosong
 * TC-TRX-003: Akses transaksi tanpa login → redirect ke login
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestTransaksi extends BaseTest {

    // TC-TRX-001: Halaman transaksi tampil setelah login
    @Test @Order(1) @DisplayName("TC-TRX-001: Halaman transaksi tampil")
    void tcTrx001() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("Transaksi") || src.contains("POS") ||
            src.contains("Menu") || src.contains("transaksi"),
            "Halaman transaksi harus ada konten");
        System.out.println("[TC-TRX-001] PASS");
    }

    // TC-TRX-002: Halaman transaksi memuat konten yang tidak kosong
    @Test @Order(2) @DisplayName("TC-TRX-002: Halaman transaksi ada konten")
    void tcTrx002() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        // Pastikan halaman punya konten yang cukup (bukan halaman error)
        wait.until(d -> d.getPageSource().length() > 500);
        Assertions.assertTrue(driver.getPageSource().length() > 500,
            "Halaman transaksi harus memiliki konten yang cukup");
        System.out.println("[TC-TRX-002] PASS");
    }

    // TC-TRX-003: Akses transaksi tanpa login → redirect ke login
    @Test @Order(3) @DisplayName("TC-TRX-003: Transaksi tanpa login → redirect login")
    void tcTrx003() {
        try {
            driver.get(BASE + "/logout");
            Thread.sleep(1500);
        } catch (Exception ignored) {}

        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"),
            "Akses transaksi tanpa login harus redirect ke halaman login");
        System.out.println("[TC-TRX-003] PASS");
    }
}
