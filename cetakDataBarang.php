<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Koneksi database
require_once __DIR__ . '/koneksi.php';

// Cek FPDF
$fpdfFile = null;
if (file_exists(__DIR__ . '/fpdf186/fpdf.php')) {
    $fpdfFile = __DIR__ . '/fpdf186/fpdf.php';
} elseif (file_exists(__DIR__ . '/fpdf.php')) {
    $fpdfFile = __DIR__ . '/fpdf.php';
}

if (!$fpdfFile) {
    die("FPDF library tidak ditemukan.");
}

require_once $fpdfFile;

// Ambil filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$filter_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($koneksi, $_GET['status']) : 'aktif';

// Build query
$where = [];
if ($status_filter !== 'all') {
    $where[] = "status='$status_filter'";
}
if ($search) {
    $where[] = "(nama_barang LIKE '%$search%' OR kode_barang LIKE '%$search%')";
}
if ($filter_kategori) {
    $where[] = "kategori='$filter_kategori'";
}
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "SELECT * FROM barang $where_clause ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);
if (!$result) {
    die(mysqli_error($koneksi));
}

// ================= PDF CLASS =================
class PDF extends FPDF {

    function Header() {
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(102, 126, 234);
        $this->Cell(0, 10, 'LAPORAN DATA BARANG', 0, 1, 'C');

        $this->SetDrawColor(102, 126, 234);
        $this->SetLineWidth(0.5);
        $this->Line(10, 22, 200, 22);

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, 'Dicetak: ' . date('d/m/Y H:i:s'), 0, 1, 'R');

        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(130, 130, 130);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function HeaderTable() {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(102, 126, 234);
        $this->SetTextColor(255, 255, 255);

        $this->Cell(12, 9, 'No', 1, 0, 'C', true);
        $this->Cell(28, 9, 'Kode', 1, 0, 'C', true);
        $this->Cell(50, 9, 'Nama Barang', 1, 0, 'C', true);
        $this->Cell(28, 9, 'Kategori', 1, 0, 'C', true);
        $this->Cell(18, 9, 'Stok', 1, 0, 'C', true);
        $this->Cell(38, 9, 'Harga', 1, 0, 'C', true);
        $this->Cell(38, 9, 'Total', 1, 1, 'C', true);
    }
}

// ================= GENERATE PDF =================
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// Judul box
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 8, 'DAFTAR DATA BARANG', 0, 1);

$pdf->Ln(3);
$pdf->HeaderTable();

// Data
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(50, 50, 50);

$no = 1;
$total_stok = 0;
$total_nilai = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $stok = (int)$row['stok'];
    $harga = (float)$row['harga'];
    $subtotal = $stok * $harga;

    $total_stok += $stok;
    $total_nilai += $subtotal;

    if ($pdf->GetY() > 175) {
        $pdf->AddPage();
        $pdf->HeaderTable();
    }

    $pdf->Cell(12, 7, $no++, 1, 0, 'C');
    $pdf->Cell(28, 7, $row['kode_barang'], 1);
    $pdf->Cell(50, 7, substr($row['nama_barang'], 0, 30), 1);
    $pdf->Cell(28, 7, substr($row['kategori'], 0, 18), 1);
    $pdf->Cell(18, 7, number_format($stok), 1, 0, 'R');
    $pdf->Cell(38, 7, number_format($harga, 0, ',', '.'), 1, 0, 'R');
    $pdf->Cell(38, 7, number_format($subtotal, 0, ',', '.'), 1, 1, 'R');
}

// TOTAL
$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(102, 126, 234);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(118, 10, 'TOTAL', 1, 0, 'R', true);
$pdf->Cell(18, 10, number_format($total_stok), 1, 0, 'R', true);
$pdf->Cell(38, 10, '-', 1, 0, 'C', true);
$pdf->Cell(38, 10, number_format($total_nilai, 0, ',', '.'), 1, 1, 'R', true);

// OUTPUT
$filename = 'Laporan_Data_Barang_' . date('Ymd_His') . '.pdf';
$pdf->Output('I', $filename);
exit();
?>
