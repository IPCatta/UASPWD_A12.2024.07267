<?php
// Include koneksi database jika belum ada
if (!isset($koneksi)) {
    $koneksi_path = dirname(__DIR__) . '/koneksi.php';
    if (file_exists($koneksi_path)) {
        include_once $koneksi_path;
    }
}

// Deteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
$current_query = isset($_GET['page']) ? $_GET['page'] : '';

// Fungsi untuk mengecek apakah menu aktif
function isActive($page, $query = '') {
    global $current_page, $current_query;
    if ($query) {
        return ($current_query == $query) ? 'active' : '';
    }
    return ($current_page == $page) ? 'active' : '';
}
?>

<aside class="sidebar-menu" id="sidebar-menu">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="sidebar-logo">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="sidebar-title">
                <h3>Menu Utama</h3>
                <span>Navigasi</span>
            </div>
        </div>
        <button class="sidebar-toggle" id="sidebar-toggle" title="Sembunyikan Menu">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    
    <div class="sidebar-content">
        <!-- Quick Stats -->
        <div class="sidebar-stats">
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-total-barang">-</div>
                    <div class="stat-label">Total Barang</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" id="stat-total-stok">-</div>
                    <div class="stat-label">Total Stok</div>
                </div>
            </div>
        </div>
        
        <!-- Main Menu -->
        <nav class="sidebar-nav">
            <ul class="menu-list">
                <li class="menu-item">
                    <a href="index.php" class="menu-link <?php echo ($current_page == 'index.php' && !$current_query) ? 'active' : ''; ?>">
                        <div class="menu-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <span class="menu-text">Beranda</span>
                        <span class="menu-badge"></span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="tampilDataBarang.php" class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'tampilDataBarang.php') ? 'active' : ''; ?>">
                        <div class="menu-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <span class="menu-text">Data Barang</span>
                        <span class="menu-badge"></span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="tambahDataBarang.php" class="menu-link <?php echo isActive('tambahDataBarang.php'); ?>">
                        <div class="menu-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <span class="menu-text">Tambah Barang</span>
                        <span class="menu-badge new">New</span>
                    </a>
                </li>
                
            </ul>
        </nav>
    </div>
    
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar-small">
                <?php 
                $user_initials = isset($_SESSION['nama']) ? strtoupper(substr($_SESSION['nama'], 0, 2)) : 'AD';
                echo $user_initials;
                ?>
            </div>
            <div class="user-details">
                <div class="user-name-small">
                    <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?>
                </div>
                <div class="user-role-small">Admin</div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn" title="Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?');">
            <i class="fas fa-sign-out-alt"></i>
            <span class="menu-text">Keluar</span>
        </a>
    </div>
</aside>

<style>
.sidebar-menu {
    width: 280px;
    background: #ffffff;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    height: 100vh;
    max-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 999;
    transition: all 0.3s ease;
    overflow: hidden;
}

.sidebar-menu.collapsed {
    width: 70px;
}

.sidebar-menu.collapsed .menu-text,
.sidebar-menu.collapsed .sidebar-title,
.sidebar-menu.collapsed .stat-info,
.sidebar-menu.collapsed .user-details,
.sidebar-menu.collapsed .menu-divider span,
.sidebar-menu.collapsed .logout-btn .menu-text {
    display: none;
}

.sidebar-menu.collapsed .sidebar-toggle i {
    transform: rotate(180deg);
}

.sidebar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #ffffff;
    flex-shrink: 0;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sidebar-logo {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    color: #ffd700;
}

.sidebar-title h3 {
    font-size: 1.1em;
    font-weight: 600;
    margin: 0;
    line-height: 1.2;
}

.sidebar-title span {
    font-size: 0.75em;
    opacity: 0.9;
    font-weight: 300;
}

.sidebar-toggle {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #ffffff;
    width: 35px;
    height: 35px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.3);
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 20px 0;
    min-height: 0;
    -webkit-overflow-scrolling: touch;
}

.sidebar-stats {
    padding: 0 15px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.2em;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 1.3em;
    font-weight: 600;
    color: #333;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.85em;
    color: #666;
    margin-top: 2px;
}

.sidebar-nav {
    padding: 0 10px;
}

.menu-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.menu-item {
    margin-bottom: 5px;
}

.menu-link {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 12px 15px;
    color: #555;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    position: relative;
}

.menu-link:hover {
    background: #f0f0f0;
    color: #667eea;
    transform: translateX(5px);
}

.menu-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.menu-link.active .menu-icon {
    background: rgba(255, 255, 255, 0.2);
    color: #ffd700;
}

.menu-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 8px;
    color: #667eea;
    font-size: 1.1em;
    transition: all 0.3s ease;
}

.menu-link:hover .menu-icon {
    background: #e0e0e0;
}

.menu-text {
    flex: 1;
    font-weight: 500;
    font-size: 0.95em;
}

.menu-badge {
    background: #e74c3c;
    color: #ffffff;
    font-size: 0.7em;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 600;
    display: none;
}

.menu-badge.new {
    display: inline-block;
    background: #27ae60;
}

.menu-divider {
    padding: 15px 15px 8px;
    margin-top: 10px;
}

.menu-divider span {
    font-size: 0.75em;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.sidebar-footer {
    padding: 15px;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-shrink: 0;
    margin-top: auto;
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.user-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 600;
    font-size: 0.9em;
}

.user-details {
    flex: 1;
}

.user-name-small {
    font-size: 0.9em;
    font-weight: 600;
    color: #333;
    line-height: 1.2;
}

.user-role-small {
    font-size: 0.75em;
    color: #666;
}

.logout-btn {
    width: 100%;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: #e74c3c;
    color: #ffffff;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    padding: 0 15px;
    font-size: 0.9em;
    font-weight: 500;
}

.logout-btn:hover {
    background: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
}

.logout-btn .menu-text {
    display: inline;
}

/* Scrollbar Styling */
.sidebar-content::-webkit-scrollbar {
    width: 6px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Firefox scrollbar */
.sidebar-content {
    scrollbar-width: thin;
    scrollbar-color: #888 #f1f1f1;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar-menu {
        transform: translateX(-100%);
        width: 280px;
    }
    
    .sidebar-menu.active {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: none;
    }
}

/* Content Wrapper Adjustment */
.content-wrapper {
    display: flex;
    margin-left: 280px;
    transition: all 0.3s ease;
    min-height: 100vh;
    width: calc(100% - 280px);
}

.sidebar-menu.collapsed ~ .content-wrapper,
.content-wrapper.no-sidebar {
    margin-left: 70px;
    width: calc(100% - 70px);
}

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        width: 100%;
    }
}
</style>

<script>
// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar-menu');
    const toggle = document.getElementById('sidebar-toggle');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    if (toggle) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            if (contentWrapper) {
                contentWrapper.classList.toggle('no-sidebar');
            }
            
            // Simpan state ke localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    
    // Load saved state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        sidebar.classList.add('collapsed');
        if (contentWrapper) {
            contentWrapper.classList.add('no-sidebar');
        }
    }
    
    // Load statistics (jika ada koneksi database)
    <?php if (isset($koneksi)): ?>
    loadStatistics();
    <?php endif; ?>
});

// Load Statistics
function loadStatistics() {
    // Contoh: Load statistik dari database
    // Anda bisa menyesuaikan dengan query database yang sesuai
    fetch('get_statistics.php')
        .then(response => response.json())
        .then(data => {
            if (data.total_barang !== undefined) {
                document.getElementById('stat-total-barang').textContent = data.total_barang;
            }
            if (data.total_stok !== undefined) {
                document.getElementById('stat-total-stok').textContent = data.total_stok;
            }
        })
        .catch(error => {
            console.log('Statistik tidak dapat dimuat');
            // Set default values
            document.getElementById('stat-total-barang').textContent = '0';
            document.getElementById('stat-total-stok').textContent = '0';
        });
}

// Mobile menu toggle (jika diperlukan)
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar-menu');
    sidebar.classList.toggle('active');
}
</script>

<?php
// Query untuk statistik
$total_barang = 0;
$total_stok = 0;

if (isset($koneksi) && $koneksi) {
    $query_total = "SELECT COUNT(*) as total FROM barang WHERE status = 'aktif'";
    $query_stok = "SELECT COALESCE(SUM(stok), 0) as total_stok FROM barang WHERE status = 'aktif'";
    
    $result_total = mysqli_query($koneksi, $query_total);
    $result_stok = mysqli_query($koneksi, $query_stok);
    
    if ($result_total) {
        $row_total = mysqli_fetch_assoc($result_total);
        $total_barang = $row_total['total'] ?? 0;
    }
    
    if ($result_stok) {
        $row_stok = mysqli_fetch_assoc($result_stok);
        $total_stok = $row_stok['total_stok'] ?? 0;
        // Pastikan total_stok tidak null
        if ($total_stok === null) {
            $total_stok = 0;
        }
    }
}

// Update statistik langsung dengan PHP (lebih reliable)
if (isset($koneksi) && $koneksi) {
    echo "<script>";
    echo "(function() {";
    echo "    const totalBarangEl = document.getElementById('stat-total-barang');";
    echo "    const totalStokEl = document.getElementById('stat-total-stok');";
    echo "    if (totalBarangEl) {";
    echo "        totalBarangEl.textContent = '" . intval($total_barang) . "';";
    echo "    }";
    echo "    if (totalStokEl) {";
    echo "        totalStokEl.textContent = '" . number_format(intval($total_stok), 0, ',', '.') . "';";
    echo "    }";
    echo "})();";
    echo "</script>";
}
?>

