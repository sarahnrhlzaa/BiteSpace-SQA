import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestTable.java
 * Selenium UI Test – Fitur Manajemen Meja BiteSpace CI4
 */
public class TestTable {

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

    // TC-22: Halaman daftar meja tampil
    @Test @DisplayName("TC-22: Halaman daftar meja berhasil dimuat")
    void tc22_halamanMejaTampil() {
        loginAsAdmin();
        driver.get(BASE_URL + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getPageSource().contains("Meja") || driver.getPageSource().contains("Table"));
        System.out.println("[TC-22] PASS: Halaman meja tampil.");
    }

    // TC-23: Halaman tambah meja bisa diakses
    @Test @DisplayName("TC-23: Halaman tambah meja bisa diakses")
    void tc23_halamanTambahMeja() {
        loginAsAdmin();
        driver.get(BASE_URL + "/table/create");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getPageSource().contains("Tambah") || driver.getPageSource().contains("Meja"));
        System.out.println("[TC-23] PASS: Halaman tambah meja terbuka.");
    }

    // TC-24: Form tambah meja punya field nomor meja & kapasitas
    @Test @DisplayName("TC-24: Form tambah meja punya field nomor & kapasitas")
    void tc24_formTambahMejaAdaField() {
        loginAsAdmin();
        driver.get(BASE_URL + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nomor_meja")));
        Assertions.assertNotNull(driver.findElement(By.name("nomor_meja")));
        Assertions.assertNotNull(driver.findElement(By.name("kapasitas")));
        System.out.println("[TC-24] PASS: Field form meja ada.");
    }

    // TC-25: Submit meja tanpa nomor meja → validasi menolak
    @Test @DisplayName("TC-25: Tambah meja gagal jika nomor meja kosong")
    void tc25_tambahMejaKosong() {
        loginAsAdmin();
        driver.get(BASE_URL + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kapasitas")));
        driver.findElement(By.name("kapasitas")).sendKeys("4");
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-25] PASS: Validasi nomor meja kosong berhasil.");
    }
}
