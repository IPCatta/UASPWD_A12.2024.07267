<?php
// Script untuk mengecek struktur tabel users
require_once 'koneksi.php';

echo "<h2>Struktur Tabel Users</h2>";
echo "<pre>";

// Cek apakah tabel users ada
$check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'users'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    echo "✓ Tabel 'users' ditemukan!\n\n";
    
    // Tampilkan struktur tabel
    echo "=== Struktur Tabel ===\n";
    $structure = mysqli_query($koneksi, "DESCRIBE users");
    if ($structure) {
        echo "Field\t\tType\t\tNull\tKey\tDefault\tExtra\n";
        echo str_repeat("-", 80) . "\n";
        while ($row = mysqli_fetch_assoc($structure)) {
            printf("%-15s %-20s %-5s %-5s %-10s %s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key'], 
                $row['Default'] ?? 'NULL', 
                $row['Extra']
            );
        }
    }
    
    echo "\n\n=== Data Users ===\n";
    $data = mysqli_query($koneksi, "SELECT * FROM users LIMIT 5");
    if ($data && mysqli_num_rows($data) > 0) {
        $first_row = mysqli_fetch_assoc($data);
        echo "Kolom yang ada: " . implode(", ", array_keys($first_row)) . "\n\n";
        
        mysqli_data_seek($data, 0);
        echo "Sample data:\n";
        while ($row = mysqli_fetch_assoc($data)) {
            foreach ($row as $key => $value) {
                if ($key === 'password') {
                    echo "  $key: " . (strlen($value) > 20 ? '[HASHED]' : $value) . "\n";
                } else {
                    echo "  $key: $value\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "Tabel kosong (belum ada data)\n";
    }
} else {
    echo "✗ Tabel 'users' tidak ditemukan!\n";
    echo "Silakan buat tabel terlebih dahulu menggunakan file create_users_table.sql\n";
}

echo "</pre>";
?>

