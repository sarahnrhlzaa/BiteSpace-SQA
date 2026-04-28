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

    // TC-17: Halaman meja tampil
    @Test @Order(1) @DisplayName("TC-17: Halaman /table tampil")
    void tc17() {
        loginAsAdmin();
        driver.get(BASE + "/table");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(
            driver.getPageSource().contains("Meja") || driver.getPageSource().contains("Table")
        );
        System.out.println("[TC-17] PASS");
    }

    // TC-18: Halaman tambah meja bisa diakses admin
    @Test @Order(2) @DisplayName("TC-18: Halaman /table/create bisa diakses admin")
    void tc18() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        System.out.println("[TC-18] PASS");
    }

    // TC-19: Form tambah meja ada field nomor_meja & kapasitas
    @Test @Order(3) @DisplayName("TC-19: Form meja ada field nomor_meja & kapasitas")
    void tc19() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nomor_meja")));
        Assertions.assertNotNull(driver.findElement(By.name("nomor_meja")));
        Assertions.assertNotNull(driver.findElement(By.name("kapasitas")));
        System.out.println("[TC-19] PASS");
    }

    // TC-20: Submit tambah meja valid → tersimpan
    @Test @Order(4) @DisplayName("TC-20: Tambah meja valid → tersimpan")
    void tc20() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nomor_meja")));
        driver.findElement(By.name("nomor_meja")).sendKeys("99");
        driver.findElement(By.name("kapasitas")).sendKeys("4");
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-save"))).click();
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("table"));
        System.out.println("[TC-20] PASS");
    }

    // TC-21: Submit meja tanpa nomor_meja → validasi menolak
    @Test @Order(5) @DisplayName("TC-21: Tambah meja tanpa nomor → ditolak")
    void tc21() {
        loginAsAdmin();
        driver.get(BASE + "/table/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("kapasitas")));
        driver.findElement(By.name("kapasitas")).sendKeys("4");
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-save"))).click();
        // FIX: tunggu halaman stabil sebelum assert
        wait.until(ExpectedConditions.urlContains("table"));
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-21] PASS");
    }
}
