import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestPromo.java
 * Selenium UI Test – Fitur Promo BiteSpace CI4
 */
public class TestPromo {

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

    // TC-18: Halaman promo tampil
    @Test @DisplayName("TC-18: Halaman daftar promo berhasil dimuat")
    void tc18_halamanPromoTampil() {
        loginAsAdmin();
        driver.get(BASE_URL + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(driver.getPageSource().contains("Promo") || driver.getPageSource().contains("promo"));
        System.out.println("[TC-18] PASS: Halaman promo tampil.");
    }

    // TC-19: Halaman tambah promo bisa diakses admin
    @Test @DisplayName("TC-19: Admin bisa akses halaman tambah promo")
    void tc19_halamanTambahPromo() {
        loginAsAdmin();
        driver.get(BASE_URL + "/promo/create");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        System.out.println("[TC-19] PASS: Halaman tambah promo bisa diakses.");
    }

    // TC-20: Form tambah promo punya field yang diperlukan
    @Test @DisplayName("TC-20: Form tambah promo punya field kode & nama promo")
    void tc20_formTambahPromoAdaField() {
        loginAsAdmin();
        driver.get(BASE_URL + "/promo/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kode_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("kode_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("nilai_diskon")));
        System.out.println("[TC-20] PASS: Field form promo ada.");
    }

    // TC-21: Submit promo kosong → validasi menolak
    @Test @DisplayName("TC-21: Tambah promo gagal jika field kosong")
    void tc21_tambahPromoKosong() {
        loginAsAdmin();
        driver.get(BASE_URL + "/promo/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.cssSelector("button[type='submit']")));
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-21] PASS: Validasi field kosong promo berhasil.");
    }
}
