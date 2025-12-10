<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu!']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

$user_id = $_SESSION['user_id'];
$items = $data['items'];

// 1. TANGKAP NAMA CUSTOMER DARI CHECKOUT
$customer_name = isset($data['customer_name']) ? mysqli_real_escape_string($conn, $data['customer_name']) : 'Guest';

$waktu_transaksi = date("Y-m-d H:i:s"); 
$success_count = 0;

foreach ($items as $item) {
    $item_name = mysqli_real_escape_string($conn, $item['name']);
    $price = (int) $item['price'];
    $qty = (int) $item['qty'];
    
    // Update Stok
    $update_stok = mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE name = '$item_name' AND stock >= $qty");

    if (mysqli_affected_rows($conn) > 0) {
        // 2. MASUKKAN customer_name KE DATABASE
        $sql = "INSERT INTO orders (user_id, customer_name, item_name, price, quantity, order_date) 
                VALUES ('$user_id', '$customer_name', '$item_name', '$price', '$qty', '$waktu_transaksi')";
        
        if (mysqli_query($conn, $sql)) {
            $success_count++;
        }
    }
}

if ($success_count > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal atau Stok Habis']);
}
?>