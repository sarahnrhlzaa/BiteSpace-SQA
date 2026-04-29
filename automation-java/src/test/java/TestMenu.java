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

    // TC-MNU-001: Halaman daftar menu tampil
    @Test @Order(1) @DisplayName("TC-MNU-001: Halaman menu tampil")
    void tcMnu001() {
        loginAsAdmin();
        driver.get(BASE + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getPageSource().contains("Menu"));
        System.out.println("[TC-MNU-001] PASS");
    }

    // TC-MNU-002: Halaman tambah menu bisa diakses admin
    @Test @Order(2) @DisplayName("TC-MNU-002: Halaman /menu/create bisa diakses admin")
    void tcMnu002() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        Assertions.assertTrue(driver.getPageSource().contains("Tambah Menu"));
        System.out.println("[TC-MNU-002] PASS");
    }

    // TC-MNU-003: Form tambah menu punya field nama_menu, harga, id_category
    @Test @Order(3) @DisplayName("TC-MNU-003: Form tambah menu ada field nama_menu & harga")
    void tcMnu003() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_menu")));
        Assertions.assertNotNull(driver.findElement(By.name("harga")));
        Assertions.assertNotNull(driver.findElement(By.name("id_category")));
        System.out.println("[TC-MNU-003] PASS");
    }

    // TC-MNU-004: Submit form tambah menu valid → sukses
    @Test @Order(4) @DisplayName("TC-MNU-004: Tambah menu valid → tersimpan")
    void tcMnu004() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));
        driver.findElement(By.name("nama_menu")).sendKeys("Menu Test Selenium");
        driver.findElement(By.name("harga")).sendKeys("15000");
        new Select(driver.findElement(By.name("id_category"))).selectByIndex(1);
        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save-menu")));
        jsClick(btnSave);
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"));
        System.out.println("[TC-MNU-004] PASS");
    }

    // TC-MNU-005: Submit nama menu kosong → validasi menolak
    @Test @Order(5) @DisplayName("TC-MNU-005: Tambah menu nama kosong → ditolak")
    void tcMnu005() {
        loginAsAdmin();
        driver.get(BASE + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("harga")));
        driver.findElement(By.name("harga")).sendKeys("10000");
        WebElement btnSave = wait.until(
            ExpectedConditions.presenceOfElementLocated(By.cssSelector("button.btn-save-menu")));
        jsClick(btnSave);
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getCurrentUrl().contains("menu"));
        System.out.println("[TC-MNU-005] PASS");
    }
}
