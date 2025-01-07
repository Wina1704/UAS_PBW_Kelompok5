# 📋 Manajemen Tugas Kelompok

## 📚 Kelompok 5
*Pemrograman Berbasis Web*

## 👥 Anggota Kelompok
1. *Putri Juliani* (NIM: 4522210015)
2. *Wina Windari Kusdarniza* (NIM: 4522210017)
3. *Daiva Baskoro Upangga* (NIM: 4522210045)

## ✨ Use Case
![WhatsApp Image 2025-01-08 at 00 11 23_a86fb477](https://github.com/user-attachments/assets/1f99f5ad-607d-4a95-b379-83cc9699f857)


## ➢ Membuat Akun dan Grup
**Aktor**: 
- Users

**Use Case**:
- **Register**: Pengguna melakukan pendaftaran akun baru (baik untuk dosen maupun mahasiswa).
- **Login**: Pengguna yang sudah terdaftar masuk ke dalam sistem.
- **Logout**: Pengguna keluar dari sistem.

### **Use Case (Dosen)**:
- **Register**: Pengguna melakukan pendaftaran akun baru (baik untuk dosen maupun mahasiswa).
- **Login**: Pengguna yang sudah terdaftar masuk ke dalam sistem.
- **Melakukan Penilaian**: Dosen dapat memberikan penilaian terhadap tugas mahasiswa.
- **Menambahkan Mata Kuliah Baru**: Dosen dapat menambahkan mata kuliah baru yang diampu.
- **Melihat Daftar Mata Kuliah**: Dosen dapat melihat daftar mata kuliah yang mereka ampu.
- **Logout**: Pengguna keluar dari sistem.

### **Use Case (Mahasiswa)**:
- **Login**: Pengguna yang sudah terdaftar masuk ke dalam sistem.
- **Membuat Grup**: Mahasiswa dapat membuat grup baru.
- **Mengundang Anggota**: Mahasiswa dapat mengundang anggota lain ke dalam grup yang telah dibuat.
- **Melihat Daftar Anggota**: Mahasiswa dapat melihat daftar anggota yang ada di dalam grup.
- **Membuat Tugas**: Pengguna dapat membuat tugas baru.
- **Logout**: Pengguna keluar dari sistem.

---

## ✨ ERD
![Untitled](https://github.com/user-attachments/assets/37a68c67-4ed5-46f8-a22b-a04b3de47648)


---

## 🗃 Entitas Utama
### 1. *Users*
Mewakili pengguna sistem. Setiap pengguna memiliki atribut berikut:
- *user_id*: Identitas unik pengguna.
- *nama*: Nama lengkap pengguna.
- *email*: Email pengguna untuk autentikasi.
- *role*: Peran pengguna dalam sistem (mahasiswa/dosen/admin).
- *password*: Kata sandi untuk login.
- *tanggal_pembuatan*: Tanggal akun dibuat.

### 2. *Groups*
Mewakili kelompok atau tim dalam sistem. Setiap grup memiliki atribut berikut:
- *group_id*: Identitas unik grup.
- *nama*: Nama grup.
- *deskripsi*: Deskripsi singkat mengenai grup.
- *tanggal_pembuatan*: Tanggal grup dibuat.

### 3. *Group_Members (Tabel Perantara)*
Mewakili hubungan many-to-many antara Users dan Groups. Atributnya adalah:
- *member_id*: Identitas unik anggota grup.
- *user_id*: Referensi ke entitas Users.
- *group_id*: Referensi ke entitas Groups.
- *deskripsi*: Deskripsi peran anggota dalam grup.
- *status*: Status keanggotaan dalam grup.
- *image_path*: Lokasi gambar profil anggota.

### 4. *Tasks*
Mewakili tugas yang diberikan kepada pengguna. Setiap tugas memiliki atribut berikut:
- *task_id*: Identitas unik tugas.
- *group_id*: Referensi ke entitas Groups.
- *nama*: Nama tugas.
- *mata_kuliah*: Referensi ke entitas Mata Kuliah.
- *deskripsi*: Deskripsi tugas.
- *tanggal_jatuh_tempo*: Deadline tugas.
- *nilai*: Nilai tugas (jika sudah dinilai).
- *komentar*: Komentar terkait tugas.
- *status*: Status tugas (selesai/belum selesai).
- *file_path*: Lokasi file terkait tugas.

### 5. *Task_Assignments (Tabel Perantara)*
Mewakili hubungan many-to-many antara Users dan Tasks. Atributnya adalah:
- *assignment_id*: Identitas unik penugasan.
- *task_id*: Referensi ke entitas Tasks.
- *user_id*: Referensi ke entitas Users.

### 6. *Mata_Kuliah*
Mewakili informasi tentang mata kuliah. Setiap mata kuliah memiliki atribut berikut:
- *id_mk*: Identitas unik mata kuliah.
- *nama_mk*: Nama mata kuliah.
- *dosen_pengampu*: Dosen yang mengampu mata kuliah.
- *deskripsi*: Deskripsi mata kuliah.

### 7. *Penilaian*
Mewakili data penilaian terhadap tugas. Setiap penilaian memiliki atribut berikut:
- *id*: Identitas unik penilaian.
- *group_id*: Referensi ke entitas Groups.
- *task_id*: Referensi ke entitas Tasks.
- *nilai*: Nilai yang diberikan.
- *komentar*: Komentar penilaian.
- *tanggal_penilaian*: Tanggal penilaian dibuat.

---

## 🔗 Hubungan Antar Entitas

1. **Users** dan **Groups** (many-to-many)
   - Satu pengguna dapat menjadi anggota dari banyak grup, dan satu grup dapat memiliki banyak anggota.
   - Implementasi: Melalui tabel **Group_Members**.

2. **Users** dan **Tasks** (many-to-many)
   - Satu pengguna dapat ditugaskan pada banyak tugas, dan satu tugas dapat ditugaskan kepada banyak pengguna.
   - Implementasi: Melalui tabel **Task_Assignments**.

3. **Groups** dan **Tasks** (one-to-many)
   - Satu grup dapat memiliki banyak tugas, tetapi satu tugas hanya dapat dimiliki oleh satu grup.

4. **Tasks** dan **Mata_Kuliah** (many-to-one)
   - Banyak tugas dapat terkait dengan satu mata kuliah.

5. **Groups** dan **Penilaian** (one-to-many)
   - Satu grup dapat memiliki banyak penilaian, tetapi satu penilaian hanya dapat terkait dengan satu grup.

6. **Tasks** dan **Penilaian** (one-to-many)
   - Satu tugas dapat memiliki banyak penilaian, tetapi satu penilaian hanya terkait dengan satu tugas.

---

