package com.bitespace.uitest;

import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * BaseTest.java — Shared base class untuk semua UI Test BiteSpace CI4
 *
 * URL Struktur:
 *   - Login  : http://localhost:8081/index.php/login
 *   - Dashboard: http://localhost:8081/index.php/dashboard
 *   - Halaman lain: http://localhost:8081/index.php/{route}
 *
 * Kredensial:
 *   - Admin  : neyza / neyza123
 */
public class BaseTest {

    protected static WebDriver driver;
    protected static WebDriverWait wait;

    // BASE pakai index.php karena CI4 default config
    protected static final String BASE = "http://localhost:8081/index.php";

    // Kredensial admin yang valid
    protected static final String ADMIN_USER = "neyza";
    protected static final String ADMIN_PASS = "neyza123";

    @BeforeAll
    static void setupDriver() {
        WebDriverManager.chromedriver().setup();
        ChromeOptions options = new ChromeOptions();
        options.addArguments("--no-sandbox");
        options.addArguments("--disable-dev-shm-usage");
        options.addArguments("--disable-gpu");
        // options.addArguments("--headless"); // uncomment jika ingin headless
        driver = new ChromeDriver(options);
        driver.manage().window().maximize();
        driver.manage().timeouts().pageLoadTimeout(Duration.ofSeconds(30));
        // Jangan pakai implicitWait bersamaan dengan ExplicitWait
        driver.manage().timeouts().implicitlyWait(Duration.ofSeconds(0));
        wait = new WebDriverWait(driver, Duration.ofSeconds(25));
    }

    @AfterAll
    static void tearDownDriver() {
        if (driver != null) {
            driver.quit();
        }
    }

    /**
     * Login sebagai admin (neyza/neyza123).
     * Selalu logout dulu, lalu login ulang.
     */
    protected void loginAsAdmin() {
        loginAs(ADMIN_USER, ADMIN_PASS);
    }

    /**
     * Login dengan username dan password tertentu.
     * Setelah submit, tunggu redirect ke dashboard.
     */
    protected void loginAs(String username, String password) {
        // Logout dulu untuk bersihkan sesi
        try {
            driver.get(BASE + "/logout");
            Thread.sleep(1500);
        } catch (Exception ignored) {}

        // Buka halaman login
        driver.get(BASE + "/login");

        // Tunggu field username muncul
        WebElement usernameField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        usernameField.clear();
        usernameField.sendKeys(username);

        // Isi password
        WebElement passwordField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("password")));
        passwordField.clear();
        passwordField.sendKeys(password);

        // Klik tombol login via JS agar tidak terhalang overlay
        WebElement submitBtn = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-submit")));
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", submitBtn);

        // Tunggu redirect ke dashboard
        wait.until(ExpectedConditions.urlContains("dashboard"));
    }

    /**
     * Isi form login tapi TANPA menunggu redirect (untuk test credential salah).
     */
    protected void submitLoginForm(String username, String password) {
        try {
            driver.get(BASE + "/logout");
            Thread.sleep(1500);
        } catch (Exception ignored) {}

        driver.get(BASE + "/login");

        WebElement usernameField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        usernameField.clear();
        usernameField.sendKeys(username);

        WebElement passwordField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("password")));
        passwordField.clear();
        passwordField.sendKeys(password);

        WebElement submitBtn = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-submit")));
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", submitBtn);
    }

    /**
     * Helper: klik elemen via JavaScript (scroll + click)
     */
    protected void jsClick(WebElement element) {
        ((JavascriptExecutor) driver).executeScript("arguments[0].scrollIntoView(true);", element);
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", element);
    }
}
