# 🗳️ E-Voting Realtime PHP

Aplikasi E-Voting realtime dengan PHP Native Prosedural yang memungkinkan pengguna melakukan voting dan melihat hasil secara otomatis tanpa reload halaman.

## ✨ Fitur Utama

### Untuk Pemilih (User)
- 🔐 Login voter dengan NIM/NIS
- 🗳️ Voting sekali (1 orang 1 suara)
- 📊 Hasil realtime (update setiap 3 detik)
- ⏱️ Countdown timer sesi voting
- 📈 Progress bar partisipasi
- 🎨 UI modern dengan Tailwind CSS
- 📱 Responsive design
- 🚀 Auto-logout 3 detik setelah voting

### Untuk Admin
- 🔑 Login admin dengan session
- 📊 Dashboard dengan statistik lengkap
- 👥 CRUD Kandidat (Tambah, Edit, Hapus)
- 🗂️ Manajemen pemilih (Import CSV, Aktif/Nonaktif)
- 📈 Lihat hasil voting dalam tabel dan chart
- 🔄 Reset voting (untuk pemilu baru)
- ⏰ Manajemen sesi voting
- 📝 Log aktivitas (IP, waktu, user agent)

## 🏗️ Arsitektur Sistem

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   FRONTEND      │────▶│   BACKEND       │────▶│   DATABASE      │
│   Tailwind CSS  │     │   PHP Native    │     │   MySQL         │
│   jQuery/Ajax   │◀────│   Procedural    │◀────│   Relasional    │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

## 📁 Struktur Folder

```
evoting-php/
├── admin/                    # Admin panel
│   ├── index.php            # Dashboard admin
│   ├── candidates.php       # Kelola kandidat
│   ├── add_candidate.php    # Tambah kandidat
│   ├── edit_candidate.php   # Edit kandidat
│   ├── delete_candidate.php # Hapus kandidat
│   ├── voters.php           # Data pemilih
│   ├── results.php          # Hasil voting
│   ├── sessions.php         # Sesi voting
│   ├── reset_votes.php      # Reset voting
│   └── ...
├── api/                      # API endpoints
│   ├── get_candidates.php   # Ambil data kandidat
│   ├── get_votes.php        # Ambil hasil vote
│   └── submit_vote.php      # Proses voting
├── includes/                 # File inti
│   ├── config.php           # Koneksi database
│   ├── functions.php        # Fungsi helper
│   └── auth.php             # Authentication
├── assets/                   # Static assets
│   ├── css/
│   └── js/
├── uploads/                  # Uploaded files
│   └── candidates/          # Foto kandidat
├── index.php                 # Halaman voting utama
├── login.php                 # Login admin
├── logout.php                # Logout handler
├── database.sql              # Database schema
└── README.md                 # Dokumentasi
```

## 💾 Database Schema

### Tabel Utama

1. **users** - Data admin
2. **voters** - Data pemilih
3. **candidates** - Data kandidat
4. **votes** - Hasil voting
5. **voting_session** - Sesi voting

## 🚀 Instalasi

### Persyaratan Sistem
- PHP >= 7.4
- MySQL >= 5.7
- Web Server (XAMPP, Laragon, WAMP, dll)
- Browser modern (Chrome, Firefox, Edge)

### Langkah Instalasi

1. **Clone atau extract project**
   ```bash
   # Letakkan di folder htdocs (XAMPP) atau www (Laragon)
   D:\laragon\www\evoting-php\
   ```

2. **Import Database**
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Buat database baru `voting_system`
   - Import file `database.sql`

3. **Konfigurasi Database**
   
   Edit file `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'voting_system');
   ```

4. **Akses Aplikasi**
   - User voting: http://localhost/evoting-php/
   - Admin panel: http://localhost/evoting-php/login.php

## 🔐 Default Credentials

**Admin Login:**
- Username: `admin`
- Password: `admin123`

**Demo Voters** (sudah ada di database):
- NIM: `2024001` - `2024010`

## 📖 Cara Penggunaan

### Untuk Admin

1. **Login**
   - Akses `/login.php`
   - Masukkan username dan password admin

2. **Kelola Kandidat**
   - Masuk ke menu "Kelola Kandidat"
   - Tambah kandidat baru dengan foto, visi, dan misi
   - Edit atau hapus kandidat jika diperlukan

3. **Kelola Pemilih**
   - Masuk ke menu "Data Pemilih"
   - Import data pemilih dari file CSV
   - Atau tambahkan manual satu per satu

4. **Atur Sesi Voting**
   - Masuk ke menu "Sesi Voting"
   - Buat sesi voting baru dengan tanggal mulai dan selesai
   - Aktifkan sesi untuk memulai voting

5. **Monitor Hasil**
   - Dashboard menampilkan statistik realtime
   - Menu "Hasil Voting" menampilkan detail per kandidat

### Untuk Pemilih

1. **Akses Halaman Voting**
   - Buka http://localhost/evoting-php/
   - Lihat daftar kandidat dan visi misi

2. **Lakukan Voting**
   - Pilih kandidat dengan klik tombol "Pilih"
   - Konfirmasi pilihan Anda
   - Setelah berhasil, auto-logout dalam 3 detik

3. **Lihat Hasil**
   - Hasil voting ditampilkan realtime
   - Update otomatis setiap 3 detik

## 🔒 Keamanan

### Fitur Keamanan
- ✅ Session-based authentication
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ Unique constraint (1 voter = 1 vote)
- ✅ Input sanitization
- ✅ File upload validation

### Untuk Production
- Ganti `md5()` dengan `password_hash()` dan `password_verify()`
- Gunakan HTTPS
- Tambahkan CSRF token
- Implementasi rate limiting
- Enable error logging (disable error display)

## 🧪 Testing

### Test Skenario

1. **1 Voter 1 Vote**
   - Login sebagai voter
   - Vote kandidat
   - Coba vote lagi → harus ditolak

2. **Auto-logout**
   - Vote berhasil
   - Tunggu 3 detik → redirect ke logout

3. **Realtime Update**
   - Buka 2 browser berbeda
   - Vote di browser 1
   - Browser 2 update otomatis dalam 3 detik

4. **Race Condition**
   - Kirim 2 request vote bersamaan
   - Hanya 1 yang tersimpan (DB constraint)

## 🛠️ Troubleshooting

### Error: Connection failed
```
Solution: Cek konfigurasi database di includes/config.php
```

### Error: Table doesn't exist
```
Solution: Import ulang database.sql
```

### Upload foto gagal
```
Solution: Pastikan folder uploads/candidates writable (chmod 777)
```

### Session tidak bekerja
```
Solution: Cek session_start() sudah dipanggil, clear browser cache
```

## 📝 API Endpoints

### GET /api/get_candidates.php
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "no_urut": 1,
      "nama_ketua": "Kandidat 1",
      ...
    }
  ]
}
```

### GET /api/get_votes.php
```json
{
  "success": true,
  "data": [...],
  "statistik": {
    "total_voters": 100,
    "voted": 50,
    "partisipasi": 50.0,
    "last_update": "14:30:45"
  }
}
```

### POST /api/submit_vote.php
```json
{
  "success": true,
  "message": "Vote successfully recorded",
  "auto_logout_in": 3,
  "redirect_to": "../logout.php?reason=vote-complete"
}
```

## 📄 License

Project ini dibuat untuk tujuan edukasi dan pembelajaran.

## 👨‍💻 Developer

Dibuat dengan ❤️ menggunakan PHP Native

---

**Happy Voting! 🗳️**
