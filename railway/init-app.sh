#!/bin/bash
# Pre-Deploy Command untuk service "App" di Railway.
#
# Jalankan lewat Railway: Settings -> Deploy -> Pre-Deploy Command
#   chmod +x ./railway/init-app.sh && sh ./railway/init-app.sh
#
# Migration otomatis Railpack sengaja dimatikan (RAILPACK_SKIP_MIGRATIONS=true)
# supaya seeding bawaan tidak pernah ikut berjalan di produksi — proyek ini
# punya akun seeder berpassword lemah (lihat README bagian "Akun Bawaan").
# Karena itu migration dijalankan di sini, secara eksplisit, tanpa --seed.
set -e

php artisan migrate --force
