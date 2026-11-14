<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# 🎓 E-Learning ARROJA (LMS Multi-Unit MTs & MA)

**E-Learning ARROJA** adalah sistem manajemen pembelajaran (*Learning Management System* / LMS) berbasis web yang dirancang khusus untuk memenuhi kebutuhan kegiatan belajar mengajar di lingkungan **Yayasan ARROJA** yang menaungi dua jenjang pendidikan sekaligus: **Madrasah Tsanawiyah (MTs)** dan **Madrasah Aliyah (MA)**.

Sistem ini dibangun dengan arsitektur yang sangat ringan, cepat, dan ramah terhadap perangkat berspesifikasi rendah maupun akses melalui *smartphone* siswa.

---

## ✨ Fitur Utama

### 1. Arsitektur Multi-Unit & Multi-Role (Single Sign-On)
* **Satu Pintu Masuk:** Seluruh warga sekolah (Operator, Guru, Wali Kelas, dan Siswa) masuk melalui satu halaman login menggunakan **Nomor Induk (NISN / NUPTK / Username)**.
* **Dukungan Lintas Sekolah:** Guru yang mengajar di kedua jenjang (MTs dan MA) cukup menggunakan **1 akun** untuk mengelola seluruh kelas dan mata pelajaran yang diampu.

### 2. Hak Akses & Dashboard Terpadu
* **Operator / Admin Yayasan:**
  * Statistik ringkas jumlah guru, siswa MTs, siswa MA, rombongan belajar, dan mata pelajaran.
  * Manajemen Data Master penuh (**CRUD**): Data Guru, Data Kelas & Wali Kelas, Data Siswa (otomatis masuk rombel), serta Plotting Mata Pelajaran.
* **Guru Mata Pelajaran & Wali Kelas:**
  * Tampilan kartu kelas lintas unit (MTs & MA) beserta indikator tugas masuk yang belum dinilai.
  * **Fitur Wali Kelas Otomatis:** Guru yang ditunjuk sebagai Wali Kelas otomatis mendapatkan panel pemantauan khusus untuk kelas perwaliannya.
  * *(Dalam Pengembangan)* Manajemen Materi ajar, Tugas dengan tenggat waktu (*deadline*), Penilaian, dan Presensi kelas.
* **Siswa (MTs & MA):**
  * Daftar tugas aktif (*To-Do List*) lengkap dengan status pengumpulan dan nilai.
  * Daftar mata pelajaran sesuai rombongan belajar masing-masing.

---

## 🛠️ Teknologi yang Digunakan (*Tech Stack*)

* **Backend:** PHP 8.x, Laravel Framework
* **Frontend:** Blade Templating, Bootstrap 5 (via CDN), Alpine.js (via CDN)
* **Database:** SQLite (Ringan, cepat, portabel, tanpa memerlukan servis database server terpisah)

---

## 🚀 Cara Instalasi di Komputer Lokal

1. **Clone repositori ini:**
   ```bash
   git clone [https://github.com/GhaniAlzyad/lms-sekolah.git](https://github.com/GhaniAlzyad/lms-sekolah.git)
   cd lms-sekolah

2. Instal dependensi PHP menggunakan Composer:
	```bash
	composer install

3. Salin file konfigurasi .env dan generate application key:
	```bash
	cp .env.example .env
	php artisan key:generate

4. Pastikan konfigurasi database di file .env menggunakan SQLite:
	DB_CONNECTION=sqlite

5. Jalankan migrasi database beserta data awal (seeder):
	```bash
	php artisan migrate:fresh --seed

6. Jalankan server lokal:
	```bash
	php artisan serve
Akses aplikasi melalui browser di alamat http://127.0.0.1:8000.

🔑 Akun Uji Coba (Default Seeder)
Setelah menjalankan perintah --seed, kamu dapat langsung menguji sistem menggunakan kredensial berikut (kata sandi untuk semua akun: password123):
Peran (Role),Nomor Induk / Username,Keterangan Akses
Admin Yayasan,admin,Kelola data master unit MTs & MA
Guru (MTs & MA),19980101,Mengampu mapel di MTs & MA + Wali Kelas X-IPA-1
Siswa MTs,1001,Siswa unit MTs (Kelas VII-A)
Siswa MA,2001,Siswa unit MA (Kelas X-IPA-1)

🗺️ Peta Jalan Pengembangan (Roadmap)
[x] Autentikasi Multi-Role berbasis Nomor Induk (NISN/NUPTK)

[x] Dashboard terpisah untuk Admin, Guru (Lintas Unit & Wali Kelas), dan Siswa

[x] Modul CRUD Data Master Admin (Guru, Kelas, Siswa, Plotting Mapel)

[ ] Ruang Kelas Guru: Posting Materi (YouTube/Drive), Tugas, & Penilaian

[ ] Ruang Kelas Siswa: Akses Materi & Pengumpulan Tugas

[ ] Modul Presensi Siswa & Rekapitulasi Wali Kelas

[ ] Modul Ujian Berbasis Komputer (CBT)

👨‍💻 Pengembang
Dikembangkan oleh Ghani Alzyad — Mahasiswa Sistem Informasi, Fakultas Ilmu Komputer Institut Teknologi Garut (ITG) & Pendidik di Lingkungan Yayasan ARROJA.