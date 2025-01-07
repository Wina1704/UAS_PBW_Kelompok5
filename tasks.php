<?php
require 'db.php';
require 'functions.php';
session_start();
redirect_if_not_logged_in();

$stmt = $pdo->prepare("SELECT id_mk, nama_mk FROM mata_kuliah");
$stmt->execute();
$mata_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $group_id = $_POST['group_id'];
    $mata_kuliah = $_POST['mata_kuliah'];
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];

    $stmt = $pdo->prepare("INSERT INTO Tasks (group_id, mata_kuliah, nama, deskripsi, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$group_id, $mata_kuliah, $nama, $deskripsi, $deadline]);

    header('Location: tasks.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'];
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE Tasks SET nama = ?, deskripsi = ?, tanggal_jatuh_tempo = ?, status = ? WHERE task_id = ?");
    $stmt->execute([$nama, $deskripsi, $deadline, $status, $task_id]);

    header('Location: tasks.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];

    $stmt = $pdo->prepare("DELETE FROM Tasks WHERE task_id = ?");
    $stmt->execute([$task_id]);

    header('Location: tasks.php');
    exit();
}

$stmt = $pdo->prepare("SELECT g.group_id, g.nama FROM Groups g 
                        INNER JOIN Group_Members gm ON g.group_id = gm.group_id 
                        WHERE gm.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tambahkan nilai pada SELECT statement saat mengambil data tugas
$stmt = $pdo->prepare("SELECT t.task_id, t.nama AS task_nama, t.deskripsi, t.tanggal_jatuh_tempo, t.status, t.nilai, g.nama AS group_nama, 
    mk.nama_mk AS mata_kuliah_nama,
    (SELECT COUNT(*) FROM Group_Members gm WHERE gm.group_id = t.group_id) AS total_anggota,
    (SELECT COUNT(*) FROM Group_Members gm WHERE gm.group_id = t.group_id AND gm.status = 'selesai') AS anggota_selesai
    FROM Tasks t
    INNER JOIN Groups g ON t.group_id = g.group_id
    LEFT JOIN mata_kuliah mk ON t.mata_kuliah = mk.id_mk
    WHERE g.group_id IN (SELECT group_id FROM Group_Members WHERE user_id = ?)");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
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

        .title {
            font-size: 2rem;
            text-align: center;
            color: #4d79ff;
        }

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

        input, textarea, select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        textarea {
            resize: none;
            height: 80px;
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

        .tasks-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .tasks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .task-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .task-card h3 {
            font-size: 1.2rem;
            color: #4d79ff;
        }

        .group-name {
            font-size: 0.9rem;
            color: #666;
        }

        .status {
            font-weight: bold;
            padding: 5px;
            border-radius: 5px;
            text-align: center;
        }

        .status.dimulai {
            background: #ff6b6b;
            color: white;
        }

        .status.sedang-proses {
            background: yellow;
            color: black;
        }

        .status.selesai {
            background: #28a745;
            color: white;
        }

        .deadline {
            font-size: 0.9rem;
            color: #555;
        }

        .btn.primary {
            background: #4d79ff;
        }

        .btn.secondary {
            background: #6c757d;
        }

        .btn.danger {
            background: #ff6b6b;
        }

        .inline-form input, 
        .inline-form textarea, 
        .inline-form select, 
        .inline-form button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }

        .inline-form textarea {
            height: 100px;
        }

        .inline-form button {
            width: auto;
            margin-top: 10px; 
        }

    </style>
    <title>Kelola Tugas</title>
</head>
<body>
    <div class="container">
        <h1 class="title">Kelola Tugas</h1>

        <section class="form-section">
            <h2>Tambah Tugas Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="group_id">Pilih Grup:</label>
                    <select name="group_id" id="group_id" required>
                        <option value="">Pilih Grup</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?php echo $group['group_id']; ?>">
                                <?php echo htmlspecialchars($group['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="mata_kuliah">Pilih Mata Kuliah:</label>
                    <select name="mata_kuliah" id="mata_kuliah" required>
                        <option value="">Pilih Mata Kuliah</option>
                        <?php foreach ($mata_kuliah as $mk): ?>
                            <option value="<?php echo $mk['id_mk']; ?>">
                                <?php echo htmlspecialchars($mk['nama_mk']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nama">Nama Tugas:</label>
                    <input type="text" id="nama" name="nama" placeholder="Nama Tugas" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi"></textarea>
                </div>
                <div class="form-group">
                    <label for="deadline">Deadline:</label>
                    <input type="date" id="deadline" name="deadline" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <button type="submit" name="add_task" class="btn primary">Tambah</button>
            </form>
        </section>

        <section class="tasks-section">
            <h2>Daftar Tugas</h2>
            <div class="tasks-grid">
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card">
                        <h3><?php echo htmlspecialchars($task['task_nama']); ?></h3>
                        <p class="group-name"><?php echo htmlspecialchars($task['group_nama']); ?></p>
                        <p class="mata-kuliah">Mata Kuliah: <?php echo htmlspecialchars($task['mata_kuliah_nama'] ?? 'Tidak ada'); ?></p>
                        <p class="status <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>">
                            Status: <?php echo htmlspecialchars($task['status']); ?>
                        </p>
                        <p class="deadline">Deadline: <?php echo htmlspecialchars($task['tanggal_jatuh_tempo']); ?></p>
                        <p><?php echo htmlspecialchars($task['deskripsi']); ?></p>
                        <div class="actions">
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                <?php
                                $currentDate = new DateTime();
                                $deadlineDate = new DateTime($task['tanggal_jatuh_tempo']);
                                $isDeadlinePassed = $currentDate > $deadlineDate;

                                if ($isDeadlinePassed): ?>
                                <?php else: ?>
                                    <button type="submit" name="delete_task" class="btn danger">Hapus</button>
                                <?php endif; ?>
                            </form>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                
                                <!-- Pastikan jika nilai sudah ada, form akan disabled -->
                                <input type="text" name="nama" value="<?php echo htmlspecialchars($task['task_nama']); ?>" required <?php echo $task['nilai'] !== null ? 'disabled' : ''; ?>>
                                <textarea name="deskripsi" <?php echo $task['nilai'] !== null ? 'disabled' : ''; ?>><?php echo htmlspecialchars($task['deskripsi']); ?></textarea>
                                <input type="date" name="deadline" value="<?php echo $task['tanggal_jatuh_tempo']; ?>" required <?php echo $task['nilai'] !== null ? 'disabled' : ''; ?>>
                                <select name="status" <?php echo $task['nilai'] !== null ? 'disabled' : ''; ?>>
                                    <option value="dimulai" <?php if ($task['status'] === 'dimulai') echo 'selected'; ?>>Dimulai</option>
                                    <option value="sedang proses" <?php if ($task['status'] === 'sedang proses') echo 'selected'; ?>>Sedang Proses</option>
                                    <option value="selesai" <?php if ($task['status'] === 'selesai') echo 'selected'; ?>>Selesai</option>
                                </select>

                                <?php if ($task['nilai'] !== null): ?>
                                    <p>Nilai: <?php echo htmlspecialchars($task['nilai']); ?></p>
                                <?php endif; ?>

                                <!-- Tampilkan tombol Simpan Perubahan hanya jika tidak ada nilai -->
                                <?php if ($task['nilai'] === null && !$isDeadlinePassed): ?>
                                    <button type="submit" name="edit_task" class="btn primary">Simpan Perubahan</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <a href="index.php" class="btn-secondary">Kembali</a>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deadlineInput = document.getElementById('deadline');

                const today = new Date().toISOString().split('T')[0];
                deadlineInput.setAttribute('min', today);

                const form = document.querySelector('form');
                form.addEventListener('submit', function(event) {
                    const selectedDate = deadlineInput.value;
                    if (selectedDate < today) {
                        alert('Tanggal deadline tidak boleh kurang dari hari ini.');
                        event.preventDefault();
                    }
                });
            });
        </script>
    </div>
</body>
</html>

