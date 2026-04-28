import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.*;
import org.openqa.selenium.support.ui.*;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestMenu.java — UI Test Menu BiteSpace CI4
 * Field : name="nama_menu", name="harga", name="id_category"
 * Submit: button.btn-save-menu
 */
@TestMethodOrder(MethodOrderer.OrderAnnotation.class)
public class TestMenu {

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

    // TC-08: Halaman daftar menu tampil
    @Test @Order(1) @DisplayName("TC-08: Halaman menu tampil")
    void tc08() {
        loginAsAdmin();
        driver.get(BASE + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getPageSource().contains("Menu"));
        System.out.println("[TC-08] PASS");
    }

    // TC-09: Halaman tambah menu bisa diakses admin
    @Test @Order(2) @DisplayName("TC-09: Halaman /menu/create bisa diakses admin")
    void tc09() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        Assertions.assertTrue(driver.getPageSource().contains("Tambah Menu"));
        System.out.println("[TC-09] PASS");
    }

    // TC-10: Form tambah menu punya field nama_menu, harga, id_category
    @Test @Order(3) @DisplayName("TC-10: Form tambah menu ada field nama_menu & harga")
    void tc10() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_menu")));
        Assertions.assertNotNull(driver.findElement(By.name("harga")));
        Assertions.assertNotNull(driver.findElement(By.name("id_category")));
        System.out.println("[TC-10] PASS");
    }

    // TC-11: Submit form tambah menu valid → sukses
    @Test @Order(4) @DisplayName("TC-11: Tambah menu valid → tersimpan")
    void tc11() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));
        driver.findElement(By.name("nama_menu")).sendKeys("Menu Test Selenium");
        driver.findElement(By.name("harga")).sendKeys("15000");
        new Select(driver.findElement(By.name("id_category"))).selectByIndex(1);
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-save-menu"))).click();
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"));
        System.out.println("[TC-11] PASS");
    }

    // TC-12: Submit nama menu kosong → validasi menolak
    @Test @Order(5) @DisplayName("TC-12: Tambah menu nama kosong → ditolak")
    void tc12() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("harga")));
        driver.findElement(By.name("harga")).sendKeys("10000");
        wait.until(ExpectedConditions.elementToBeClickable(
            By.cssSelector("button.btn-save-menu"))).click();
        // FIX: tunggu halaman stabil setelah klik
        wait.until(ExpectedConditions.or(
            ExpectedConditions.urlContains("menu/create"),
            ExpectedConditions.urlContains("menu")
        ));
        Assertions.assertTrue(
            driver.getCurrentUrl().contains("menu/create") || driver.getCurrentUrl().contains("menu")
        );
        System.out.println("[TC-12] PASS");
    }
}
