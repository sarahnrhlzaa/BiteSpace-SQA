import org.junit.jupiter.api.*;
import org.openqa.selenium.*;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;
import io.github.bonigarcia.wdm.WebDriverManager;
import java.time.Duration;

/**
 * TestMenu.java
 * Selenium UI Test – Fitur Menu BiteSpace CI4
 * Hanya admin (sarah) yang bisa tambah/edit/hapus menu
 */
public class TestMenu {

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

    // TC-14: Halaman daftar menu tampil
    @Test @DisplayName("TC-14: Halaman daftar menu berhasil dimuat")
    void tc14_halamanMenuTampil() {
        loginAsAdmin();
        driver.get(BASE_URL + "/menu");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertTrue(driver.getPageSource().contains("Menu") || driver.getPageSource().contains("menu"));
        System.out.println("[TC-14] PASS: Halaman menu tampil.");
    }

    // TC-15: Halaman tambah menu bisa diakses admin
    @Test @DisplayName("TC-15: Admin bisa akses halaman tambah menu")
    void tc15_halamanTambahMenu() {
        loginAsAdmin();
        driver.get(BASE_URL + "/menu/create");
        wait.until(ExpectedConditions.urlContains("menu"));
        Assertions.assertFalse(driver.getPageSource().contains("Akses ditolak"));
        Assertions.assertTrue(driver.getPageSource().contains("Tambah") || driver.getPageSource().contains("Menu"));
        System.out.println("[TC-15] PASS: Halaman tambah menu bisa diakses admin.");
    }

    // TC-16: Form tambah menu memiliki field yang diperlukan
    @Test @DisplayName("TC-16: Form tambah menu punya field nama & harga")
    void tc16_formTambahMenuAdaField() {
        loginAsAdmin();
        driver.get(BASE_URL + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));
        Assertions.assertNotNull(driver.findElement(By.name("nama_menu")));
        Assertions.assertNotNull(driver.findElement(By.name("harga")));
        System.out.println("[TC-16] PASS: Field form tambah menu ada.");
    }

    // TC-17: Submit tambah menu tanpa field wajib → validasi menolak
    @Test @DisplayName("TC-17: Tambah menu gagal jika field nama kosong")
    void tc17_tambahMenuFieldKosong() {
        loginAsAdmin();
        driver.get(BASE_URL + "/menu/create");
        wait.until(ExpectedConditions.visibilityOfElementLocated(By.name("nama_menu")));

        // Isi harga tapi kosongi nama_menu
        driver.findElement(By.name("harga")).sendKeys("10000");
        driver.findElement(By.cssSelector("button[type='submit']")).click();

        // Harus tetap di halaman create / ada pesan error
        Assertions.assertFalse(driver.getCurrentUrl().contains("menu") && driver.getPageSource().contains("berhasil ditambahkan"));
        System.out.println("[TC-17] PASS: Validasi field kosong berhasil.");
    }
}
