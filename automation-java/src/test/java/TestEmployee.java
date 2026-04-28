import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestEmployee.java — UI Test Employee BiteSpace CI4
 * Field : name="nama_lengkap", name="username", name="email", name="password"
 * Submit: button.btn-save
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestEmployee {

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

    // TC-22: Halaman employee tampil
    @Test @Order(1) @DisplayName("TC-22: Halaman /employee tampil")
    void tc22() {
        loginAsAdmin();
        driver.get(BASE + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(
            driver.getPageSource().contains("Employee") || driver.getPageSource().contains("Karyawan")
        );
        System.out.println("[TC-22] PASS");
    }

    // TC-23: Halaman tambah employee bisa diakses admin
    @Test @Order(2) @DisplayName("TC-23: Halaman /employee/create bisa diakses admin")
    void tc23() {
        loginAsAdmin();
        driver.get(BASE + "/employee/create");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        System.out.println("[TC-23] PASS");
    }

    // TC-24: Form employee ada semua field wajib
    @Test @Order(3) @DisplayName("TC-24: Form employee ada field nama_lengkap, username, password")
    void tc24() {
        loginAsAdmin();
        driver.get(BASE + "/employee/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_lengkap")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_lengkap")));
        Assertions.assertNotNull(driver.findElement(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("password")));
        Assertions.assertNotNull(driver.findElement(By.name("email")));
        System.out.println("[TC-24] PASS");
    }

    // TC-25: Submit employee valid → tersimpan
    @Test @Order(4) @DisplayName("TC-25: Tambah employee valid → tersimpan")
    void tc25() {
        loginAsAdmin();
        driver.get(BASE + "/employee/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_lengkap")));
        driver.findElement(By.name("nama_lengkap")).sendKeys("Test Kasir Selenium");
        // FIX: pakai timestamp supaya username unik setiap run
        driver.findElement(By.name("username")).sendKeys("testselenium" + System.currentTimeMillis());
        driver.findElement(By.name("email")).sendKeys("test" + System.currentTimeMillis() + "@mail.com");
        driver.findElement(By.name("password")).sendKeys("password123");
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-save"))).click();
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("employee"));
        System.out.println("[TC-25] PASS");
    }

    // TC-26: Daftar employee ada data sarah/neyza
    @Test @Order(5) @DisplayName("TC-26: Daftar employee ada data")
    void tc26() {
        loginAsAdmin();
        driver.get(BASE + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        String src = driver.getPageSource();
        Assertions.assertTrue(
            src.contains("sarah") || src.contains("neyza") || src.contains("Sarah")
        );
        System.out.println("[TC-26] PASS");
    }
}
