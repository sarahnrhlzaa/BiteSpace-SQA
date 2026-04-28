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
    // FIX: tambah index.php sesuai $indexPage CI4
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
        driver.get(BASE + "/login");
        WebElement usernameField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        usernameField.clear();
        usernameField.sendKeys("sarah");
        WebElement passwordField = wait.until(
            ExpectedConditions.visibilityOfElementLocated(By.name("password")));
        passwordField.clear();
        passwordField.sendKeys("sarah123");
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-submit"))).click();
        wait.until(ExpectedConditions.urlContains("dashboard"));
    }

    // TC-D01: Dashboard tampil setelah login
    @Test @Order(1) @DisplayName("TC-D01: Dashboard berhasil dimuat setelah login")
    void tcD01() {
        loginAsAdmin();
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        Assertions.assertTrue(driver.getPageSource().contains("Dashboard"));
        System.out.println("[TC-D01] PASS");
    }

    // TC-D02: Navigasi ke halaman Menu
    @Test @Order(2) @DisplayName("TC-D02: Navigasi ke halaman Menu berhasil")
    void tcD02() {
        loginAsAdmin();
        driver.get(BASE + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"));
        System.out.println("[TC-D02] PASS");
    }

    // TC-D03: Navigasi ke halaman Transaksi
    @Test @Order(3) @DisplayName("TC-D03: Navigasi ke halaman Transaksi berhasil")
    void tcD03() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("transaksi"));
        System.out.println("[TC-D03] PASS");
    }

    // TC-D04: Navigasi ke halaman Promo
    @Test @Order(4) @DisplayName("TC-D04: Navigasi ke halaman Promo berhasil")
    void tcD04() {
        loginAsAdmin();
        driver.get(BASE + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("promo"));
        System.out.println("[TC-D04] PASS");
    }

    // TC-D05: Navigasi ke halaman Meja
    @Test @Order(5) @DisplayName("TC-D05: Navigasi ke halaman Meja berhasil")
    void tcD05() {
        loginAsAdmin();
        driver.get(BASE + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("table"));
        System.out.println("[TC-D05] PASS");
    }

    // TC-D06: Navigasi ke halaman Employee
    @Test @Order(6) @DisplayName("TC-D06: Navigasi ke halaman Employee berhasil")
    void tcD06() {
        loginAsAdmin();
        driver.get(BASE + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("employee"));
        System.out.println("[TC-D06] PASS");
    }
}
