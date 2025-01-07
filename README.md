# 📋 Manajemen Tugas Kelompok

## 📚 Kelompok 5
*Pemrograman Berbasis Web*

## 👥 Anggota Kelompok
1. *Putri Juliani* (NIM: 4522210015)
2. *Wina Windari Kusdarniza* (NIM: 4522210017)
3. *Daiva Baskoro Upangga* (NIM: 4522210045)
Use Case Diagram

1. Membuat Akun dan Grup
Aktor:
Users
Use Case Dosen:
Register: Users melakukan pendaftaran akun baru untuk dosen dan mahasiswa.
Login: Users yang sudah terdaftar masuk ke dalam sistem.
Logout: Users keluar dari sistem.
Melakukan 
Penilaian: Users dapat melakukan penilaian terhadap tugas mahasiswa.
Menambahkan Mata Kuliah Baru: Users dapat menambahkan mata kuliah baru yang user ampu.
Melihat Daftar Mata Kuliah: Users dapat melihat daftar mata kuliah yang di ampu.

Use Case Mahasiswa:
Login: Users yang sudah terdaftar masuk ke dalam sistem.
Logout: Users keluar dari sistem.
Membuat Grup: Users dapat membuat grup baru.
Mengundang Anggota: Users dapat mengundang anggota lain ke dalam grup yang telah dibuat.
Melihat Daftar Anggota: Users dapat melihat daftar anggota yang ada di dalam grup.

3. Membuat Tugas Pada Sistem
Aktor:
Users
Use Case:
Membuat Tugas: Users dapat membuat tugas baru.
Mengedit Tugas: Users dapat mengubah detail tugas yang sudah ada.
Menghapus Tugas: Users dapat menghapus tugas yang tidak diperlukan lagi.
Menandai Tugas Selesai: Users dapat menandai tugas sebagai selesai.
Menandai Tugas Belum Selesai: Users dapat menandai tugas yang belum selesai.
![WhatsApp Image 2025-01-08 at 00 11 23_a86fb477](https://github.com/user-attachments/assets/3cdd3419-5ffe-4619-8449-6ebb7ec3e142)


ERD (Entity-Relationship Diagram)
Entitas Utama

Users
user_id: Identitas unik pengguna.
nama: Nama lengkap pengguna.
email: Email pengguna untuk autentikasi.
role: Peran pengguna dalam sistem (mahasiswa/dosen/admin).
mata_kuliah: ID mata kuliah yang diambil pengguna.
password: Kata sandi untuk login.
tanggal_pembuatan: Tanggal akun dibuat.

Groups
group_id: Identitas unik grup.
nama: Nama grup.
deskripsi: Deskripsi singkat mengenai grup.
tanggal_pembuatan: Tanggal grup dibuat.

Group_Members (Tabel Perantara)
member_id: Identitas unik anggota grup.
user_id: Referensi ke entitas Users.
group_id: Referensi ke entitas Groups.
deskription: Deskripsi peran anggota dalam grup.
status: Status keanggotaan dalam grup.
image_path: Lokasi gambar profil anggota.

Tasks
task_id: Identitas unik tugas.
group_id: Referensi ke entitas Groups.
nama: Nama tugas.
mata_kuliah: Referensi ke entitas Mata Kuliah.
deskripsi: Deskripsi tugas.
tanggal_jatuh_tempo: Deadline tugas.
nilai: Nilai tugas (jika sudah dinilai).
komentar: Komentar terkait tugas.
status: Status tugas (selesai/belum selesai).
file_path: Lokasi file terkait tugas.

Task_Assignments (Tabel Perantara)
assignment_id: Identitas unik penugasan.
task_id: Referensi ke entitas Tasks.
user_id: Referensi ke entitas Users.

Mata_Kuliah
id_mk: Identitas unik mata kuliah.
nama_mk: Nama mata kuliah.
dosen_pengampu: Dosen yang mengampu mata kuliah.
deskripsi: Deskripsi mata kuliah.

Penilaian
id: Identitas unik penilaian.
group_id: Referensi ke entitas Groups.
task_id: Referensi ke entitas Tasks.
nilai: Nilai yang diberikan.
komentar: Komentar penilaian.
tanggal_penilaian: Tanggal penilaian dibuat.

![Untitled](https://github.com/user-attachments/assets/513a485e-0fe1-45ad-8383-d980aa10974d)
