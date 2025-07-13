CREATE TABLE tugas_jawaban (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tugas INT NOT NULL,
    id_user INT NOT NULL,
    jawaban TEXT NOT NULL,
    tanggal_kirim DATETIME NOT NULL,
    FOREIGN KEY (id_tugas) REFERENCES tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);
