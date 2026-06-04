-- Import this in phpMyAdmin AFTER creating your database in cPanel.
-- Select your database first, then Import this file.

CREATE TABLE IF NOT EXISTS tbl_transmittal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_branch VARCHAR(100) NOT NULL,
    to_branch VARCHAR(100) NOT NULL,
    released_by VARCHAR(100) NOT NULL,
    date_released DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_transmittal_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transmittal_id INT NOT NULL,
    pad_no INT NOT NULL,
    si_start BIGINT NOT NULL,
    si_end BIGINT NOT NULL,
    FOREIGN KEY (transmittal_id) REFERENCES tbl_transmittal(id) ON DELETE CASCADE
);

CREATE INDEX idx_transmittal_date ON tbl_transmittal(date_released);
CREATE INDEX idx_details_transmittal ON tbl_transmittal_details(transmittal_id);
