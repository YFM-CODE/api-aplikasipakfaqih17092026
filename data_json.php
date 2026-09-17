<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ujian_asts";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["message" => "Koneksi gagal: " . $conn->connect_error]));
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['name'], $data['nisn'], $data['ttl'], $data['gender'], $data['email'], $data['address'])) {
            $stmt = $conn->prepare("INSERT INTO users (name, nisn, ttl, gender, email, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $data['name'], $data['nisn'], $data['ttl'], $data['gender'], $data['email'], $data['address']);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Data berhasil ditambahkan"]);
            } else {
                echo json_encode(["message" => "Gagal menambah data"]);
            }
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'], $data['name'], $data['nisn'], $data['ttl'], $data['gender'], $data['email'], $data['address'])) {
            $stmt = $conn->prepare("UPDATE users SET name=?, nisn=?, ttl=?, gender=?, email=?, address=? WHERE id=?");
            $stmt->bind_param("ssssssi", $data['name'], $data['nisn'], $data['ttl'], $data['gender'], $data['email'], $data['address'], $data['id']);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Data berhasil diperbarui"]);
            } else {
                echo json_encode(["message" => "Gagal memperbarui data"]);
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $data['id']);
            if ($stmt->execute()) {
                echo json_encode(["message" => "Data berhasil dihapus"]);
            } else {
                echo json_encode(["message" => "Gagal menghapus data"]);
            }
        }
        break;
}

$conn->close();
?>