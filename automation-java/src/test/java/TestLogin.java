import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestLogin.java — UI Test Login & Logout BiteSpace CI4
 * Field : name="username", name="password"
 * Submit: button.btn-submit
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestLogin {

    static WebDriver driver;
    static WebDriverWait wait;
    // FIX: tambah index.php sesuai $indexPage CI4
    static final String BASE = "http://localhost:8081/index.php";

    @BeforeAll
    static void setup() {
        WebDriverManager.chromedriver().setup();
        ChromeOptions o = new ChromeOptions();
        o.addArguments("--no-sandbox", "--disable-dev-shm-usage", "--disable-gpu");
        driver = new ChromeDriver(o);
        driver.manage().window().maximize();
        // FIX: naikkan implicit wait jadi 10 detik supaya stabil di server lokal
        driver.manage().timeouts().implicitlyWait(Duration.ofSeconds(10));
        wait = new WebDriverWait(driver, Duration.ofSeconds(20));
    }

    @AfterAll
    static void teardown() { if (driver != null) driver.quit(); }

    void isiFormLogin(String username, String password) {
        driver.get(BASE + "/logout");
        driver.get(BASE + "/login");
        // FIX: tunggu form benar-benar visible sebelum interaksi
        WebElement usernameField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        usernameField.clear();
        usernameField.sendKeys(username);
        WebElement passwordField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("password")));
        passwordField.clear();
        passwordField.sendKeys(password);
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-submit"))).click();
    }

    // TC-01: Halaman login terbuka, form tersedia
    @Test @Order(1) @DisplayName("TC-01: Halaman login terbuka")
    void tc01() {
        driver.get(BASE + "/login");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("password")));
        Assertions.assertNotNull(driver.findElement(By.cssSelector("button.btn-submit")));
        System.out.println("[TC-01] PASS");
    }

    // TC-02: Login valid sarah/sarah123 → redirect dashboard
    @Test @Order(2) @DisplayName("TC-02: Login sarah/sarah123 → dashboard")
    void tc02() {
        isiFormLogin("sarah", "sarah123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-02] PASS");
    }

    // TC-03: Login password salah → tetap di login
    @Test @Order(3) @DisplayName("TC-03: Login password salah → ditolak")
    void tc03() {
        isiFormLogin("sarah", "passwordSalah999");
        // FIX: tunggu sampai url stabil (bukan dashboard)
        wait.until(ExpectedConditions.not(ExpectedConditions.urlContains("dashboard")));
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-03] PASS");
    }

    // TC-04: Login username tidak ada → ditolak
    @Test @Order(4) @DisplayName("TC-04: Login username tidak ada → ditolak")
    void tc04() {
        isiFormLogin("userTidakAda999", "apapun");
        wait.until(ExpectedConditions.not(ExpectedConditions.urlContains("dashboard")));
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-04] PASS");
    }

    // TC-05: Login neyza/neyza123 valid
    @Test @Order(5) @DisplayName("TC-05: Login neyza/neyza123 → dashboard")
    void tc05() {
        isiFormLogin("neyza", "neyza123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-05] PASS");
    }

    // TC-06: Akses dashboard tanpa login → redirect login
    @Test @Order(6) @DisplayName("TC-06: Dashboard tanpa login → redirect login")
    void tc06() {
        driver.get(BASE + "/logout");
        driver.get(BASE + "/dashboard");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-06] PASS");
    }

    // TC-07: Logout → kembali ke login
    @Test @Order(7) @DisplayName("TC-07: Logout → kembali login")
    void tc07() {
        isiFormLogin("sarah", "sarah123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        driver.get(BASE + "/logout");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-07] PASS");
    }
}
