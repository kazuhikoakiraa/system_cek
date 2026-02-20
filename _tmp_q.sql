SELECT pm.id, pm.mesin_id, d.nama_mesin, pm.tanggal_pengecekan
FROM pengecekan_mesins pm
LEFT JOIN daftar_pengecekan d ON d.id = pm.mesin_id
ORDER BY pm.id DESC
LIMIT 10;
