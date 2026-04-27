import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestEmployee.java
 * Selenium UI Test – Fitur Manajemen Employee BiteSpace CI4
 */
public class TestEmployee {

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

    // TC-26: Halaman daftar employee tampil
    @Test @DisplayName("TC-26: Halaman daftar employee berhasil dimuat")
    void tc26_halamanEmployeeTampil() {
        loginAsAdmin();
        driver.get(BASE_URL + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getPageSource().contains("Employee") || driver.getPageSource().contains("Karyawan"));
        System.out.println("[TC-26] PASS: Halaman employee tampil.");
    }

    // TC-27: Halaman tambah employee bisa diakses admin
    @Test @DisplayName("TC-27: Halaman tambah employee bisa diakses")
    void tc27_halamanTambahEmployee() {
        loginAsAdmin();
        driver.get(BASE_URL + "/employee/create");
        wait.until(ExpectedConditions.urlContains("employee"));
        Assertions.assertTrue(driver.getPageSource().contains("Tambah") || driver.getPageSource().contains("Employee"));
        System.out.println("[TC-27] PASS: Halaman tambah employee terbuka.");
    }

    // TC-28: Form tambah employee punya field username & password
    @Test @DisplayName("TC-28: Form tambah employee punya field username & password")
    void tc28_formTambahEmployeeAdaField() {
        loginAsAdmin();
        driver.get(BASE_URL + "/employee/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("username")));
        Assertions.assertNotNull(driver.findElement(By.name("password")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_lengkap")));
        System.out.println("[TC-28] PASS: Field form employee ada.");
    }

    // TC-29: Submit employee tanpa username → validasi menolak
    @Test @DisplayName("TC-29: Tambah employee gagal jika username kosong")
    void tc29_tambahEmployeeKosong() {
        loginAsAdmin();
        driver.get(BASE_URL + "/employee/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_lengkap")));
        driver.findElement(By.name("nama_lengkap")).sendKeys("Test User");
        driver.findElement(By.cssSelector("button[type='submit']")).click();
        Assertions.assertFalse(driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-29] PASS: Validasi username kosong berhasil.");
    }

    // TC-30: Daftar employee menampilkan data (tidak kosong)
    @Test @DisplayName("TC-30: Daftar employee tidak kosong (ada data sarah & neyza)")
    void tc30_daftarEmployeeTidakKosong() {
        loginAsAdmin();
        driver.get(BASE_URL + "/employee");
        wait.until(ExpectedConditions.urlContains("employee"));
        String src = driver.getPageSource();
        Assertions.assertTrue(src.contains("sarah") || src.contains("neyza") || src.contains("Sarah"),
            "Harus ada minimal 1 data employee.");
        System.out.println("[TC-30] PASS: Data employee ada.");
    }
}
