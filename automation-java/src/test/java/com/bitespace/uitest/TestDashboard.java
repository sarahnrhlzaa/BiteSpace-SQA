package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.support.ui.*;

/**
 * TestDashboard.java — UI Test Dashboard BiteSpace CI4
 * Route: GET /dashboard → DashboardController::index()
 *
 * TC-DSH-001: Dashboard tampil setelah login
 * TC-DSH-002: Navigasi ke halaman Menu
 * TC-DSH-003: Navigasi ke halaman Transaksi
 * TC-DSH-004: Navigasi ke halaman Promo
 * TC-DSH-005: Navigasi ke halaman Meja (table)
 * TC-DSH-006: Navigasi ke halaman Employee
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestDashboard extends BaseTest {

    // TC-DSH-001: Dashboard tampil setelah login
    @Test @Order(1) @DisplayName("TC-DSH-001: Dashboard berhasil dimuat setelah login")
    void tcDsh001() {
        loginAsAdmin();
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"),
            "URL harus mengandung 'dashboard'");
        Assertions.assertTrue(driver.getPageSource().contains("Dashboard"),
            "Halaman harus menampilkan teks 'Dashboard'");
        System.out.println("[TC-DSH-001] PASS");
    }

    // TC-DSH-002: Navigasi ke halaman Menu
    @Test @Order(2) @DisplayName("TC-DSH-002: Navigasi ke halaman Menu berhasil")
    void tcDsh002() {
        loginAsAdmin();
        driver.get(BASE + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"),
            "URL harus mengandung 'menu'");
        System.out.println("[TC-DSH-002] PASS");
    }

    // TC-DSH-003: Navigasi ke halaman Transaksi
    @Test @Order(3) @DisplayName("TC-DSH-003: Navigasi ke halaman Transaksi berhasil")
    void tcDsh003() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("transaksi"),
            "URL harus mengandung 'transaksi'");
        System.out.println("[TC-DSH-003] PASS");
    }

    // TC-DSH-004: Navigasi ke halaman Promo
    @Test @Order(4) @DisplayName("TC-DSH-004: Navigasi ke halaman Promo berhasil")
    void tcDsh004() {
        loginAsAdmin();
        driver.get(BASE + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("promo"),
            "URL harus mengandung 'promo'");
        System.out.println("[TC-DSH-004] PASS");
    }

    // TC-DSH-005: Navigasi ke halaman Meja (table)
    @Test @Order(5) @DisplayName("TC-DSH-005: Navigasi ke halaman Meja berhasil")
    void tcDsh005() {
        loginAsAdmin();
        driver.get(BASE + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("table"),
            "URL harus mengandung 'table'");
        System.out.println("[TC-DSH-005] PASS");
    }

    // TC-DSH-006: Navigasi ke halaman Employee
    @Test @Order(6) @DisplayName("TC-DSH-006: Navigasi ke halaman Employee berhasil")
    void tcDsh006() {
        loginAsAdmin();
        driver.get(BASE + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("employee"),
            "URL harus mengandung 'employee'");
        System.out.println("[TC-DSH-006] PASS");
    }
}
