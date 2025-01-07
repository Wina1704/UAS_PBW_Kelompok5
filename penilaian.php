<?php
require 'db.php';
require 'functions.php';
session_start();
redirect_if_not_logged_in();

if ($_SESSION['role'] !== 'dosen') {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT t.task_id, t.nama AS task_nama, t.deskripsi, t.tanggal_jatuh_tempo, g.nama AS group_nama, g.group_id, 
           p.nilai, p.komentar, gm.description, gm.image_path,
           GROUP_CONCAT(u.nama SEPARATOR ', ') AS anggota_grup,
           GROUP_CONCAT(gm.image_path SEPARATOR ', ') AS group_images
    FROM Tasks t
    INNER JOIN Groups g ON t.group_id = g.group_id
    LEFT JOIN Penilaian p ON t.task_id = p.task_id
    INNER JOIN group_members gm ON gm.group_id = g.group_id
    INNER JOIN users u ON u.user_id = gm.user_id
    WHERE t.status = 'selesai'
    GROUP BY t.task_id, g.group_id
");


$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_task'])) {
    $task_id = $_POST['task_id'];
    $group_id = $_POST['group_id'];
    $nilai = $_POST['nilai'];
    $komentar = $_POST['komentar'];

    // Check if there's an existing rating for this task and group
    $stmt = $pdo->prepare("SELECT * FROM penilaian WHERE task_id = ? AND group_id = ?");
    $stmt->execute([$task_id, $group_id]);
    $existing_penilaian = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_penilaian) {
        // Update the existing rating
        $stmt = $pdo->prepare("UPDATE penilaian SET nilai = ?, komentar = ? WHERE task_id = ? AND group_id = ?");
        $stmt->execute([$nilai, $komentar, $task_id, $group_id]);
    } else {
        // Insert a new rating
        $stmt = $pdo->prepare("INSERT INTO penilaian (group_id, task_id, nilai, komentar) VALUES (?, ?, ?, ?)");
        $stmt->execute([$group_id, $task_id, $nilai, $komentar]);
    }
    
    // Update the Tasks table with the new nilai and komentar
    $stmt = $pdo->prepare("UPDATE tasks SET nilai = ?, komentar = ? WHERE task_id = ?");
    $stmt->execute([$nilai, $komentar, $task_id]);

    header("Location: penilaian.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Penilaian Tugas</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-detail {
            background: none;
            border: none;
            color: #007BFF; /* Warna biru */
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
        }

        .btn-detail:hover {
            color: #0056b3; /* Biru lebih gelap saat hover */
        }

        .btn-detail i {
            margin-right: 5px; /* Jarak antara ikon dan teks */
        }

        .swal2-html-container {
            text-align: left; /* Rata kiri isi detail */
            font-size: 14px; /* Ukuran font lebih kecil */
        }

        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input[type="number"]:focus,
        textarea:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Menu</h2>
        <ul>
            <li><a href="penilaian.php"><i class="fas fa-star"></i><span>Penilaian</span></a></li>
            <li><a href="mata_kuliah.php"><i class="fas fa-tasks"></i><span>Mata Kuliah</span></a></li>
            <li><a href="register.php"><i class="fas fa-user-plus"></i><span>Buat Akun</span></a></li>
            <li><a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <h1>Penilaian Tugas</h1>

        <?php if (count($tasks) > 0): ?>
            <table class="projects-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Grup</th>
                        <th>Nama Tugas</th>
                        <th>Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $index => $task): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($task['group_nama']); ?></td>
                            <td><?php echo htmlspecialchars($task['task_nama']); ?></td>
                            <td>
                                <?php echo $task['nilai'] !== null ? htmlspecialchars($task['nilai']) : 'Belum dinilai'; ?>
                            </td>
                            <td>
                                <button class="btn-detail" onclick="showDetail(<?php echo htmlspecialchars(json_encode($task)); ?>)">
                                    <i class="fas fa-info-circle"></i>Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada tugas yang bisa dinilai.</p>
        <?php endif; ?>
    </div>

    <!-- JavaScript untuk SweetAlert Logout -->
    <script>
        document.getElementById('logoutBtn').addEventListener('click', function(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan keluar dari sesi ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        });

        function showDetail(task) {
    const imageSection = task.group_images ? task.group_images.split(',').map(imagePath => {
        return `<img src="${imagePath}" alt="Gambar Tugas" style="max-width:100%; margin-top:10px; margin-bottom:10px;">`;
    }).join('') : '';  // Menampilkan semua gambar anggota grup

    const groupMemberDescription = task.description ? `<p><b>Deskripsi Progress Grup:</b> ${task.description}</p>` : '';
    const groupMembers = task.anggota_grup ? `<p><b>Anggota Grup:</b> ${task.anggota_grup}</p>` : '';
    const isRated = task.nilai !== null;

    Swal.fire({
        title: `Detail Tugas: ${task.task_nama}`,
        html: `
            <p><b>Nama Grup:</b> ${task.group_nama}</p>
            <p><b>Deskripsi:</b> ${task.deskripsi}</p>
            <p><b>Deadline:</b> ${task.tanggal_jatuh_tempo}</p>
            ${groupMemberDescription}
            ${groupMembers}
            ${imageSection}  <!-- Menampilkan gambar -->
            <form id="penilaianForm" method="POST" action="penilaian.php">
                <input type="hidden" name="task_id" value="${task.task_id}">
                <input type="hidden" name="group_id" value="${task.group_id}">
                <div style="margin-top: 10px;">
                    <label for="nilai"><b>Nilai (0-100):</b></label>
                    <input type="number" id="nilai" name="nilai" min="0" max="100" required value="${isRated ? task.nilai : ''}" ${isRated ? 'disabled' : ''}>
                </div>
                <div style="margin-top: 10px;">
                    <label for="komentar"><b>Komentar:</b></label>
                    <textarea id="komentar" name="komentar" rows="3" required ${isRated ? 'disabled' : ''}>${isRated ? task.komentar : ''}</textarea>
                </div>
            </form>
        `,
        showCancelButton: true,
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed && !isRated) {
            const form = document.getElementById('penilaianForm');
            const nilai = form.nilai.value;
            const komentar = form.komentar.value;
            const task_id = form.task_id.value;
            const group_id = form.group_id.value;

            fetch('penilaian.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    rate_task: true,
                    task_id,
                    group_id,
                    nilai,
                    komentar
                }),
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('success')) {
                    Swal.fire('Berhasil!', 'Penilaian telah disimpan.', 'success')
                        .then(() => {
                            document.getElementById('nilai').disabled = true;
                            document.getElementById('komentar').disabled = true;
                            window.location.reload();
                        });
                } else {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan penilaian.', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan penilaian.', 'error');
            });
        }
    });
}

    </script>
</body>
</html>