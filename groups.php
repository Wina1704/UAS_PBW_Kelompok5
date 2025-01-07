<?php
require 'db.php';
require 'functions.php';
session_start();
redirect_if_not_logged_in();

// Tambah Grup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_group'])) {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];

    $stmt = $pdo->prepare("INSERT INTO Groups (nama, deskripsi) VALUES (?, ?)");
    $stmt->execute([$nama, $deskripsi]);

    $group_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO Group_Members (user_id, group_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $group_id]);

    header('Location: groups.php');
    exit();
}

// Ambil Grup
$stmt = $pdo->prepare("SELECT g.group_id, g.nama, g.deskripsi FROM Groups g 
                        INNER JOIN Group_Members gm ON g.group_id = gm.group_id 
                        WHERE gm.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tambahkan Anggota ke Grup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invite_member'])) {
    $group_id = $_POST['group_id'];
    $email = $_POST['email'];

    // Cari User berdasarkan email
    $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $stmt = $pdo->prepare("INSERT INTO Group_Members (user_id, group_id) VALUES (?, ?)");
        $stmt->execute([$user['user_id'], $group_id]);
        $message = "Anggota berhasil diundang!";
    } else {
        $error = "Email tidak ditemukan!";
    }
}

// Hapus Anggota dari Grup
if (isset($_GET['remove_member'])) {
    $group_id = $_GET['group_id'];
    $user_id = $_GET['user_id'];

    // Pastikan hanya anggota lain yang bisa dihapus, tidak bisa menghapus diri sendiri
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM Group_Members WHERE user_id = ? AND group_id = ?");
        $stmt->execute([$user_id, $group_id]);
        $message = "Anggota berhasil dihapus!";
    } else {
        $error = "Anda tidak dapat menghapus diri sendiri!";
    }

    header("Location: groups.php?group_id=$group_id");
    exit();
}

if (isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];

    // Ambil nama grup
    $stmt = $pdo->prepare("SELECT nama FROM Groups WHERE group_id = ?");
    $stmt->execute([$group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($group) {
        $group_name = $group['nama'];

        // Ambil anggota grup
        $stmt = $pdo->prepare("SELECT u.nama, u.email, u.user_id FROM Users u 
                                INNER JOIN Group_Members gm ON u.user_id = gm.user_id 
                                WHERE gm.group_id = ?");
        $stmt->execute([$group_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Jika grup tidak ditemukan
        $error = "Grup tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Kelola Grup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        h2 {
            font-size: 20px;
            color: #444;
            margin-bottom: 15px;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"], input[type="email"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            height: 100px;
            resize: none;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            margin: 10px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        ul li a {
            text-decoration: none;
            color: #007bff;
        }

        ul li a:hover {
            text-decoration: underline;
        }

        .message {
            margin-top: 10px;
            font-size: 14px;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Kelola Grup</h1>

        <!-- Tambah Grup -->
        <section>
            <h2>Tambah Grup Baru</h2>
            <form method="POST">
                <input type="text" name="nama" placeholder="Nama Grup" required>
                <textarea name="deskripsi" placeholder="Deskripsi Grup"></textarea>
                <button type="submit" name="add_group">Tambah</button>
            </form>
        </section>

        <!-- Daftar Grup -->
        <section>
            <h2>Daftar Grup </h2>
            <ul>
                <?php foreach ($groups as $group): ?>
                    <li>
                        <span><strong><?php echo htmlspecialchars($group['nama']); ?></strong>: 
                        <?php echo htmlspecialchars($group['deskripsi']); ?></span>
                        <a href="groups.php?group_id=<?php echo $group['group_id']; ?>">Lihat Anggota</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Anggota Grup -->
        <?php if (isset($_GET['group_id']) && isset($group_name)): ?>
            <section>
                <h2>Anggota Grup: <?php echo htmlspecialchars($group_name); ?></h2>
                <ul>
                    <?php foreach ($members as $member): ?>
                        <li>
                            <?php echo htmlspecialchars($member['nama']); ?> (<?php echo htmlspecialchars($member['email']); ?>)
                            <?php if ($member['user_id'] != $_SESSION['user_id']): ?>
                                <a href="groups.php?group_id=<?php echo $group_id; ?>&remove_member=1&user_id=<?php echo $member['user_id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?');">Hapus</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Undang Anggota -->
                <h3>Undang Anggota</h3>
                <form method="POST">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <input type="email" name="email" placeholder="Email Anggota" required>
                    <button type="submit" name="invite_member">Undang</button>
                </form>
                <?php if (isset($message)) echo "<p class='message success'>$message</p>"; ?>
                <?php if (isset($error)) echo "<p class='message error'>$error</p>"; ?>
            </section>
        <?php elseif (isset($error)): ?>
            <p class='message error'><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <a href="index.php" class="btn-secondary">Kembali</a>
    </div>
</body>
</html>
