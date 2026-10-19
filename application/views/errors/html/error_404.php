<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Halaman Tidak Ditemukan - LIS PRO SIMRS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
        body { background-color: #f8fafc; color: #1e293b; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .card { max-width: 500px; width: 100%; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 2.5rem 2rem; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01); }
        .badge { display: inline-block; padding: 4px 12px; background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; border-radius: 9999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; }
        .icon { width: 64px; height: 64px; margin: 0 auto 1.25rem; background: linear-gradient(135deg, #0284c7, #06b6d4); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.3); font-size: 28px; font-weight: bold; }
        h1 { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; }
        p { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; }
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 20px; background: #0284c7; color: white; text-decoration: none; font-size: 13px; font-weight: 600; border-radius: 12px; transition: background 0.2s; box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2); }
        .btn:hover { background: #0369a1; }
        .info { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">404</div>
        <span class="badge">SIMRS LIS PRO &bull; Modul Tidak Ditemukan</span>
        <h1><?php echo html_escape($heading); ?></h1>
        <p><?php echo strip_tags($message); ?></p>
        <a href="<?php echo config_item('base_url'); ?>" class="btn">Kembali ke Halaman Utama</a>
        <div class="info">
            Laboratory Information System &bull; CodeIgniter 3
        </div>
    </div>
</body>
</html>