CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_barang INT NOT NULL,
    id_user INT NOT NULL,
    tanggal_peminjaman DATE NOT NULL,
    tanggal_pengembalian DATE,
    status ENUM('dipinjam', 'dikembalikan') NOT NULL,
    FOREIGN KEY (id_barang) REFERENCES barang(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
);
