<?php
session_start(); // Mulai sesi untuk menyimpan login user

// Konfigurasi database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'todolist_db';

// Membuat koneksi ke database
$conn = new mysqli($host, $user, $password, $database);

// Periksa apakah koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>