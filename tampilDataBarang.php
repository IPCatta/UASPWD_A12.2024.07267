<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$page_title = "Data Barang";

// Pagination
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$offset = ($page - 1) * $limit;

// Search dan Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';

// Build query
$where_conditions = ["status = 'aktif'"];

if (!empty($search)) {
    $where_conditions[] = "(nama_barang LIKE '%$search%' OR kode_barang LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}

if (!empty($filter_kategori)) {
    $where_conditions[] = "kategori = '$filter_kategori'";
}

$where_clause = implode(' AND ', $where_conditions);

// Query untuk total data
$count_query = "SELECT COUNT(*) as total FROM barang WHERE $where_clause";
$count_result = mysqli_query($koneksi, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_data = $count_row['total'];
$total_pages = ceil($total_data / $limit);

// Query untuk data barang
$query = "SELECT * FROM barang WHERE $where_clause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);

// Get unique categories for filter
$cat_query = "SELECT DISTINCT kategori FROM barang WHERE status = 'aktif' ORDER BY kategori";
$cat_result = mysqli_query($koneksi, $cat_query);
$categories = [];
while ($cat_row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $cat_row['kategori'];
}
?>

<?php include 'bingkai/header.php'; ?>

<div class="content-wrapper">
    <?php include 'bingkai/menu.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h2><i class="fas fa-boxes"></i> Data Barang</h2>
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>Data Barang</span>
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
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $total_data; ?></div>
                        <div class="stat-label">Total Barang</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="stat-content">
                        <?php
                        $stok_query = "SELECT SUM(stok) as total_stok FROM barang WHERE $where_clause";
                        $stok_result = mysqli_query($koneksi, $stok_query);
                        $stok_row = mysqli_fetch_assoc($stok_result);
                        $total_stok = $stok_row['total_stok'] ?? 0;
                        ?>
                        <div class="stat-value"><?php echo number_format($total_stok, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Stok</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo count($categories); ?></div>
                        <div class="stat-label">Kategori</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <?php
                        $harga_query = "SELECT SUM(harga * stok) as total_nilai FROM barang WHERE $where_clause";
                        $harga_result = mysqli_query($koneksi, $harga_query);
                        $harga_row = mysqli_fetch_assoc($harga_result);
                        $total_nilai = $harga_row['total_nilai'] ?? 0;
                        ?>
                        <div class="stat-value">Rp <?php echo number_format($total_nilai, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Nilai</div>
                    </div>
                </div>
            </div>
            
            <!-- Filter & Search Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-filter"></i> Filter & Pencarian</h3>
                    <a href="tambahDataBarang.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Barang
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label><i class="fas fa-search"></i> Pencarian</label>
                                <input type="text" name="search" placeholder="Cari kode, nama, atau deskripsi..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="filter-group">
                                <label><i class="fas fa-tags"></i> Kategori</label>
                                <select name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                                                <?php echo $filter_kategori == $cat ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Cari
                                </button>
                                <a href="tampilDataBarang.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Data Table Card -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-table"></i> Daftar Barang</h3>
                    <div class="card-actions" style="display: flex; align-items: center; gap: 15px;">
                        <a href="cetakDataBarang.php?<?php echo http_build_query(array_filter(['search' => $search, 'kategori' => $filter_kategori])); ?>" 
                           class="btn btn-success" target="_blank">
                            <i class="fas fa-file-pdf"></i> Cetak PDF
                        </a>
                        <span class="data-count">
                            Menampilkan <?php echo min($offset + 1, $total_data); ?> - <?php echo min($offset + $limit, $total_data); ?> dari <?php echo $total_data; ?> data
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Harga</th>
                                        <th>Total Nilai</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($result)): 
                                        $total_nilai_item = $row['harga'] * $row['stok'];
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-barcode"></i> <?php echo htmlspecialchars($row['kode_barang']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['nama_barang']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-category">
                                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['kategori']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $row['stok'] > 10 ? 'badge-success' : ($row['stok'] > 0 ? 'badge-warning' : 'badge-danger'); ?>">
                                                    <i class="fas fa-cubes"></i> <?php echo number_format($row['stok'], 0, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></strong>
                                            </td>
                                            <td>
                                                <strong class="text-primary">Rp <?php echo number_format($total_nilai_item, 0, ',', '.'); ?></strong>
                                            </td>
                                            <td>
                                                <span class="text-truncate" title="<?php echo htmlspecialchars($row['deskripsi']); ?>">
                                                    <?php 
                                                    $deskripsi = htmlspecialchars($row['deskripsi']);
                                                    echo strlen($deskripsi) > 50 ? substr($deskripsi, 0, 50) . '...' : $deskripsi;
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="koreksiDataBarang.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn-action btn-edit" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="hapusDataBarang.php?id=<?php echo $row['id']; ?>" 
                                                       class="btn-action btn-delete" 
                                                       onclick="return confirm('Apakah Anda yakin ingin menghapus barang <?php echo htmlspecialchars($row['nama_barang']); ?>?')" 
                                                       title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page_num=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo urlencode($filter_kategori); ?>" 
                                       class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i> Sebelumnya
                                    </a>
                                <?php endif; ?>
                                
                                <div class="pagination-info">
                                    Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                                </div>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page_num=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo urlencode($filter_kategori); ?>" 
                                       class="pagination-btn">
                                        Selanjutnya <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>Tidak Ada Data</h3>
                            <p>Belum ada data barang yang tersedia.</p>
                            <a href="tambahDataBarang.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Barang Pertama
                            </a>
                        </div>
                    <?php endif; ?>
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

@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
        width: 100%;
    }
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

/* Filter Form */
.filter-form {
    margin: 0;
}

.filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 500;
    color: #333;
    font-size: 0.9em;
    display: flex;
    align-items: center;
    gap: 5px;
}

.filter-group input,
.filter-group select {
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.95em;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-actions {
    display: flex;
    gap: 10px;
}

/* Table */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.data-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
}

.data-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table tbody tr {
    border-bottom: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
}

.data-table td {
    padding: 15px;
    font-size: 0.95em;
    color: #333;
}

.text-truncate {
    max-width: 200px;
    display: inline-block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Badges */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
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

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 10px 20px;
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

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: #ffffff;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-action {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-edit {
    background: #28a745;
    color: #ffffff;
}

.btn-edit:hover {
    background: #218838;
    transform: scale(1.1);
}

.btn-delete {
    background: #dc3545;
    color: #ffffff;
}

.btn-delete:hover {
    background: #c82333;
    transform: scale(1.1);
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.pagination-btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.pagination-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.pagination-info {
    color: #666;
    font-weight: 500;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 4em;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5em;
    margin-bottom: 10px;
    color: #666;
}

.empty-state p {
    margin-bottom: 25px;
    color: #999;
}

/* Card Actions */
.card-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.data-count {
    font-size: 0.9em;
    color: rgba(255, 255, 255, 0.9);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions .btn {
        width: 100%;
    }
    
    .data-table {
        font-size: 0.85em;
    }
    
    .data-table th,
    .data-table td {
        padding: 10px 8px;
    }
    
    .pagination {
        flex-direction: column;
        gap: 15px;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>

<script>
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

