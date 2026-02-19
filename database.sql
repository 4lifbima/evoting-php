-- =====================================================
-- DATABASE: voting_system
-- =====================================================
CREATE DATABASE IF NOT EXISTS voting_system;
USE voting_system;

-- =====================================================
-- TABLE: users (untuk admin/login)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'superadmin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- =====================================================
-- TABLE: voters (data pemilih)
-- =====================================================
CREATE TABLE voters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim_nis VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    kelas_jurusan VARCHAR(50),
    angkatan YEAR,
    status ENUM('aktif', 'tidak_aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE: candidates (kandidat)
-- =====================================================
CREATE TABLE candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_urut INT UNIQUE NOT NULL,
    nama_ketua VARCHAR(100) NOT NULL,
    nama_wakil VARCHAR(100),
    foto VARCHAR(255) DEFAULT 'default.jpg',
    visi TEXT,
    misi TEXT,
    angkatan YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE: votes (hasil voting - relasi utama)
-- =====================================================
CREATE TABLE votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    voter_id INT NOT NULL,
    candidate_id INT NOT NULL,
    vote_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (voter_id) REFERENCES voters(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (voter_id) -- 1 orang hanya bisa 1 vote
);

-- =====================================================
-- TABLE: voting_session (untuk kontrol periode voting)
-- =====================================================
CREATE TABLE voting_session (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_sesi VARCHAR(100) NOT NULL,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME NOT NULL,
    status ENUM('aktif', 'selesai', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- INSERT DATA SAMPLE
-- =====================================================

-- Insert admin default (password: admin123)
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES
('admin', '$2y$10$HebFVD3HH7PhDpH2U3Ea8ey0dcR0.uXiTPeraTmX41m5y7n0Bfa/O', 'Administrator', 'admin@voting.com', 'superadmin');

-- Insert sample voters
INSERT INTO voters (nim_nis, nama, kelas_jurusan, angkatan) VALUES
('2024001', 'Ahmad Fauzi', 'XII RPL 1', 2024),
('2024002', 'Budi Santoso', 'XII RPL 2', 2024),
('2024003', 'Citra Dewi', 'XII TKJ 1', 2024),
('2024004', 'Dian Permata', 'XII TKJ 2', 2024),
('2024005', 'Eko Prasetyo', 'XII MM 1', 2024),
('2024006', 'Fani Rahmawati', 'XII MM 2', 2024),
('2024007', 'Gunawan Pratama', 'XII AKL 1', 2024),
('2024008', 'Hana Pertiwi', 'XII AKL 2', 2024),
('2024009', 'Indra Wijaya', 'XII OTKP 1', 2024),
('2024010', 'Jihan Kamila', 'XII OTKP 2', 2024);

-- Insert sample candidates
INSERT INTO candidates (no_urut, nama_ketua, nama_wakil, visi, misi, angkatan) VALUES
(1, 'Raffi Ahmad', 'Nagita Slavina', 'Mewujudkan sekolah yang berkarakter dan berprestasi', '1. Meningkatkan kualitas belajar\n2. Mengadakan kegiatan positif\n3. Memperkuat solidaritas', 2024),
(2, 'Ariel Noah', 'Raisa Andriana', 'Sekolah inovatif menuju generasi emas', '1. Digitalisasi sekolah\n2. Program mentoring\n3. Workshop kewirausahaan', 2024),
(3, 'Tulus', 'Rossa', 'Membangun lingkungan sekolah yang hijau dan asri', '1. Program go green\n2. Penghijauan sekolah\n3. Bank sampah', 2024);

-- Insert voting session (active for 7 days from now)
INSERT INTO voting_session (nama_sesi, tanggal_mulai, tanggal_selesai, status) VALUES
('Pemilihan Ketua OSIS 2024', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'aktif');
