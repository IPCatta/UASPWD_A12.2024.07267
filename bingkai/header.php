<?php
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil page title jika sudah diset
$page_title = isset($page_title) ? $page_title : "Sistem Manajemen Gudang";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo htmlspecialchars($page_title); ?> - Sistem Manajemen Gudang</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .app-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-top {
            background: rgba(0, 0, 0, 0.1);
            padding: 8px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .header-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.9em;
        }
        
        .header-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .header-info-item i {
            color: #ffd700;
        }
        
        .header-main {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: #ffffff;
        }
        
        .header-logo {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8em;
            color: #ffd700;
            transition: all 0.3s ease;
        }
        
        .header-brand:hover .header-logo {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(5deg);
        }
        
        .header-title {
            display: flex;
            flex-direction: column;
        }
        
        .header-title h1 {
            font-size: 1.5em;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }
        
        .header-title span {
            font-size: 0.75em;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-search {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .header-search input {
            padding: 10px 15px 10px 40px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 0.9em;
            width: 250px;
            transition: all 0.3s ease;
        }
        
        .header-search input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .header-search input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.3);
            width: 300px;
        }
        
        .header-search i {
            position: absolute;
            left: 15px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .header-notification {
            position: relative;
        }
        
        .notification-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #ffffff;
            font-size: 1.2em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .notification-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: #ffffff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            font-weight: 600;
        }
        
        .header-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .header-user:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ffd700;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-weight: 600;
            font-size: 0.9em;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-size: 0.9em;
            font-weight: 500;
        }
        
        .user-role {
            font-size: 0.75em;
            opacity: 0.8;
        }
        
        .header-nav {
            background: rgba(0, 0, 0, 0.1);
            padding: 0 20px;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 5px;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95em;
            border-bottom: 3px solid transparent;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-bottom-color: #ffd700;
        }
        
        .nav-link i {
            font-size: 1em;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #ffffff;
            font-size: 1.5em;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        @media (max-width: 768px) {
            .header-top {
                display: none;
            }
            
            .header-main {
                padding: 12px 15px;
            }
            
            .header-title h1 {
                font-size: 1.2em;
            }
            
            .header-search {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .header-nav {
                display: none;
            }
            
            .header-nav.active {
                display: block;
            }
            
            .nav-menu {
                flex-direction: column;
                gap: 0;
            }
            
            .nav-link {
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .user-info {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .header-title span {
                display: none;
            }
            
            .notification-btn {
                width: 35px;
                height: 35px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="header-top">
            <div class="header-info">
                <div class="header-info-item">
                    <i class="fas fa-clock"></i>
                    <span id="current-time"></span>
                </div>
                <div class="header-info-item">
                    <i class="fas fa-calendar"></i>
                    <span id="current-date"></span>
                </div>
            </div>
            <div class="header-info">
                <div class="header-info-item">
                    <i class="fas fa-database"></i>
                    <span>Database: Terhubung</span>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <a href="index.php" class="header-brand">
                <div class="header-logo">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="header-title">
                    <h1>Sistem Manajemen Gudang</h1>
                    <span>Kelola Inventori dengan Mudah</span>
                </div>
            </a>
            
            <div class="header-actions">
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari barang, kategori..." id="header-search">
                </div>
                
                <div class="header-notification">
                    <button class="notification-btn" title="Notifikasi">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                </div>
                
                <div class="header-user" title="Menu Pengguna">
                    <div class="user-avatar">
                        <?php 
                        $user_initials = isset($_SESSION['nama']) ? strtoupper(substr($_SESSION['nama'], 0, 2)) : 'AD';
                        echo $user_initials;
                        ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?>
                        </div>
                        <div class="user-role">Admin</div>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 0.8em;"></i>
                </div>
                
                <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <nav class="header-nav" id="header-nav">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tampilDataBarang.php" class="nav-link">
                        <i class="fas fa-boxes"></i>
                        <span>Data Barang</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="tambahDataBarang.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Tambah Barang</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <script>
        // Update waktu dan tanggal
        function updateDateTime() {
            const now = new Date();
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            
            document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID', timeOptions);
            document.getElementById('current-date').textContent = now.toLocaleDateString('id-ID', dateOptions);
        }
        
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Mobile menu toggle
        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            const nav = document.getElementById('header-nav');
            nav.classList.toggle('active');
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
        
        // Search functionality (bisa dikembangkan lebih lanjut)
        document.getElementById('header-search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    // Redirect ke halaman pencarian atau filter
                    window.location.href = 'tampilDataBarang.php?search=' + encodeURIComponent(query);
                }
            }
        });
    </script>

