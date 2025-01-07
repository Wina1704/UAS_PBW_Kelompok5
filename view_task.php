<?php
require 'db.php';
require 'functions.php';
session_start();
redirect_if_not_logged_in();

if (!isset($_GET['task_id'])) {
    die('Task ID tidak ditemukan.');
}

$task_id = $_GET['task_id'];

// Ambil data tugas berdasarkan task_id
$stmt = $pdo->prepare("SELECT t.task_id, t.nama AS task_nama, t.deskripsi, t.tanggal_jatuh_tempo, t.status, g.nama AS group_nama, g.group_id 
                        FROM Tasks t
                        INNER JOIN Groups g ON t.group_id = g.group_id
                        WHERE t.task_id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$task) {
    die('Tugas tidak ditemukan.');
}

// Cek jika task sudah selesai, maka status tidak bisa diubah
$is_task_completed = $task['status'] === 'selesai';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_status']) && !$is_task_completed) {
    $member_id = $_POST['member_id'];
    $description = $_POST['description'];
    $status = ($_POST['save_status'] === 'finish') ? 'selesai' : 'sedang mengerjakan';

    $image_path = null;

    // Ambil image_path lama dari database
    $stmt = $pdo->prepare("SELECT image_path FROM Group_Members WHERE member_id = ?");
    $stmt->execute([$member_id]);
    $old_image = $stmt->fetchColumn();

    if (isset($_FILES['task_image']) && $_FILES['task_image']['error'] === UPLOAD_ERR_OK) {
        // Jika ada file diunggah, hapus file lama
        if ($old_image && file_exists($old_image)) {
            unlink($old_image);
        }

        $upload_dir = 'uploads/';
        $file_name = time() . '_' . basename($_FILES['task_image']['name']);
        $upload_file = $upload_dir . $file_name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['task_image']['tmp_name'], $upload_file)) {
            $image_path = $upload_file;
        } else {
            echo "Gagal mengupload gambar.";
        }
    } else {
        // Jika tidak ada file diunggah, gunakan gambar lama
        $image_path = $old_image;
    }

    // Update data anggota
    $stmt = $pdo->prepare("UPDATE Group_Members SET description = ?, status = ?, image_path = ? WHERE member_id = ?");
    $stmt->execute([$description, $status, $image_path, $member_id]);

    header("Location: view_task.php?task_id=" . $_GET['task_id']);
    exit();
}

$stmt = $pdo->prepare("
    SELECT gm.member_id, u.nama, u.email, gm.description, gm.status, gm.image_path
    FROM group_members gm
    INNER JOIN users u ON gm.user_id = u.user_id
    WHERE gm.group_id = ?
");
$stmt->execute([$task['group_id']]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .task-detail {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .section-title {
            margin-top: 30px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
        }
        .list-group-item {
            border: none;
        }
        .back-button {
            margin-top: 20px;
            background-color: #0056b3;
        }
        .list-group-item {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 8px;
            padding: 15px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .collapse {
            border-top: 1px dashed #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .task-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Description text box */
        .description-box {
            width: 100%;
            height: 150px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
            transition: border-color 0.3s ease;
        }

        .description-box:focus {
            border-color: #3498db;
            outline: none;
        }

        /* File input styling */
        .file-input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 15px;
            background-color: #fff;
            cursor: pointer;
        }

        /* Button container */
        .button-container {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        /* Save button */
        .save-btn {
            background-color: #3498db;
            color: white;
        }

        .save-btn:hover {
            background-color: #2980b9;
        }

        /* Finish button */
        .finish-btn {
            margin-right:425px;
            background-color: #2ecc71;
            color: white;
        }

        .finish-btn:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Detail Tugas</h1>
        <div class="task-detail">
            <h5><i class="fas fa-tasks text-primary me-2"></i>Nama Tugas</h5>
            <p><?php echo htmlspecialchars($task['task_nama']); ?></p>

            <h5><i class="fas fa-users text-success me-2"></i>Grup</h5>
            <p><?php echo htmlspecialchars($task['group_nama']); ?></p>

            <h5><i class="fas fa-info-circle text-warning me-2"></i>Status</h5>
            <p>
                <span class="badge bg-<?php echo $task['status'] == 'selesai' ? 'success' : 'warning'; ?>">
                    <?php echo htmlspecialchars(ucfirst($task['status'])); ?>
                </span>
            </p>

            <h5><i class="fas fa-calendar-alt text-danger me-2"></i>Deadline</h5>
            <p><?php echo htmlspecialchars($task['tanggal_jatuh_tempo']); ?></p>

            <h5><i class="fas fa-align-left text-secondary me-2"></i>Deskripsi</h5>
            <p><?php echo nl2br(htmlspecialchars($task['deskripsi'])); ?></p>
        </div>
        <h2 class="section-title">Anggota yang Terlibat</h2>
        <div class="task-detail">
            <?php if (!empty($members)): ?>
                <ul class="list-group">
                    <?php foreach ($members as $member): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($member['nama']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($member['email']); ?></small>
                                    <br>
                                    <span class="badge bg-<?php echo $member['status'] == 'selesai' ? 'success' : ($member['status'] == 'sedang mengerjakan' ? 'warning' : 'secondary'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($member['status'])); ?>
                                    </span>
                                </div>
                                <?php if (!$is_task_completed): ?>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#member-<?php echo $member['member_id']; ?>">
                                        Ubah Status
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="collapse mt-3" id="member-<?php echo $member['member_id']; ?>">
                                <form method="POST" enctype="multipart/form-data" class="task-form">
                                    <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">

                                    <!-- Deskripsi Tugas -->
                                    <textarea name="description" class="description-box" placeholder="Deskripsi tugas" required><?php echo htmlspecialchars($member['description']); ?></textarea>

                                    <!-- Input Gambar -->
                                    <?php if ($member['image_path']): ?>
                                        <div>
                                            <img src="<?php echo htmlspecialchars($member['image_path']); ?>" alt="Image" class="img-fluid mb-3" style="max-height: 200px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="task_image" class="file-input" accept="image/*">

                                    <!-- Button Save dan Finish -->
                                    <div class="button-container">
                                        <button type="submit" name="save_status" value="save" class="btn save-btn">Save</button>
                                        <button type="submit" name="save_status" value="finish" class="btn finish-btn">Finish</button>
                                    </div>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">Tidak ada anggota yang terlibat dalam tugas ini.</p>
            <?php endif; ?>
        </div>
        <a href="index.php" class="btn btn-primary back-button"><i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
