<?php
// Form tambah barang (versi sederhana)

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$page_title = "Tambah Barang";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_barang = trim($_POST['kode_barang'] ?? '');
    $nama_barang = trim($_POST['nama_barang'] ?? '');
    $kategori   = trim($_POST['kategori'] ?? '');
    $stok       = (int) ($_POST['stok'] ?? 0);
    // Hapus titik dan koma dari harga, lalu konversi ke float
    $harga_raw = trim($_POST['harga'] ?? '0');
    $harga_clean = str_replace(['.', ','], '', $harga_raw);
    $harga = (float) $harga_clean;
    $deskripsi  = trim($_POST['deskripsi'] ?? '');

    // Validasi sederhana
    if ($nama_barang === '' || $kategori === '') {
        $_SESSION['pesan'] = "Nama barang dan kategori wajib diisi.";
        $_SESSION['tipe']  = "error";
    } elseif ($stok < 0 || $harga < 0) {
        $_SESSION['pesan'] = "Stok dan harga tidak boleh negatif.";
        $_SESSION['tipe']  = "error";
    } else {
        // Generate kode otomatis jika kosong
        if ($kode_barang === '') {
            $prefix = 'BRG';
            $sqlMax = "SELECT MAX(CAST(SUBSTRING(kode_barang, 4) AS UNSIGNED)) AS max_code 
                       FROM barang WHERE kode_barang LIKE '{$prefix}%'";
            $resMax = mysqli_query($koneksi, $sqlMax);
            $rowMax = $resMax ? mysqli_fetch_assoc($resMax) : null;
            $next   = ($rowMax['max_code'] ?? 0) + 1;
            $kode_barang = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
        }

        // Cek kode unik
        $kode_esc = mysqli_real_escape_string($koneksi, $kode_barang);
        $cek = mysqli_query($koneksi, "SELECT id FROM barang WHERE kode_barang = '$kode_esc'");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $_SESSION['pesan'] = "Kode barang '$kode_barang' sudah digunakan.";
            $_SESSION['tipe']  = "error";
        } else {
            // Insert sederhana
            $nama_esc = mysqli_real_escape_string($koneksi, $nama_barang);
            $kat_esc  = mysqli_real_escape_string($koneksi, $kategori);
            $desk_esc = mysqli_real_escape_string($koneksi, $deskripsi);

            $sql = "INSERT INTO barang (kode_barang, nama_barang, kategori, stok, harga, deskripsi, status)
                    VALUES ('$kode_esc', '$nama_esc', '$kat_esc', $stok, $harga, '$desk_esc', 'aktif')";

            if (mysqli_query($koneksi, $sql)) {
                $_SESSION['pesan'] = "Barang berhasil ditambahkan.";
                $_SESSION['tipe']  = "success";
                header("Location: tampilDataBarang.php");
                exit;
            } else {
                $_SESSION['pesan'] = "Gagal menambahkan barang.";
                $_SESSION['tipe']  = "error";
            }
        }
    }
}
?>

<?php include 'bingkai/header.php'; ?>

<div class="content-wrapper">
    <?php include 'bingkai/menu.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h2>Tambah Barang Baru</h2>
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <i class="fas fa-chevron-right"></i>
                <a href="tampilDataBarang.php">Data Barang</a>
                <i class="fas fa-chevron-right"></i>
                <span>Tambah Barang</span>
            </div>
        </div>
        
        <div class="content">
            <!-- Alert Messages -->
            <?php if (isset($_SESSION['pesan'])): ?>
                <div class="alert alert-<?php echo $_SESSION['tipe']; ?> alert-dismissible">
                    <i class="fas fa-<?php echo $_SESSION['tipe'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($_SESSION['pesan']); ?></span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php 
                unset($_SESSION['pesan']);
                unset($_SESSION['tipe']);
                ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-plus-circle"></i> Form Tambah Barang</h3>
                    <a href="tampilDataBarang.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                
                <div class="card-body">
                    <form method="POST" class="form-vertical" id="form-tambah-barang" onsubmit="return validateForm()">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kode_barang">
                                    <i class="fas fa-barcode"></i> Kode Barang
                                </label>
                                <input type="text" id="kode_barang" name="kode_barang" 
                                       placeholder="Kosongkan untuk generate otomatis" 
                                       pattern="[A-Z]{3}[0-9]{3}" 
                                       title="Format: 3 huruf besar diikuti 3 angka (contoh: BRG001)">
                                <small class="form-hint">
                                    <i class="fas fa-info-circle"></i> Kosongkan untuk generate otomatis atau isi dengan format BRG001
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="nama_barang">
                                    <i class="fas fa-box"></i> Nama Barang *
                                </label>
                                <input type="text" id="nama_barang" name="nama_barang" 
                                       placeholder="Masukkan nama barang" required 
                                       maxlength="100">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kategori">
                                    <i class="fas fa-tags"></i> Kategori *
                                </label>
                                <select id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Elektronik">Elektronik</option>
                                    <option value="Pakaian">Pakaian</option>
                                    <option value="Makanan">Makanan</option>
                                    <option value="Minuman">Minuman</option>
                                    <option value="Alat Tulis">Alat Tulis</option>
                                    <option value="Olahraga">Olahraga</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="stok">
                                    <i class="fas fa-cubes"></i> Stok *
                                </label>
                                <input type="number" id="stok" name="stok" min="0" 
                                       placeholder="0" required 
                                       oninput="this.value = Math.abs(this.value)">
                            </div>
                            
                            <div class="form-group">
                                <label for="harga">
                                    <i class="fas fa-money-bill-wave"></i> Harga (Rp) *
                                </label>
                                <input type="text" id="harga" name="harga" 
                                       placeholder="0" required 
                                       oninput="formatCurrencyInput(this)"
                                       onblur="validateHarga(this)">
                                <small class="form-hint">
                                    <i class="fas fa-info-circle"></i> Harga dalam Rupiah (Rp) - Contoh: 1.500.000
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">
                                <i class="fas fa-align-left"></i> Deskripsi
                            </label>
                            <textarea id="deskripsi" name="deskripsi" rows="4" 
                                      placeholder="Masukkan deskripsi barang (opsional)" 
                                      maxlength="500"></textarea>
                            <small class="form-hint">
                                <span id="char-count">0</span> / 500 karakter
                            </small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Barang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'bingkai/footer.php'; ?>

<style>
/* Content Wrapper & Main Content */
.content-wrapper {
    display: flex;
    min-height: 100vh;
    margin-left: 280px;
    width: calc(100% - 280px);
    transition: all 0.3s ease;
}

.main-content {
    flex: 1;
    padding: 30px;
    padding-bottom: 50px;
    background: #f5f7fa;
    width: 100%;
    overflow-x: hidden;
    min-height: calc(100vh - 0px);
}

/* Page Header */
.page-header {
    background: #ffffff;
    padding: 25px 30px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.page-header h2 {
    font-size: 1.8em;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9em;
    color: #666;
}

.breadcrumb a {
    color: #667eea;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #764ba2;
}

.breadcrumb i {
    font-size: 0.7em;
    color: #999;
}

.breadcrumb span {
    color: #333;
    font-weight: 500;
}

/* Content */
.content {
    max-width: 1200px;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert i {
    font-size: 1.2em;
}

.alert-close {
    position: absolute;
    right: 15px;
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 1.2em;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.alert-close:hover {
    opacity: 1;
}

/* Card */
.card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 25px;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1.3em;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body {
    padding: 30px;
}

/* Form Styles */
.form-vertical {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95em;
}

.form-group label i {
    color: #667eea;
    width: 18px;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.95em;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #333;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #999;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-hint {
    font-size: 0.85em;
    color: #666;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: -5px;
}

.form-hint i {
    color: #667eea;
}

/* Buttons */
.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.95em;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: #ffffff;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding-top: 10px;
    border-top: 1px solid #e0e0e0;
    margin-top: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        width: 100%;
    }
    
    .main-content {
        padding: 20px 15px;
    }
    
    .page-header {
        padding: 20px;
    }
    
    .page-header h2 {
        font-size: 1.5em;
    }
    
    .card-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Format currency input (mendukung nilai jutaan)
function formatCurrencyInput(input) {
    // Hapus semua karakter non-digit
    let value = input.value.replace(/[^\d]/g, '');
    
    if (value) {
        // Konversi ke number untuk menghindari overflow
        let numValue = parseInt(value);
        
        // Format dengan titik sebagai pemisah ribuan
        input.value = numValue.toLocaleString('id-ID');
        
        // Simpan nilai numerik di data attribute untuk form submission
        input.setAttribute('data-numeric-value', numValue);
    } else {
        input.value = '';
        input.removeAttribute('data-numeric-value');
    }
}

// Validasi harga sebelum submit
function validateHarga(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value && parseInt(value) < 0) {
        alert('Harga tidak boleh negatif!');
        input.value = '';
        input.focus();
    }
}

// Character counter for textarea
document.getElementById('deskripsi').addEventListener('input', function() {
    const charCount = this.value.length;
    document.getElementById('char-count').textContent = charCount;
    
    if (charCount > 450) {
        document.getElementById('char-count').style.color = '#e74c3c';
    } else {
        document.getElementById('char-count').style.color = '#666';
    }
});

// Form validation
function validateForm() {
    const namaBarang = document.getElementById('nama_barang').value.trim();
    const kategori = document.getElementById('kategori').value;
    const stok = parseInt(document.getElementById('stok').value);
    
    // Ambil harga dari input (hapus format titik)
    const hargaInput = document.getElementById('harga');
    const hargaRaw = hargaInput.value.replace(/[^\d]/g, '');
    const harga = parseFloat(hargaRaw) || 0;
    
    // Update hidden input atau langsung set value tanpa format
    hargaInput.value = harga;
    
    if (!namaBarang) {
        alert('Nama barang wajib diisi!');
        document.getElementById('nama_barang').focus();
        return false;
    }
    
    if (!kategori) {
        alert('Kategori wajib dipilih!');
        document.getElementById('kategori').focus();
        return false;
    }
    
    if (stok < 0) {
        alert('Stok tidak boleh negatif!');
        document.getElementById('stok').focus();
        return false;
    }
    
    if (harga <= 0) {
        alert('Harga harus lebih dari 0!');
        hargaInput.focus();
        return false;
    }
    
    // Set nilai tanpa format untuk dikirim ke server
    hargaInput.value = harga;
    
    return true;
}

// Auto-hide alert after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 300);
    });
}, 5000);
</script>
