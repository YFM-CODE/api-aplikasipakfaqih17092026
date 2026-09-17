<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ujian_asts";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Koneksi database gagal."]));
}

$data = json_decode(file_get_contents("php://input"), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Username dan Password tidak boleh kosong!"]);
            exit;
        }

        // Cek apakah username sudah terdaftar
        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Username sudah digunakan!"]);
            exit;
        }

        // Hash password demi keamanan
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Registrasi Admin berhasil! Silakan login."]);
        } else {
            echo json_encode(["success" => false, "message" => "Gagal meregistrasi Admin."]);
        }
    } 
    elseif ($action === 'login') {
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Username dan Password harus diisi!"]);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                // Buat token sesi sederhana
                $token = bin2hex(random_bytes(16));
                echo json_encode([
                    "success" => true,
                    "message" => "Login berhasil!",
                    "token" => $token,
                    "username" => $admin['username']
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Password salah!"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Username tidak ditemukan!"]);
        }
    }
}

$conn->close();
?>