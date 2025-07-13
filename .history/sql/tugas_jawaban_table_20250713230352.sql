CREATE TABLE IF NOT EXISTS tugas_jawaban (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_tugas INT,
  id_user INT,
  file_jawaban VARCHAR(255),
  nilai INT,
  komentar TEXT,
  FOREIGN KEY (id_tugas) REFERENCES tugas(id),
  FOREIGN KEY (id_user) REFERENCES users(id)
);
