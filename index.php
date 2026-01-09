<?php
// Dashboard sederhana

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Koneksi database
require_once 'koneksi.php';

// Judul halaman
$page_title = "Beranda";
?>

<?php include 'bingkai/header.php'; ?>

<div class="content-wrapper">
    <?php include 'bingkai/menu.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-home"></i> Selamat Datang</h2>
            <div class="breadcrumb">
                <span>Beranda</span>
            </div>
        </div>
        
        <div class="content">
            <!-- Welcome Card -->
            <div class="card welcome-card">
                <div class="card-header">
                    <h3><i class="fas fa-warehouse"></i> Sistem Manajemen Gudang</h3>
                </div>
                <div class="card-body">
                    <div class="welcome-content">
                        <div class="welcome-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <h2>Selamat Datang di Sistem Manajemen Gudang</h2>
                        <p>Kelola inventori dan stok barang Anda dengan mudah dan efisien</p>
                        <div class="welcome-features">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Manajemen Stok Real-time</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Pencarian & Filter Cepat</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Laporan Lengkap</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <?php
            $total_barang = 0;
            $total_stok = 0;
            $total_kategori = 0;
            $total_nilai = 0;
            
            $stats_query = "SELECT 
                COUNT(*) as total_barang,
                SUM(stok) as total_stok,
                COUNT(DISTINCT kategori) as total_kategori,
                SUM(harga * stok) as total_nilai
                FROM barang WHERE status = 'aktif'";
            $stats_result = mysqli_query($koneksi, $stats_query);
            if ($stats_result) {
                $stats_row = mysqli_fetch_assoc($stats_result);
                $total_barang = $stats_row['total_barang'] ?? 0;
                $total_stok = $stats_row['total_stok'] ?? 0;
                $total_kategori = $stats_row['total_kategori'] ?? 0;
                $total_nilai = $stats_row['total_nilai'] ?? 0;
            }
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_barang, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Barang</div>
                    </div>
                    <a href="tampilDataBarang.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_stok, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Stok</div>
                    </div>
                    <a href="tampilDataBarang.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_kategori, 0, ',', '.'); ?></div>
                        <div class="stat-label">Kategori</div>
                    </div>
                    <a href="tampilDataBarang.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Nilai</div>
                    </div>
                    <a href="tampilDataBarang.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="tambahDataBarang.php" class="action-card">
                            <div class="action-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="action-content">
                                <h4>Tambah Barang</h4>
                                <p>Tambahkan barang baru ke dalam sistem</p>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
                        </a>
                        
                        <a href="tampilDataBarang.php" class="action-card">
                            <div class="action-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div class="action-content">
                                <h4>Data Barang</h4>
                                <p>Lihat dan kelola semua data barang</p>
                            </div>
                            <i class="fas fa-chevron-right action-arrow"></i>
                        </a>
                        
                    </div>
                </div>
            </div>
            
            <!-- Recent Items & Low Stock -->
            <div class="cards-row">
                <?php
                $recent_query = "SELECT * FROM barang WHERE status = 'aktif' ORDER BY id DESC LIMIT 5";
                $recent_result = mysqli_query($koneksi, $recent_query);
                if (mysqli_num_rows($recent_result) > 0):
                ?>
                <div class="card card-half">
                    <div class="card-header">
                        <h3><i class="fas fa-clock"></i> Barang Terbaru</h3>
                        <a href="tampilDataBarang.php" class="btn btn-secondary">
                            Lihat Semua <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="recent-items">
                            <?php 
                            mysqli_data_seek($recent_result, 0);
                            while ($item = mysqli_fetch_assoc($recent_result)): 
                            ?>
                                <div class="recent-item">
                                    <div class="recent-item-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="recent-item-content">
                                        <h4><?php echo htmlspecialchars($item['nama_barang']); ?></h4>
                                        <p>
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($item['kode_barang']); ?></span>
                                            <span class="badge badge-category"><?php echo htmlspecialchars($item['kategori']); ?></span>
                                        </p>
                                    </div>
                                    <div class="recent-item-stock">
                                        <span class="stock-value"><?php echo number_format($item['stok'], 0, ',', '.'); ?></span>
                                        <span class="stock-label">Stok</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php
                $low_stock_query = "SELECT * FROM barang WHERE status = 'aktif' AND stok <= 10 ORDER BY stok ASC LIMIT 5";
                $low_stock_result = mysqli_query($koneksi, $low_stock_query);
                if (mysqli_num_rows($low_stock_result) > 0):
                ?>
                <div class="card card-half">
                    <div class="card-header">
                        <h3><i class="fas fa-exclamation-triangle"></i> Stok Menipis</h3>
                        <a href="tampilDataBarang.php" class="btn btn-warning">
                            Periksa <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="recent-items">
                            <?php while ($item = mysqli_fetch_assoc($low_stock_result)): ?>
                                <div class="recent-item low-stock">
                                    <div class="recent-item-icon" style="background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);">
                                        <i class="fas fa-exclamation"></i>
                                    </div>
                                    <div class="recent-item-content">
                                        <h4><?php echo htmlspecialchars($item['nama_barang']); ?></h4>
                                        <p>
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($item['kode_barang']); ?></span>
                                            <span class="badge badge-category"><?php echo htmlspecialchars($item['kategori']); ?></span>
                                        </p>
                                    </div>
                                    <div class="recent-item-stock">
                                        <span class="stock-value" style="color: #e74c3c;"><?php echo number_format($item['stok'], 0, ',', '.'); ?></span>
                                        <span class="stock-label">Stok</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        width: 100%;
    }
}

/* Welcome Card */
.welcome-card {
    margin-bottom: 25px;
}

.welcome-content {
    text-align: center;
    padding: 20px;
}

.welcome-icon {
    font-size: 5em;
    color: #667eea;
    margin-bottom: 20px;
}

.welcome-content h2 {
    font-size: 2em;
    color: #333;
    margin-bottom: 10px;
}

.welcome-content p {
    font-size: 1.1em;
    color: #666;
    margin-bottom: 25px;
}

.welcome-features {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
    margin-top: 30px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    border-radius: 25px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.feature-item i {
    font-size: 1.1em;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.stat-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.5em;
    flex-shrink: 0;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.8em;
    font-weight: 700;
    color: #333;
    line-height: 1.2;
}

.stat-label {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}

.stat-link {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 50%;
    color: #667eea;
    text-decoration: none;
    transition: all 0.3s ease;
}

.stat-link:hover {
    background: #667eea;
    color: #ffffff;
    transform: scale(1.1);
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.action-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.action-card:hover {
    background: #ffffff;
    border-color: #667eea;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.3em;
    flex-shrink: 0;
}

.action-content {
    flex: 1;
}

.action-content h4 {
    font-size: 1.1em;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.action-content p {
    font-size: 0.9em;
    color: #666;
    margin: 0;
}

.action-arrow {
    color: #999;
    transition: all 0.3s ease;
}

.action-card:hover .action-arrow {
    color: #667eea;
    transform: translateX(5px);
}

/* Recent Items */
.recent-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.recent-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.recent-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.recent-item-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 1.2em;
}

.recent-item-content {
    flex: 1;
}

.recent-item-content h4 {
    font-size: 1em;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.recent-item-content p {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin: 0;
}

.recent-item-stock {
    text-align: center;
    padding: 10px 15px;
    background: #ffffff;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
}

.stock-value {
    display: block;
    font-size: 1.3em;
    font-weight: 700;
    color: #667eea;
}

.stock-label {
    display: block;
    font-size: 0.8em;
    color: #666;
    margin-top: 3px;
}

/* Badges */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    font-weight: 500;
}

.badge-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
}

.badge-category {
    background: #e3f2fd;
    color: #1976d2;
}

/* Cards Row */
.cards-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

.card-half {
    flex: 1;
}

.low-stock {
    border-left: 4px solid #e74c3c;
}

.btn-warning {
    background: #ffc107;
    color: #333;
}

.btn-warning:hover {
    background: #e0a800;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .cards-row {
        grid-template-columns: 1fr;
    }
    
    .welcome-icon {
        font-size: 3em;
    }
    
    .welcome-content h2 {
        font-size: 1.5em;
    }
    
    .welcome-features {
        flex-direction: column;
        gap: 15px;
    }
    
    .feature-item {
        width: 100%;
        justify-content: center;
    }
}
</style>

