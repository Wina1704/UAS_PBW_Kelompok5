<?php
require 'db.php';
require 'functions.php';
session_start();
redirect_if_not_logged_in();

// Tambah Mata Kuliah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mk'])) {
    $nama_mk = $_POST['nama_mk'];
    $dosen_pengampu = $_POST['dosen_pengampu'];
    $deskripsi = $_POST['deskripsi'];

    $stmt = $pdo->prepare("INSERT INTO mata_kuliah (nama_mk, dosen_pengampu, deskripsi) VALUES (?, ?, ?)");
    $stmt->execute([$nama_mk, $dosen_pengampu, $deskripsi]);

    header('Location: mata_kuliah.php');
    exit();
}

// Edit Mata Kuliah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_mk'])) {
    $id_mk = $_POST['id_mk'];
    $nama_mk = $_POST['nama_mk'];
    $dosen_pengampu = $_POST['dosen_pengampu'];
    $deskripsi = $_POST['deskripsi'];

    $stmt = $pdo->prepare("UPDATE mata_kuliah SET nama_mk = ?, dosen_pengampu = ?, deskripsi = ? WHERE id_mk = ?");
    $stmt->execute([$nama_mk, $dosen_pengampu, $deskripsi, $id_mk]);

    header('Location: mata_kuliah.php');
    exit();
}

// Hapus Mata Kuliah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_mk'])) {
    $id_mk = $_POST['id_mk'];

    $stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE id_mk = ?");
    $stmt->execute([$id_mk]);

    header('Location: mata_kuliah.php');
    exit();
}

// Ambil Semua Data Mata Kuliah
$stmt = $pdo->prepare("SELECT * FROM mata_kuliah");
$stmt->execute();
$mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        /* Reset dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4d79ff, #85e8ff);
            color: #333;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        /* Judul Halaman */
        .title {
            font-size: 2rem;
            text-align: center;
            color: #4d79ff;
        }

        /* Form */
        .form-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        input, textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        textarea {
            resize: none;
            height: 80px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF; /* Warna background tombol */
            color: #fff; /* Warna teks */
            text-align: center;
            text-decoration: none; /* Hilangkan garis bawah */
            border-radius: 5px; /* Agar sudut tombol membulat */
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .button:hover {
            background-color: #0056b3; /* Warna saat hover */
        }

        button {
            background: #4d79ff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }

        button:hover {
            background: #365ec9;
        }

        /* Tabel Mata Kuliah */
        .table-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #4d79ff;
            color: white;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn.danger {
            background: #ff6b6b;
        }

        .btn.secondary {
            background: #6c757d;
        }
    </style>
    <title>Kelola Mata Kuliah</title>
</head>
<body>
    <div class="container">
        <h1 class="title">Kelola Mata Kuliah</h1>

        <!-- Tambah Mata Kuliah -->
        <section class="form-section">
            <h2>Tambah Mata Kuliah Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="nama_mk">Nama Mata Kuliah:</label>
                    <input type="text" id="nama_mk" name="nama_mk" placeholder="Nama Mata Kuliah" required>
                </div>
                <div class="form-group">
                    <label for="dosen_pengampu">Dosen Pengampu:</label>
                    <input type="text" id="dosen_pengampu" name="dosen_pengampu" placeholder="Dosen Pengampu" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi"></textarea>
                </div>
                <button type="submit" name="add_mk" class="btn primary">Tambah</button>
            </form>
        </section>

        <!-- Daftar Mata Kuliah -->
        <section class="table-section">
            <h2>Daftar Mata Kuliah</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Dosen Pengampu</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mata_kuliah as $mk): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mk['id_mk']); ?></td>
                            <td><?php echo htmlspecialchars($mk['nama_mk']); ?></td>
                            <td><?php echo htmlspecialchars($mk['dosen_pengampu']); ?></td>
                            <td><?php echo htmlspecialchars($mk['deskripsi']); ?></td>
                            <td class="actions">
                                <!-- Form Edit -->
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="id_mk" value="<?php echo $mk['id_mk']; ?>">
                                    <input type="text" name="nama_mk" value="<?php echo htmlspecialchars($mk['nama_mk']); ?>" required>
                                    <input type="text" name="dosen_pengampu" value="<?php echo htmlspecialchars($mk['dosen_pengampu']); ?>" required>
                                    <textarea name="deskripsi" required><?php echo htmlspecialchars($mk['deskripsi']); ?></textarea>
                                    <button type="submit" name="edit_mk" class="btn primary">Simpan</button>
                                </form>
                                <!-- Form Hapus -->
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="id_mk" value="<?php echo $mk['id_mk']; ?>">
                                    <button type="submit" name="delete_mk" class="btn danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <a href="penilaian.php" class="btn secondary button">Kembali</a>
    </div>
</body>
</html>
