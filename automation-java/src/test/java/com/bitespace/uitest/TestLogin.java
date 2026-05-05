package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;

/**
 * TestLogin.java — UI Test Login & Logout BiteSpace CI4
 *
 * TC-LGN-001: Halaman login terbuka, form tersedia
 * TC-LGN-002: Login neyza/neyza123 → redirect ke dashboard
 * TC-LGN-003: Login password salah → tetap di login
 * TC-LGN-004: Login username tidak ada → tetap di login
 * TC-LGN-005: Login username valid tapi password salah → ditolak
 * TC-LGN-006: Akses dashboard tanpa login → redirect ke login
 * TC-LGN-007: Logout → kembali ke halaman login
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestLogin extends BaseTest {

    // TC-LGN-001: Halaman login terbuka
    @Test @Order(1) @DisplayName("TC-LGN-001: Halaman login terbuka")
    void tcLgn001() {
        driver.get(BASE + "/login");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("password")));
        Assertions.assertNotNull(driver.findElement(By.cssSelector("button.btn-submit")));
        System.out.println("[TC-LGN-001] PASS");
    }

    // TC-LGN-002: Login neyza/neyza123 → dashboard
    @Test @Order(2) @DisplayName("TC-LGN-002: Login neyza/neyza123 → dashboard")
    void tcLgn002() {
        loginAs(ADMIN_USER, ADMIN_PASS);
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-LGN-002] PASS");
    }

    // TC-LGN-003: Login password salah → ditolak
    @Test @Order(3) @DisplayName("TC-LGN-003: Login password salah → ditolak")
    void tcLgn003() {
        submitLoginForm(ADMIN_USER, "passwordSalah999");
        try { Thread.sleep(3000); } catch (InterruptedException ignored) {}
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-LGN-003] PASS");
    }

    // TC-LGN-004: Login username tidak ada → ditolak
    @Test @Order(4) @DisplayName("TC-LGN-004: Login username tidak ada → ditolak")
    void tcLgn004() {
        submitLoginForm("userTidakAda999", "apapun");
        try { Thread.sleep(3000); } catch (InterruptedException ignored) {}
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-LGN-004] PASS");
    }

    // TC-LGN-005: Login username valid tapi password salah → ditolak
    // Sengaja TIDAK login berhasil agar tidak ada sesi yang nyangkut sebelum tcLgn007
    @Test @Order(5) @DisplayName("TC-LGN-005: Login user valid password salah → ditolak")
    void tcLgn005() {
        submitLoginForm(ADMIN_USER, "passwordSalahUntukTC005");
        try { Thread.sleep(3000); } catch (InterruptedException ignored) {}
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"),
            "Login dengan password salah harus ditolak");
        System.out.println("[TC-LGN-005] PASS");
    }

    // TC-LGN-006: Akses dashboard tanpa login → redirect ke login
    @Test @Order(6) @DisplayName("TC-LGN-006: Dashboard tanpa login → redirect login")
    void tcLgn006() {
        driver.get(BASE + "/dashboard");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-LGN-006] PASS");
    }

    // TC-LGN-007: Logout → kembali ke halaman login
    @Test @Order(7) @DisplayName("TC-LGN-007: Logout → kembali login")
    void tcLgn007() {
        loginAs(ADMIN_USER, ADMIN_PASS);
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));

        driver.get(BASE + "/logout");
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-LGN-007] PASS");
    }
}
