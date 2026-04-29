import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestTransaksi.java — UI Test Transaksi/POS BiteSpace CI4
 * Route: GET /transaksi → TransaksiController::index()
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestTransaksi {

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
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}
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

    // TC-TRX-001: Halaman transaksi tampil setelah login
    @Test @Order(1) @DisplayName("TC-TRX-001: Halaman transaksi tampil")
    void tcTrx001() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("Transaksi") || src.contains("POS") || src.contains("Menu"));
        System.out.println("[TC-TRX-001] PASS");
    }

    // TC-TRX-002: Halaman transaksi memuat konten yang tidak kosong
    @Test @Order(2) @DisplayName("TC-TRX-002: Halaman transaksi ada konten menu")
    void tcTrx002() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        wait.until(d -> d.getPageSource().length() > 1000);
        Assertions.assertTrue(driver.getPageSource().length() > 1000);
        System.out.println("[TC-TRX-002] PASS");
    }

    // TC-TRX-003: Akses transaksi tanpa login → redirect ke login
    @Test @Order(3) @DisplayName("TC-TRX-003: Transaksi tanpa login → redirect login")
    void tcTrx003() {
        driver.get(BASE + "/logout");
        try { Thread.sleep(2000); } catch (InterruptedException ignored) {}
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-TRX-003] PASS");
    }
}
