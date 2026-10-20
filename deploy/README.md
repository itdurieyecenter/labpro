# Deployment LIS PRO ke VPS

Stack terisolasi dalam Docker Compose: **tidak** mengubah konfigurasi aplikasi lain
yang berjalan di VPS yang sama.

| Komponen | Isi |
|---|---|
| `docker/Dockerfile` | `php:8.0-apache` + ekstensi `mysqli`, mod_rewrite, header keamanan, folder session PHP |
| `docker/vhost.conf` | DocumentRoot, `AllowOverride All` untuk `.htaccess`, `SetEnv CI_ENV production`, blokir akses langsung ke `application/`, `system/`, dan berkas `*.sql`/`*.pem` |
| `docker/zz-lispro.ini` | timezone Asia/Jakarta, `display_errors=Off`, `session.save_path`, limit upload 20M |
| `docker-compose.yml` | layanan `web` (host port 8085) + `db` (MariaDB 10.11, hanya jaringan internal) |
| `.env.example` | contoh variabel rahasia; file `.env` yang sebenarnya hanya ada di server (chmod 600) |

## Tata letak di server

```
/home/<user>/lispro/
├── app/              # kode aplikasi (bind-mount ke /var/www/html)
├── docker/           # dari repo ini
├── initdb/           # dump SQL awal, hanya dipakai saat volume DB masih kosong
├── backups/          # hasil backup harian (retensi 14 hari)
├── docker-compose.yml
├── backup.sh
└── .env              # rahasia, tidak ada di repo
```

## Perintah operasional

```bash
cd ~/lispro
docker compose ps                 # status
docker compose logs -f web        # log Apache/PHP
docker compose restart web        # restart web saja
./backup.sh                       # backup manual (cron jalan 02:15 WIB)
```

## Memperbarui kode aplikasi

Dari mesin pengembang (folder repo):

```bash
tar -czf - --exclude='./ALLDOC' --exclude='./.git' --exclude='./node_modules' \
    --exclude='./database' --exclude='./deploy' --exclude='*.pem' . \
  | ssh -i ~/.ssh/<kunci>.pem <user>@<host> 'tar xzf - -C ~/lispro/app'
ssh -i ~/.ssh/<kunci>.pem <user>@<host> 'cd ~/lispro && docker compose restart web'
```

Rebuild image hanya perlu bila `docker/` berubah: `docker compose build && docker compose up -d`.

## Konfigurasi lewat environment

Agar tidak ada kredensial produksi di repo, empat nilai dibaca dari environment dengan
fallback ke nilai pengembangan lokal (XAMPP):

- `application/config/database.php` — `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
- `application/config/config.php` — `APP_BASE_URL`, `CI_ENCRYPTION_KEY`, `CI_LOG_THRESHOLD`
- `index.php` — `CI_ENV` (sudah didukung CodeIgniter sejak awal, diisi lewat `SetEnv` di vhost)

## Migrasi database

Volume `lispro_dbdata` diisi sekali dari `initdb/` saat container DB pertama kali dibuat.
Untuk memuat ulang dari nol: `docker compose down -v` lalu `docker compose up -d`
(**menghapus seluruh data**; backup dulu).
