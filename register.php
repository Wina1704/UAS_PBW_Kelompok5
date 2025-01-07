<?php
require 'db.php';
$stmt = $pdo->prepare("SELECT id_mk AS value, nama_mk AS label FROM mata_kuliah");
$stmt->execute();
$mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $mata_kuliah_id = isset($_POST['mata_kuliah']) ? $_POST['mata_kuliah'] : null;
    if ($role === 'dosen' && !$mata_kuliah_id) {
        $error = "Mata kuliah harus dipilih untuk role dosen.";
    } elseif ($role !== 'dosen' && $mata_kuliah_id) {
        $error = "Mata kuliah hanya boleh diisi untuk role dosen.";
    } else {
        try {
            $mata_kuliah_id = ($role === 'dosen') ? $mata_kuliah_id : null;
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, role, mata_kuliah, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $role, $mata_kuliah_id, $password]);
            header('Location: register.php');
            exit();
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style.css">
    <title>Register</title>
    <style>
        select {
            width: 200px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            font-size: 14px;
            color: #333;
            cursor: pointer;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        select:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }
        option {
            padding: 10px;
            font-size: 14px;
            color: #333;
            background-color: #fff;
        }
        #mataKuliahContainer {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="nama" placeholder="Nama" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="role" id="role" onchange="toggleMataKuliah()">
            <option value="mahasiswa">Mahasiswa</option>
            <option value="dosen">Dosen</option>
        </select>
        <div id="mataKuliahContainer">
            <select name="mata_kuliah" id="mata_kuliah">
                <option value="">Pilih Mata Kuliah</option>
                <?php foreach ($mata_kuliah as $mk): ?>
                    <option value="<?= htmlspecialchars($mk['value']) ?>"><?= htmlspecialchars($mk['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
    </form>
    <a href="penilaian.php">Kembali</a>
</div>

<script>
    function toggleMataKuliah() {
        const role = document.getElementById('role').value;
        const mataKuliahContainer = document.getElementById('mataKuliahContainer');
        if (role === 'dosen') {
            mataKuliahContainer.style.display = 'block';
        } else {
            mataKuliahContainer.style.display = 'none';
        }
    }
</script>
</body>
</html>
