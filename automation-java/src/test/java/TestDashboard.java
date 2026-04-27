import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestDashboard.java
 * Selenium UI Test – Fitur Dashboard BiteSpace CI4
 */
public class TestDashboard {

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

    void loginAsAdmin() {
        driver.get(BASE_URL + "/login");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("username"))).sendKeys("sarah");
        driver.findElement(By.name("password")).sendKeys("sarah123");
        driver.findElement(By.cssSelector("button.btn-submit")).click();
        wait.until(ExpectedConditions.urlContains("dashboard"));
    }

    // TC-08: Dashboard tampil setelah login
    @Test @DisplayName("TC-08: Dashboard berhasil dimuat setelah login")
    void tc08_dashboardTampil() {
        loginAsAdmin();
        Assertions.assertTrue(driver.getCurrentUrl().contains("dashboard"));
        Assertions.assertTrue(driver.getPageSource().contains("Dashboard"));
        System.out.println("[TC-08] PASS: Dashboard tampil.");
    }

    // TC-09: Navigasi ke halaman Menu
    @Test @DisplayName("TC-09: Navigasi ke halaman Menu berhasil")
    void tc09_navigasiMenu() {
        loginAsAdmin();
        driver.get(BASE_URL + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"));
        System.out.println("[TC-09] PASS: Halaman menu terbuka.");
    }

    // TC-10: Navigasi ke halaman Transaksi
    @Test @DisplayName("TC-10: Navigasi ke halaman Transaksi berhasil")
    void tc10_navigasiTransaksi() {
        loginAsAdmin();
        driver.get(BASE_URL + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("transaksi"));
        System.out.println("[TC-10] PASS: Halaman transaksi terbuka.");
    }

    // TC-11: Navigasi ke halaman Promo
    @Test @DisplayName("TC-11: Navigasi ke halaman Promo berhasil")
    void tc11_navigasiPromo() {
        loginAsAdmin();
        driver.get(BASE_URL + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("promo"));
        System.out.println("[TC-11] PASS: Halaman promo terbuka.");
    }

    // TC-12: Navigasi ke halaman Meja (Table)
    @Test @DisplayName("TC-12: Navigasi ke halaman Meja berhasil")
    void tc12_navigasiTable() {
        loginAsAdmin();
        driver.get(BASE_URL + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("table"));
        System.out.println("[TC-12] PASS: Halaman meja terbuka.");
    }

    // TC-13: Navigasi ke halaman Employee
    @Test @DisplayName("TC-13: Navigasi ke halaman Employee berhasil")
    void tc13_navigasiEmployee() {
        loginAsAdmin();
        driver.get(BASE_URL + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("employee"));
        System.out.println("[TC-13] PASS: Halaman employee terbuka.");
    }
}
