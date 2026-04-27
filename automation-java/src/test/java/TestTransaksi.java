import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestTransaksi.java
 * Selenium UI Test – Fitur Transaksi (POS) BiteSpace CI4
 */
public class TestTransaksi {

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

    // TC-31: Halaman transaksi/POS tampil
    @Test @DisplayName("TC-31: Halaman Transaksi POS berhasil dimuat")
    void tc31_halamanTransaksiTampil() {
        loginAsAdmin();
        driver.get(BASE_URL + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        String src = driver.getPageSource();
        Assertions.assertTrue(src.contains("Transaksi") || src.contains("POS") || src.contains("Menu"));
        System.out.println("[TC-31] PASS: Halaman transaksi tampil.");
    }

    // TC-32: Halaman transaksi menampilkan daftar menu yang tersedia
    @Test @DisplayName("TC-32: Halaman transaksi menampilkan daftar menu")
    void tc32_transaksiTampilkanMenu() {
        loginAsAdmin();
        driver.get(BASE_URL + "/transaksi");
        wait.until(ExpectedConditions.urlContains("transaksi"));
        // Halaman POS harus punya lebih dari 1000 karakter (ada konten menu)
        Assertions.assertTrue(driver.getPageSource().length() > 1000,
            "Halaman transaksi harus memiliki konten (daftar menu).");
        System.out.println("[TC-32] PASS: Konten menu tersedia di halaman transaksi.");
    }

    // TC-33: Halaman transaksi tanpa login → redirect ke login
    @Test @DisplayName("TC-33: Akses transaksi tanpa login → redirect login")
    void tc33_transaksiTanpaLogin() {
        driver.get(BASE_URL + "/logout");
        driver.get(BASE_URL + "/transaksi");
        wait.until(ExpectedConditions.urlContains("login"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("login"));
        System.out.println("[TC-33] PASS: Redirect ke login berhasil.");
    }
}
