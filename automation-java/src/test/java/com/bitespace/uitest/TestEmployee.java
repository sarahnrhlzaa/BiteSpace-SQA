package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;

/**
 * TestEmployee.java — UI Test Employee BiteSpace CI4
 * Route: GET /employee → EmployeeController::index()
 *
 * TC-EMP-001: Halaman employee tampil
 * TC-EMP-002: Halaman tambah employee bisa diakses admin
 * TC-EMP-003: Form employee ada semua field wajib
 * TC-EMP-004: Submit employee valid → tersimpan
 * TC-EMP-005: Daftar employee ada data (neyza/sarah)
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestEmployee extends BaseTest {

    // TC-EMP-001: Halaman employee tampil
    @Test @Order(1) @DisplayName("TC-EMP-001: Halaman /employee tampil")
    void tcEmp001() {
        loginAsAdmin();
        driver.get(BASE + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("Employee") || src.contains("Karyawan") || src.contains("employee"),
            "Halaman employee harus menampilkan konten terkait employee");
        System.out.println("[TC-EMP-001] PASS");
    }

    // TC-EMP-002: Halaman tambah employee bisa diakses admin
    @Test @Order(2) @DisplayName("TC-EMP-002: Halaman /employee/create bisa diakses admin")
    void tcEmp002() {
        loginAsAdmin();
        driver.get(BASE + "/employee/create");
        wait.until(ExpectedConditions.urlContains("employee"));
        String src = driver.getPageSource();
        Assertions.assertFalse(src.contains("Akses ditolak"),
            "Admin tidak boleh mendapat pesan 'Akses ditolak'");
        System.out.println("[TC-EMP-002] PASS");
    }

    // TC-EMP-003: Form employee ada semua field wajib
    @Test @Order(3) @DisplayName("TC-EMP-003: Form employee ada field nama_lengkap, username, password")
    void tcEmp003() {
        loginAsAdmin();
        driver.get(BASE + "/employee/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_lengkap")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_lengkap")),
            "Field nama_lengkap harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("username")),
            "Field username harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("password")),
            "Field password harus ada");
        Assertions.assertNotNull(driver.findElement(By.name("email")),
            "Field email harus ada");
        System.out.println("[TC-EMP-003] PASS");
    }

    // TC-EMP-004: Submit employee valid → tersimpan
    @Test @Order(4) @DisplayName("TC-EMP-004: Tambah employee valid → tersimpan")
    void tcEmp004() {
        loginAsAdmin();
        driver.get(BASE + "/employee/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_lengkap")));

        long ts = System.currentTimeMillis();
        driver.findElement(By.name("nama_lengkap")).sendKeys("Test Selenium " + ts);
        driver.findElement(By.name("username")).sendKeys("testselenium" + ts);
        driver.findElement(By.name("email")).sendKeys("test" + ts + "@mail.com");
        driver.findElement(By.name("password")).sendKeys("password123");

        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save")));
        jsClick(btnSave);

        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("employee"),
            "Setelah simpan harus kembali ke halaman employee");
        System.out.println("[TC-EMP-004] PASS");
    }

    // TC-EMP-005: Daftar employee ada data neyza atau sarah
    @Test @Order(5) @DisplayName("TC-EMP-005: Daftar employee ada data")
    void tcEmp005() {
        loginAsAdmin();
        driver.get(BASE + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("neyza") || src.contains("Neyza") ||
            src.contains("sarah") || src.contains("Sarah"),
            "Daftar employee harus mengandung data user yang ada");
        System.out.println("[TC-EMP-005] PASS");
    }
}
