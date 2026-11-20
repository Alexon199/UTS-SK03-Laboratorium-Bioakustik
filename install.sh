#!/usr/bin/env bash
# Instalasi otomatis: membuat proyek Laravel 12 baru, lalu menimpanya dengan kode aplikasi Bioakustik.
#
# Pemakaian:   ./install.sh [folder-tujuan]      (bawaan: ../bioakustik-app)
# Prasyarat:   PHP >= 8.2, Composer, MySQL/MariaDB.
set -euo pipefail

SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TARGET="${1:-$SRC/../bioakustik-app}"

command -v php >/dev/null 2>&1 || { echo "PHP belum terpasang."; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "Composer belum terpasang."; exit 1; }

if [ -e "$TARGET" ]; then
    echo "Folder tujuan sudah ada: $TARGET"
    exit 1
fi

echo "==> Membuat proyek Laravel 12 di $TARGET"
composer create-project "laravel/laravel:^12.0" "$TARGET" --no-interaction --prefer-dist

echo "==> Menyalin kode aplikasi Bioakustik"
for dir in app database lang public resources routes tests; do
    mkdir -p "$TARGET/$dir"
    cp -R "$SRC/$dir/." "$TARGET/$dir/"
done

# ExampleTest bawaan mengharapkan GET / = 200, sedangkan di sini "/" dialihkan ke /recordings.
rm -f "$TARGET/tests/Feature/ExampleTest.php"

cp "$SRC/.env.example" "$TARGET/.env.example"
cp "$SRC/.env.example" "$TARGET/.env"
( cd "$TARGET" && php artisan key:generate --ansi )

cat <<EOF

Selesai. Langkah berikutnya:

  1. Buat database MySQL:
       mysql -u root -p -e "CREATE DATABASE bioakustik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  2. Sesuaikan DB_USERNAME / DB_PASSWORD di $TARGET/.env
  3. cd $TARGET
     php artisan migrate --seed
     php artisan test            # opsional, memakai SQLite in-memory
     php artisan serve           # buka http://localhost:8000
EOF
