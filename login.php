<?php
// Enable error reporting untuk debugging (hapus di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi database dengan error handling yang lebih baik
$koneksi = null;
$db_error = '';
$table_exists = false;

// Coba koneksi database tanpa die() yang menghentikan eksekusi
try {
    // Baca config dari koneksi.php jika ada, atau gunakan default
    if (file_exists('koneksi.php')) {
        // Include koneksi.php, tapi kita akan override die() behavior
        // dengan menggunakan output buffering
        ob_start();
        
        // Baca isi file dan ekstrak konfigurasi
        $koneksi_content = file_get_contents('koneksi.php');
        
        // Ekstrak konfigurasi dari file
        preg_match('/\$host\s*=\s*["\']([^"\']+)["\']/', $koneksi_content, $host_match);
        preg_match('/\$user\s*=\s*["\']([^"\']+)["\']/', $koneksi_content, $user_match);
        preg_match('/\$pass\s*=\s*["\']([^"\']*)["\']/', $koneksi_content, $pass_match);
        preg_match('/\$db\s*=\s*["\']([^"\']+)["\']/', $koneksi_content, $db_match);
        
        $host = isset($host_match[1]) ? $host_match[1] : "localhost";
        $user = isset($user_match[1]) ? $user_match[1] : "root";
        $pass = isset($pass_match[1]) ? $pass_match[1] : "";
        $db = isset($db_match[1]) ? $db_match[1] : "gudang_db";
        
        ob_end_clean();
    } else {
        // Gunakan default jika file tidak ada
        $host = "localhost";
        $user = "root";
        $pass = "";
        $db = "gudang_db";
        $db_error = 'File koneksi.php tidak ditemukan. Menggunakan konfigurasi default.';
    }
    
    // Coba koneksi dengan error suppression
    $koneksi = @mysqli_connect($host, $user, $pass, $db);
    
    if (!$koneksi) {
        $db_error = 'Koneksi database gagal: ' . mysqli_connect_error() . 
                   '<br><strong>Periksa:</strong><br>' .
                   '- Host: ' . htmlspecialchars($host) . '<br>' .
                   '- Database: ' . htmlspecialchars($db) . '<br>' .
                   '- Pastikan MySQL/XAMPP sedang berjalan<br>' .
                   '- Pastikan database "' . htmlspecialchars($db) . '" sudah dibuat';
    } else {
        // Cek apakah tabel users ada
        $check_table = @mysqli_query($koneksi, "SHOW TABLES LIKE 'users'");
        if ($check_table) {
            $table_exists = mysqli_num_rows($check_table) > 0;
        } else {
            $db_error = 'Error checking table: ' . mysqli_error($koneksi);
        }
    }
} catch (Exception $e) {
    $db_error = 'Exception: ' . $e->getMessage();
} catch (Error $e) {
    $db_error = 'Fatal Error: ' . $e->getMessage();
}

// Jika sudah login, redirect ke index
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: index.php");
    exit();
}

// Proses login dari database
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bypass login untuk setup (jika tabel belum ada)
    if (isset($_POST['bypass_login']) && !$table_exists) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = 'setup';
        $_SESSION['nama'] = 'Setup Mode';
        header("Location: index.php");
        exit();
    }
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } elseif (!$koneksi) {
        $error = 'Tidak dapat terhubung ke database. ' . ($db_error ?: 'Periksa koneksi database.');
    } elseif (!$table_exists) {
        $error = 'Tabel users belum dibuat. Silakan buat tabel users di database terlebih dahulu.';
    } else {
        // Cek struktur tabel users untuk menyesuaikan query
        $columns_query = mysqli_query($koneksi, "SHOW COLUMNS FROM users");
        $columns = array();
        if ($columns_query) {
            while ($col = mysqli_fetch_assoc($columns_query)) {
                $columns[] = $col['Field'];
            }
        }
        
        // Buat query SELECT berdasarkan kolom yang ada
        $select_fields = array();
        $required_fields = array('username', 'password');
        
        // Pastikan username dan password ada
        if (!in_array('username', $columns) || !in_array('password', $columns)) {
            $error = 'Tabel users harus memiliki kolom username dan password!';
        } else {
            // Tambahkan kolom yang ada ke SELECT
            if (in_array('id', $columns)) $select_fields[] = 'id';
            $select_fields[] = 'username';
            $select_fields[] = 'password';
            if (in_array('nama', $columns)) $select_fields[] = 'nama';
            if (in_array('name', $columns)) $select_fields[] = 'name';
            if (in_array('email', $columns)) $select_fields[] = 'email';
            if (in_array('role', $columns)) $select_fields[] = 'role';
            
            $select_sql = "SELECT " . implode(", ", $select_fields) . " FROM users WHERE username = ?";
            
            // Tambahkan kondisi status jika kolom ada
            if (in_array('status', $columns)) {
                $select_sql .= " AND status = 'aktif'";
            } elseif (in_array('is_active', $columns)) {
                $select_sql .= " AND is_active = 1";
            } elseif (in_array('active', $columns)) {
                $select_sql .= " AND active = 1";
            }
            
            $select_sql .= " LIMIT 1";
            
            // Query ke database menggunakan prepared statement
            if (!$koneksi) {
                $error = 'Koneksi database tidak tersedia.';
            } else {
                $stmt = mysqli_prepare($koneksi, $select_sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    // Cek password (jika di-hash, gunakan password_verify)
                    // Jika password plain text di database, bandingkan langsung
                    $db_password = $row['password'];
                    $password_valid = false;
                    
                    // Cek apakah password di-hash (panjang > 20 atau dimulai dengan $2y$)
                    if (strlen($db_password) > 20 || substr($db_password, 0, 4) === '$2y$' || substr($db_password, 0, 4) === '$2a$') {
                        // Password di-hash, gunakan password_verify
                        $password_valid = password_verify($password, $db_password);
                    } else {
                        // Password plain text, bandingkan langsung
                        $password_valid = ($password === $db_password);
                    }
                    
                    if ($password_valid) {
                        // Login berhasil
                        $_SESSION['user_logged_in'] = true;
                        
                        // Simpan data user ke session
                        if (isset($row['id'])) $_SESSION['user_id'] = $row['id'];
                        $_SESSION['username'] = $row['username'];
                        
                        // Simpan nama (cek beberapa kemungkinan nama kolom)
                        if (isset($row['nama'])) {
                            $_SESSION['nama'] = $row['nama'];
                        } elseif (isset($row['name'])) {
                            $_SESSION['nama'] = $row['name'];
                        } else {
                            $_SESSION['nama'] = $row['username']; // Fallback ke username
                        }
                        
                        if (isset($row['email'])) $_SESSION['email'] = $row['email'];
                        if (isset($row['role'])) $_SESSION['role'] = $row['role'];
                        
                        // Update last login (opsional, jika kolom ada)
                        if (isset($row['id'])) {
                            $id = $row['id'];
                            if (in_array('last_login', $columns)) {
                                $update_stmt = mysqli_prepare($koneksi, "UPDATE users SET last_login = NOW() WHERE id = ?");
                                if ($update_stmt) {
                                    mysqli_stmt_bind_param($update_stmt, "i", $id);
                                    mysqli_stmt_execute($update_stmt);
                                    mysqli_stmt_close($update_stmt);
                                }
                            } elseif (in_array('last_login_at', $columns)) {
                                $update_stmt = mysqli_prepare($koneksi, "UPDATE users SET last_login_at = NOW() WHERE id = ?");
                                if ($update_stmt) {
                                    mysqli_stmt_bind_param($update_stmt, "i", $id);
                                    mysqli_stmt_execute($update_stmt);
                                    mysqli_stmt_close($update_stmt);
                                }
                            }
                        }
                        
                        mysqli_stmt_close($stmt);
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = 'Username atau password salah!';
                    }
                } else {
                    $error = 'Username atau password salah!';
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = 'Terjadi kesalahan sistem: ' . ($koneksi ? mysqli_error($koneksi) : 'Database tidak terhubung');
            }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login - Sistem Manajemen Gudang</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* Background Animation */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
            top: -50%;
            left: -50%;
        }
        
        @keyframes moveBackground {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }
        
        .login-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .login-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5em;
            color: #ffffff;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .login-header h1 {
            font-size: 2em;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .login-header p {
            color: #666;
            font-size: 0.95em;
        }
        
        .login-form {
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 0.9em;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.1em;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input::placeholder {
            color: #999;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1.1em;
            transition: color 0.3s ease;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.9em;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .remember-me label {
            color: #666;
            cursor: pointer;
            margin: 0;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #c33;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .error-message i {
            font-size: 1.2em;
        }
        
        .login-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .login-button i {
            font-size: 1.2em;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }
        
        .login-footer p {
            color: #666;
            font-size: 0.9em;
        }
        
        .demo-info {
            background: #f0f7ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.85em;
        }
        
        .demo-info h4 {
            color: #0066cc;
            margin-bottom: 8px;
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .demo-info p {
            color: #0066cc;
            margin: 5px 0;
        }
        
        .demo-info strong {
            color: #004499;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 1.6em;
            }
            
            .login-icon {
                width: 70px;
                height: 70px;
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Selamat Datang</h1>
            <p>Sistem Manajemen Gudang</p>
        </div>
        
        <?php if (!empty($db_error)): ?>
            <div class="error-message" style="background: #fee; color: #c33; border-left-color: #c33;">
                <i class="fas fa-database"></i>
                <span><strong>Database Error:</strong> <?php echo htmlspecialchars($db_error); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!$table_exists): ?>
            <div class="error-message" style="background: #fff3cd; color: #856404; border-left-color: #ffc107;">
                <i class="fas fa-exclamation-triangle"></i>
                <span>
                    <strong>Peringatan:</strong> Tabel users belum dibuat. 
                    <br>Silakan buka phpMyAdmin dan jalankan query dari file <strong>create_users_table.sql</strong>
                    <br>Atau gunakan tombol "Bypass Login" di bawah untuk masuk tanpa database (hanya untuk setup).
                </span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="login-form" id="loginForm">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Masukkan username Anda"
                        required
                        autocomplete="username"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan password Anda"
                        required
                        autocomplete="current-password"
                    >
                    <span class="password-toggle" id="passwordToggle">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>
                <a href="#" class="forgot-password">Lupa password?</a>
            </div>
            
            <button type="submit" class="login-button">
                <i class="fas fa-sign-in-alt"></i>
                <span>Masuk</span>
            </button>
        </form>
        
        <?php if (!$table_exists): ?>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <form method="POST" action="" style="margin: 0;">
                    <input type="hidden" name="bypass_login" value="1">
                    <button type="submit" class="login-button" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-key"></i>
                        <span>Bypass Login (Setup Mode)</span>
                    </button>
                </form>
                <p style="text-align: center; margin-top: 10px; font-size: 0.85em; color: #666;">
                    <i class="fas fa-info-circle"></i> Hanya untuk setup awal. Setelah tabel users dibuat, gunakan login normal.
                </p>
            </div>
        <?php endif; ?>
        
        <div class="login-footer">
            <p>
                <i class="fas fa-copyright"></i> 
                <?php echo date('Y'); ?> Sistem Manajemen Gudang
            </p>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        // Form validation
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Username dan password harus diisi!');
                return false;
            }
        });
        
        // Auto-hide error message after 5 seconds
        const errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            setTimeout(function() {
                errorMessage.style.opacity = '0';
                errorMessage.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    errorMessage.style.display = 'none';
                }, 500);
            }, 5000);
        }
    </script>
</body>
</html>

