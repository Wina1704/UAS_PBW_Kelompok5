<?php
require 'db.php';
require 'functions.php';
session_start();
redirect_if_not_logged_in();

// Hitung Total Groups
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_groups FROM Groups g 
                        INNER JOIN Group_Members gm ON g.group_id = gm.group_id 
                        WHERE gm.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_groups = $stmt->fetch(PDO::FETCH_ASSOC)['total_groups'];

// Hitung Total Tasks
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_tasks FROM tasks t 
                        INNER JOIN Groups g ON t.group_id = g.group_id 
                        INNER JOIN Group_Members gm ON g.group_id = gm.group_id 
                        WHERE gm.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_tasks = $stmt->fetch(PDO::FETCH_ASSOC)['total_tasks'];

// Ambil Tugas
$stmt = $pdo->prepare("SELECT t.task_id, t.nama AS task_nama, t.deskripsi, t.tanggal_jatuh_tempo, t.status, g.nama AS group_nama 
                       FROM tasks t
                       INNER JOIN Groups g ON t.group_id = g.group_id
                       INNER JOIN Group_Members gm ON g.group_id = gm.group_id
                       WHERE gm.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Menghitung progress berdasarkan status anggota untuk setiap tugas
for ($i = 0; $i < count($tasks); $i++) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_members, 
                                     SUM(CASE WHEN gm.status = 'selesai' THEN 1 ELSE 0 END) AS completed_members
                          FROM Group_Members gm 
                          WHERE gm.group_id = (SELECT group_id FROM Tasks WHERE task_id = ?)");
    $stmt->execute([$tasks[$i]['task_id']]);
    $member_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($member_data['total_members'] > 0) {
        $progress = ($member_data['completed_members'] / $member_data['total_members']) * 100;
    } else {
        $progress = 0;
    }

    $tasks[$i]['progress'] = round($progress);
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" href="assets/styles.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <title>Dashboard</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Menu</h2>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                <li><a href="groups.php"><i class="fas fa-users"></i><span>Kelola Grup</span></a></li>
                <li><a href="tasks.php"><i class="fas fa-tasks"></i><span>Kelola Tugas</span></a></li>
                <li><a href="#" id="logoutBtn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
            <!-- Statistik -->
            <div class="stats">
                <div class="stat-box">
                    <p><i class="fas fa-users"></i> Total Groups</p>
                    <h2><?php echo $total_groups; ?></h2>
                </div>
                <div class="stat-box">
                    <p><i class="fas fa-tasks"></i> Total Tasks</p>
                    <h2><?php echo $total_tasks; ?></h2>
                </div>
            </div>

            <!-- Tabel Tugas -->
            <h2>Daftar Tugas</h2>
            <table class="projects-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Tugas</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tasks) > 0): ?>
                        <?php foreach ($tasks as $index => $task): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($task['task_nama']); ?></td>
                                <td><?php echo htmlspecialchars($task['status']); ?></td>
                                <td>
                                    <div style="width: 100px; background: #f4f4f4; border-radius: 5px; overflow: hidden;">
                                        <div style="width: <?php echo $task['progress']; ?>%; background: #4caf50; height: 10px;"></div>
                                    </div>
                                    <small><?php echo $task['progress']; ?>% Complete</small>
                                </td>
                                <td>
                                    <a href="view_task.php?task_id=<?php echo $task['task_id']; ?>" class="btn primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Belum ada tugas yang tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- JavaScript untuk SweetAlert pada logout -->
        <script>
            document.getElementById('logoutBtn').addEventListener('click', function(event) {
                event.preventDefault(); // Mencegah tautan langsung bekerja
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
                        // Arahkan ke halaman logout jika dikonfirmasi
                        window.location.href = 'logout.php';
                    }
                });
            });
        </script>
    </body>
</html>
