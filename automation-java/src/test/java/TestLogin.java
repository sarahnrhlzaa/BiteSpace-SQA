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
    static final String BASE = "http://localhost:8081/index.php";

    @BeforeAll
    static void setup() {
        WebDriverManager.chromedriver().setup();
        ChromeOptions o = new ChromeOptions();
        o.addArguments("--no-sandbox", "--disable-dev-shm-usage", "--disable-gpu");
        driver = new ChromeDriver(o);
        driver.manage().window().maximize();
        driver.manage().timeouts().implicitlyWait(Duration.ofSeconds(10));
        wait = new WebDriverWait(driver, Duration.ofSeconds(20));
    }

    @AfterAll
    static void teardown() { if (driver != null) driver.quit(); }

    void isiFormLogin(String username, String password) {
        driver.get(BASE + "/logout");
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}
        driver.get(BASE + "/login");
        WebElement usernameField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        usernameField.clear();
        usernameField.sendKeys(username);
        WebElement passwordField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("password")));
        passwordField.clear();
        passwordField.sendKeys(password);
        WebElement btn = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-submit")));
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", btn);
    }

    // TC-LGN-001: Halaman login terbuka, form tersedia
    @Test @Order(1) @DisplayName("TC-LGN-001: Halaman login terbuka")
    void tcLgn001() {
        driver.get(BASE + "/login");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("password")));
        Assertions.assertNotNull(driver.findElement(By.cssSelector("button.btn-submit")));
        System.out.println("[TC-LGN-001] PASS");
    }

    // TC-LGN-002: Login dengan kredensial valid (sarah/sarah123) → redirect ke dashboard
    @Test @Order(2) @DisplayName("TC-LGN-002: Login sarah/sarah123 → dashboard")
    void tcLgn002() {
        isiFormLogin("sarah", "sarah123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-LGN-002] PASS");
    }

    // TC-LGN-003: Login dengan password salah → tetap di login
    @Test @Order(3) @DisplayName("TC-LGN-003: Login password salah → ditolak")
    void tcLgn003() {
        isiFormLogin("sarah", "passwordSalah999");
        wait.until(ExpectedConditions.not(ExpectedConditions.urlContains("dashboard")));
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-LGN-003] PASS");
    }

    // TC-LGN-004: Login dengan username yang tidak ada → ditolak
    @Test @Order(4) @DisplayName("TC-LGN-004: Login username tidak ada → ditolak")
    void tcLgn004() {
        isiFormLogin("userTidakAda999", "apapun");
        wait.until(ExpectedConditions.not(ExpectedConditions.urlContains("dashboard")));
        Assertions.assertFalse(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-LGN-004] PASS");
    }

    // TC-LGN-005: Login dengan akun kedua (neyza/neyza123) → berhasil
    @Test @Order(5) @DisplayName("TC-LGN-005: Login neyza/neyza123 → dashboard")
    void tcLgn005() {
        isiFormLogin("neyza", "neyza123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        System.out.println("[TC-LGN-005] PASS");
    }

    // TC-LGN-006: Akses dashboard tanpa login → redirect ke login
    @Test @Order(6) @DisplayName("TC-LGN-006: Dashboard tanpa login → redirect login")
    void tcLgn006() {
        driver.get(BASE + "/logout");
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}
        driver.get(BASE + "/dashboard");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-LGN-006] PASS");
    }

    // TC-LGN-007: Logout → kembali ke halaman login
    @Test @Order(7) @DisplayName("TC-LGN-007: Logout → kembali login")
    void tcLgn007() {
        isiFormLogin("sarah", "sarah123");
        wait.until(ExpectedConditions.urlContains("dashboard"));
        driver.get(BASE + "/logout");
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-LGN-007] PASS");
    }
}
