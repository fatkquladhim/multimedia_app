CREATE TABLE tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    deadline DATE NOT NULL,
    id_penerima_tugas INT NOT NULL,
    status ENUM('belum selesai', 'selesai') NOT NULL DEFAULT 'belum selesai',
    FOREIGN KEY (id_penerima_tugas) REFERENCES users(id) ON DELETE CASCADE
);
