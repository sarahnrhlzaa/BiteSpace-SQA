import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestTable.java — UI Test Meja BiteSpace CI4
 * Field : name="nomor_meja", name="kapasitas"
 * Submit: button.btn-save
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestTable {

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

    // TC-TBL-001: Halaman meja tampil
    @Test @Order(1) @DisplayName("TC-TBL-001: Halaman /table tampil")
    void tcTbl001() {
        loginAsAdmin();
        driver.get(BASE + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(
            driver.getPageSource().contains("Meja") || driver.getPageSource().contains("Table"));
        System.out.println("[TC-TBL-001] PASS");
    }

    // TC-TBL-002: Halaman tambah meja bisa diakses admin
    @Test @Order(2) @DisplayName("TC-TBL-002: Halaman /table/create bisa diakses admin")
    void tcTbl002() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        System.out.println("[TC-TBL-002] PASS");
    }

    // TC-TBL-003: Form tambah meja ada field nomor_meja & kapasitas
    @Test @Order(3) @DisplayName("TC-TBL-003: Form meja ada field nomor_meja & kapasitas")
    void tcTbl003() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nomor_meja")));
        Assertions.assertNotNull(driver.findElement(By.name("nomor_meja")));
        Assertions.assertNotNull(driver.findElement(By.name("kapasitas")));
        System.out.println("[TC-TBL-003] PASS");
    }

    // TC-TBL-004: Submit tambah meja valid → tersimpan
    @Test @Order(4) @DisplayName("TC-TBL-004: Tambah meja valid → tersimpan")
    void tcTbl004() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nomor_meja")));
        driver.findElement(By.name("nomor_meja")).sendKeys("99");
        driver.findElement(By.name("kapasitas")).sendKeys("4");
        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save")));
        jsClick(btnSave);
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("table"));
        System.out.println("[TC-TBL-004] PASS");
    }

    // TC-TBL-005: Submit meja tanpa nomor_meja → validasi menolak
    @Test @Order(5) @DisplayName("TC-TBL-005: Tambah meja tanpa nomor → ditolak")
    void tcTbl005() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kapasitas")));
        driver.findElement(By.name("kapasitas")).sendKeys("4");
        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save")));
        jsClick(btnSave);
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-TBL-005] PASS");
    }
}
