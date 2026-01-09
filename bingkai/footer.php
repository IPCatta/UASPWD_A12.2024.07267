<footer class="app-footer">
    <div class="footer-content">
        <div class="footer-section">
            <div class="footer-brand">
                <i class="fas fa-warehouse"></i>
                <h4>Sistem Manajemen Gudang</h4>
            </div>
            <p class="footer-description">
                Sistem manajemen gudang yang efisien untuk mengelola stok dan inventori barang Anda.
            </p>
        </div>
        
        <div class="footer-section">
            <h5>Menu Utama</h5>
            <ul class="footer-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Beranda</a></li>
                <li><a href="tampilDataBarang.php"><i class="fas fa-boxes"></i> Data Barang</a></li>
                <li><a href="tambahDataBarang.php"><i class="fas fa-plus-circle"></i> Tambah Barang</a></li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="footer-copyright">
            <p>
                <i class="fas fa-copyright"></i> 
                <?php echo date('Y'); ?> Sistem Manajemen Gudang. 
                <span>Hak Cipta Dilindungi.</span>
            </p>
        </div>
        <div class="footer-info">
            <span>
                <i class="fas fa-code"></i> Dibuat dengan 
                <i class="fas fa-heart" style="color: #e74c3c;"></i> 
                untuk efisiensi gudang Anda
            </span>
        </div>
    </div>
</footer>

<style>
.app-footer {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
    padding: 40px 20px 20px;
    margin-top: 40px;
    margin-left: 280px;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
    width: calc(100% - 280px);
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.footer-section h4,
.footer-section h5 {
    margin: 0 0 15px 0;
    font-weight: 600;
    color: #ffffff;
}

.footer-section h4 {
    font-size: 1.3em;
}

.footer-section h5 {
    font-size: 1.1em;
    border-bottom: 2px solid rgba(255, 255, 255, 0.3);
    padding-bottom: 10px;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.footer-brand i {
    font-size: 2em;
    color: #ffd700;
}

.footer-description {
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: #ffd700;
    transform: translateX(5px);
}

.footer-links a i {
    width: 20px;
    text-align: center;
}

.footer-bottom {
    max-width: 1200px;
    margin: 0 auto;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.footer-copyright p {
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    gap: 5px;
}

.footer-info {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9em;
}

.footer-info i {
    margin: 0 3px;
}

@media (max-width: 768px) {
    .footer-content {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }
    
    .app-footer {
        padding: 30px 15px 15px;
        margin-left: 0;
        width: 100%;
    }
}
</style>

