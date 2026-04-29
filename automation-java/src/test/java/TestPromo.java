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

    void jsClick(WebElement element) {
        ((JavascriptExecutor) driver).executeScript("arguments[0].scrollIntoView(true);", element);
        ((JavascriptExecutor) driver).executeScript("arguments[0].click();", element);
    }

    // TC-PRM-001: Halaman promo tampil
    @Test @Order(1) @DisplayName("TC-PRM-001: Halaman promo tampil")
    void tcPrm001() {
        loginAsAdmin();
        driver.get(BASE + "/promo");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(
            driver.getPageSource().contains("Promo") || driver.getPageSource().contains("promo"));
        System.out.println("[TC-PRM-001] PASS");
    }

    // TC-PRM-002: Halaman tambah promo bisa diakses admin
    @Test @Order(2) @DisplayName("TC-PRM-002: Halaman /promo/create bisa diakses admin")
    void tcPrm002() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        System.out.println("[TC-PRM-002] PASS");
    }

    // TC-PRM-003: Form promo ada field kode_promo, nama_promo, nilai_diskon
    @Test @Order(3) @DisplayName("TC-PRM-003: Form promo ada field kode_promo & nama_promo")
    void tcPrm003() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kode_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("kode_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_promo")));
        Assertions.assertNotNull(driver.findElement(By.name("nilai_diskon")));
        System.out.println("[TC-PRM-003] PASS");
    }

    // TC-PRM-004: Submit promo kosong → validasi menolak
    @Test @Order(4) @DisplayName("TC-PRM-004: Tambah promo field kosong → ditolak")
    void tcPrm004() {
        loginAsAdmin();
        driver.get(BASE + "/promo/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kode_promo")));
        WebElement btnSubmit = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-submit-bs")));
        jsClick(btnSubmit);
        wait.until(ExpectedConditions.urlContains("promo"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("promo"));
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-PRM-004] PASS");
    }
}
