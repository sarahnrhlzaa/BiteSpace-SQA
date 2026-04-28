import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestPromo.java — UI Test Promo BiteSpace CI4
 * Field : name="kode_promo", name="nama_promo", name="nilai_diskon", dll
 * Submit: button.btn-submit-bs
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestPromo {

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

    // TC-13: Halaman promo tampil
    @Test @Order(1) @DisplayName("TC-13: Halaman promo tampil")
    void tc13() {
        loginAsAdmin();
        driver.get(BASE + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(
            driver.getPageSource().contains("Promo") || driver.getPageSource().contains("promo")
        );
        System.out.println("[TC-13] PASS");
    }

    // TC-14: Halaman tambah promo bisa diakses admin
    @Test @Order(2) @DisplayName("TC-14: Halaman /promo/create bisa diakses admin")
    void tc14() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        System.out.println("[TC-14] PASS");
    }

    // TC-15: Form promo ada field kode_promo, nama_promo, nilai_diskon
    @Test @Order(3) @DisplayName("TC-15: Form promo ada field kode_promo & nama_promo")
    void tc15() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kode_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("kode_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("nilai_diskon")));
        System.out.println("[TC-15] PASS");
    }

    // TC-16: Submit promo kosong → validasi menolak
    @Test @Order(4) @DisplayName("TC-16: Tambah promo field kosong → ditolak")
    void tc16() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-submit-bs")));
        driver.findElement(By.cssSelector("button.btn-submit-bs")).click();
        // FIX: tunggu halaman stabil setelah submit
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("promo"));
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-16] PASS");
    }
}
