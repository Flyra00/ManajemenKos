#!/bin/bash
# Custom Start Command untuk service "Cron" di Railway.
#
# Jalankan lewat Railway: Settings -> Deploy -> Custom Start Command
#   chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh
#
# Railpack tidak menjalankan Laravel scheduler, sehingga tanpa service ini
# perintah terjadwal di routes/console.php (kos:generate-monthly-bills,
# tanggal 1 pukul 00:05) tidak akan pernah dieksekusi.
#
# Loop ini meniru cron: menjalankan schedule:run setiap 60 detik.
while [ true ]
do
echo "Running the scheduler..."
php artisan schedule:run --verbose --no-interaction &
sleep 60
done
