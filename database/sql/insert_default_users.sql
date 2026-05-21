-- =====================================================================
-- Insert 5 default users into m_user
-- Engine target: SQL Server (sqlsrv)
-- Password encryption: PHP password_hash() with PASSWORD_BCRYPT (cost 10)
--
-- Default credentials:
--   superadmin / superadmin123    (kd_group: US0000)
--   admin1     / admin123         (kd_group: US0001)
--   admin2     / admin123         (kd_group: US0001)
--   kasir1     / kasir123         (kd_group: US0002)
--   kasir2     / kasir123         (kd_group: US0002)
--
-- Catatan:
--   - Hash di bawah ini compatible dengan password_verify() / Hash::check().
--   - Jalankan file ini SEKALI saja. Jika sudah ada datanya, hapus dulu.
-- =====================================================================

INSERT INTO m_user (kd_user, kd_group, username, password, keterangan, status) VALUES
('UAA000', 'US0000', 'superadmin', '$2y$10$mN0vf3AggD/EMjMLQ9UbeObW/wvz4sN12QCnop9B8MnecxZub3ynO', 'Super Administrator', 1),
('UAA001', 'US0001', 'admin1',     '$2y$10$1TF/ICLUO8qBWhTo8EoV0OxYXce5CCc4zDYioN79H8t4Kp54xaSdm', 'Administrator 1',     1),
('UAA002', 'US0001', 'admin2',     '$2y$10$jx3kGv2rUwJuF/tq/gHbJOJIlUMxI7nAwMSjf/bmxYFdCEUc8XDSe', 'Administrator 2',     1),
('UAA003', 'US0002', 'kasir1',     '$2y$10$9nFQENtzYdEsiZA.vrwrd./LcYUXpSnLsWoe6RuEpHG/jK5slmTn6', 'Kasir 1',             1),
('UAA004', 'US0002', 'kasir2',     '$2y$10$NSXDdg75MagEQ64PN5rQ.uIaUjhasHKAGj7KIDH2x6ISXnJ.YMXE2', 'Kasir 2',             1);
