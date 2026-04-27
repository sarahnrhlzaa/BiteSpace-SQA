import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestLogin.java
 * Selenium UI Test – Fitur Login & Logout BiteSpace CI4
 * Server: http://localhost:8081
 */
public class TestLogin {

    static WebDriver driver;
    static WebDriverWait wait;
    static final String BASE_URL = "http://localhost:8081";

    @BeforeAll
    static void setup() {
        WebDriverManager.chromedriver().setup();
        ChromeOptions opt = new ChromeOptions();
        opt.addArguments("--no-sandbox", "--disable-dev-shm-usage");
        driver = new ChromeDriver(opt);
        driver.manage().window().maximize();
        wait = new WebDriverWait(driver, Duration.ofSeconds(10));
    }

    @AfterAll
    static void teardown() { if (driver != null) driver.quit(); }

    void submitForm(String username, String password) {
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("username"))).sendKeys(username);
        driver.findElement(By.name("password")).sendKeys(password);
        driver.findElement(By.cssSelector("button.btn-submit")).click();
    }

    // TC-01: Halaman login terbuka
    @Test @DisplayName("TC-01: Halaman login terbuka")
    void tc01_halamanLoginTerbuka() {
        driver.get(BASE_URL + "/login");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("password")));
        System.out.println("[TC-01] PASS: Halaman login terbuka.");
    }

    // TC-02: Login valid → masuk dashboard
    @Test @DisplayName("TC-02: Login sukses sarah/sarah123")
    void tc02_loginValid() {
        driver.get(BASE_URL + "/login");
        submitForm("sarah", "sarah123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-02] PASS: Login berhasil.");
    }

    // TC-03: Login password salah → tetap di login
    @Test @DisplayName("TC-03: Login gagal – password salah")
    void tc03_loginPasswordSalah() {
        driver.get(BASE_URL + "/login");
        submitForm("sarah", "salah999");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-03] PASS: Login ditolak.");
    }

    // TC-04: Login username tidak ada → ditolak
    @Test @DisplayName("TC-04: Login gagal – username tidak terdaftar")
    void tc04_loginUsernameAsal() {
        driver.get(BASE_URL + "/login");
        submitForm("userAsal999", "apapun");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-04] PASS: Username tidak ada ditolak.");
    }

    // TC-05: Login field kosong → ditolak
    @Test @DisplayName("TC-05: Login gagal – field kosong")
    void tc05_loginFieldKosong() {
        driver.get(BASE_URL + "/login");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("button.btn-submit"))).click();
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-05] PASS: Field kosong ditolak.");
    }

    // TC-06: Dashboard tanpa login → redirect ke login
    @Test @DisplayName("TC-06: Akses dashboard tanpa login → redirect login")
    void tc06_dashboardTanpaLogin() {
        driver.get(BASE_URL + "/logout");
        driver.get(BASE_URL + "/dashboard");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-06] PASS: Redirect ke login.");
    }

    // TC-07: Logout berhasil
    @Test @DisplayName("TC-07: Logout berhasil → kembali ke login")
    void tc07_logoutBerhasil() {
        driver.get(BASE_URL + "/login");
        submitForm("sarah", "sarah123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        driver.get(BASE_URL + "/logout");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-07] PASS: Logout berhasil.");
    }
}
