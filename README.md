## Demo voucher MyAds

Halaman masuk menggunakan kode alfanumerik 6-8 karakter dan tombol Masuk.
Kode dummy didefinisikan di `config/vouchers.php`:

| Kode | Harga | Report | SMS LBA |
| --- | --- | --- | --- |
| MYADS20K | Rp 20.000 | 7 hari | 400 pesan |
| MYADS25K | Rp 25.000 | 7 hari | 800 pesan |
| MYADS50K | Rp 50.000 | 30 hari | 10.000 pesan |
| RPT20K01 | Rp 20.000 | 7 hari | 400 pesan (campaign selesai + report dummy) |

Kupon `RPT20K01` langsung membuka report campaign SMS LBA yang sudah selesai:
400 pesan dikirim, 368 berhasil, 32 gagal (92%). Report mencakup ringkasan pengiriman,
lokasi, jadwal, dan pesan campaign dalam tampilan mobile. Data dan tanggal campaign
dibuat sebagai simulasi saat redeem; kupon ini hanya untuk melihat report.

Kode menerima huruf besar/kecil. Setelah redeem, form setup campaign SMS LBA
langsung tampil di `/dashboard` dengan tampilan mobile dan kuota sesuai kode.
Jumlah penerima dikunci sesuai kode: 400, 800, atau 10.000; server mengabaikan
perubahan jumlah dari form. Isi pesan, lokasi melalui popup peta LBA, radius,
rentang tanggal dan jam WIB, serta nomor HP testing Anda, lalu simpan
setup campaign. Draft dapat diedit dan hanya tersimpan selama sesi. Jenis campaign
dan durasi report berasal dari kode; paket 50K menampilkan jumlah tetap 10.000.
Pengiriman tersedia 06.00-20.00 WIB dengan rentang jam minimal 4 jam per hari dan
waktu mulai minimal 4 jam dari sekarang. Default rentang adalah 4 jam; jika tidak
cukup sebelum 20.00, default pindah ke hari berikutnya 06.00-10.00.
Satu kolom nomor testing menampilkan prefix 62 tetap yang tidak bisa dihapus.
Gunakan Tambah nomor testing untuk menambahkan nomor ke daftar vertikal di bawah
input, atau Hapus untuk menghapus nomor dari daftar. Pengguna mengisi nomor mulai dari 8; server menyimpan daftar
nomor lengkap dengan awalan 628 dan menggabungkan nomor yang duplikat.
Setelah setup campaign tersimpan, popup meminta email report secara opsional.
Tombol Lewati menutup popup tanpa mengubah campaign. Email dapat ditambahkan
atau diubah kemudian dan tetap tersimpan saat draft diedit. Mode dummy belum
mengirim report melalui email.
Tombol Keluar / Ganti kode mengakhiri sesi dan menghapus draft. Ini hanya demo:
pengiriman SMS dan report belum terhubung ke API, kode dapat dipakai ulang.

Jalankan `composer install`, siapkan `.env` dan `APP_KEY`, lalu
`php artisan serve`. Uji alur akses dengan `php artisan test --filter=AuthLoginTest`.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
