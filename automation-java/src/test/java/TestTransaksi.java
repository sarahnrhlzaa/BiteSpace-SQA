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

    // TC-27: Halaman transaksi tampil setelah login
    @Test @Order(1) @DisplayName("TC-27: Halaman transaksi tampil")
    void tc27() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("Transaksi") || src.contains("POS") || src.contains("Menu")
        );
        System.out.println("[TC-27] PASS");
    }

    // TC-28: Halaman transaksi menampilkan konten menu (tidak kosong)
    @Test @Order(2) @DisplayName("TC-28: Halaman transaksi ada konten menu")
    void tc28() {
        loginAsAdmin();
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        // FIX: tunggu halaman selesai render penuh (JS-heavy POS page)
        wait.until(d -> d.getPageSource().length() > 1000);
        Assertions.assertTrue(driver.getPageSource().length() > 1000);
        System.out.println("[TC-28] PASS");
    }

    // TC-29: Akses transaksi tanpa login → redirect login
    @Test @Order(3) @DisplayName("TC-29: Transaksi tanpa login → redirect login")
    void tc29() {
        driver.get(BASE + "/logout");
        driver.get(BASE + "/transaksi");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-29] PASS");
    }
}
