import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestDashboard.java — UI Test Dashboard BiteSpace CI4
 * Route: GET /dashboard → DashboardController::index()
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestDashboard {

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

    void loginAsAdmin() {
        driver.get(BASE + "/logout");
        wait.until(ExpectedConditions.urlContains("login"));
        driver.get(BASE + "/login");
        WebElement usernameField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        usernameField.clear();
        usernameField.sendKeys("sarah");
        WebElement passwordField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("password")));
        passwordField.clear();
        passwordField.sendKeys("sarah123");
        WebElement btn = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-submit")));
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", btn);
        wait.until(ExpectedConditions.urlContains("dashboard"));
    }

    // TC-DSH-001: Dashboard tampil setelah login
    @Test @Order(1) @DisplayName("TC-DSH-001: Dashboard berhasil dimuat setelah login")
    void tcDsh001() {
        loginAsAdmin();
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        Assertions.assertTrue(driver.getPageSource().contains("Dashboard"));
        System.out.println("[TC-DSH-001] PASS");
    }

    // TC-DSH-002: Navigasi ke halaman Menu
    @Test @Order(2) @DisplayName("TC-DSH-002: Navigasi ke halaman Menu berhasil")
    void tcDsh002() {
        loginAsAdmin();
        driver.get(BASE + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"));
        System.out.println("[TC-DSH-002] PASS");
    }

    // TC-DSH-003: Navigasi ke halaman Transaksi
    @Test @Order(3) @DisplayName("TC-DSH-003: Navigasi ke halaman Transaksi berhasil")
    void tcDsh003() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("transaksi"));
        System.out.println("[TC-DSH-003] PASS");
    }

    // TC-DSH-004: Navigasi ke halaman Promo
    @Test @Order(4) @DisplayName("TC-DSH-004: Navigasi ke halaman Promo berhasil")
    void tcDsh004() {
        loginAsAdmin();
        driver.get(BASE + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("promo"));
        System.out.println("[TC-DSH-004] PASS");
    }

    // TC-DSH-005: Navigasi ke halaman Meja
    @Test @Order(5) @DisplayName("TC-DSH-005: Navigasi ke halaman Meja berhasil")
    void tcDsh005() {
        loginAsAdmin();
        driver.get(BASE + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("table"));
        System.out.println("[TC-DSH-005] PASS");
    }

    // TC-DSH-006: Navigasi ke halaman Employee
    @Test @Order(6) @DisplayName("TC-DSH-006: Navigasi ke halaman Employee berhasil")
    void tcDsh006() {
        loginAsAdmin();
        driver.get(BASE + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("employee"));
        System.out.println("[TC-DSH-006] PASS");
    }
}
