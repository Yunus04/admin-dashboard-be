<?php

return [
    // General
    'success' => 'Berhasil',
    'error' => 'Error',
    'created' => 'Berhasil dibuat',
    'not_found' => 'Data tidak ditemukan',
    'forbidden' => 'Akses ditolak',
    'unauthorized' => 'Tidak terotorisasi',
    'validation_failed' => 'Validasi gagal',
    'invalid_role' => 'Role tidak valid',

    // Auth
    'auth' => [
        'login_success' => 'Login berhasil',
        'invalid_credentials' => 'Kredensial tidak valid',
        'unauthorized' => 'Tidak terotorisasi',
        'logged_out' => 'Logout berhasil',
        'password_reset_sent' => 'Jika email Anda terdaftar, Anda akan menerima link reset password.',
        'password_reset_success' => 'Reset password berhasil. Silakan login dengan password baru Anda.',
        'invalid_token' => 'Token atau email tidak valid',
    ],

    // Users
    'users' => [
        'retrieved' => 'Users berhasil diambil',
        'created' => 'User berhasil dibuat',
        'not_found' => 'User tidak ditemukan',
        'updated' => 'User berhasil diupdate',
        'deleted' => 'User berhasil dihapus',
        'restored' => 'User berhasil dipulihkan',
        'cannot_change_super_admin' => 'Tidak dapat mengubah role Super Admin',
        'cannot_delete_super_admin' => 'Tidak dapat menghapus Super Admin',
        'cannot_create_admin' => 'Admin tidak dapat membuat akun Admin lain',
        'cannot_manage_admin' => 'Admin tidak dapat mengelola akun Admin lain',
        'failed_retrieve' => 'Gagal mengambil users: :message',
        'failed_create' => 'Gagal membuat user: :message',
        'failed_update' => 'Gagal mengupdate user: :message',
        'failed_delete' => 'Gagal menghapus user: :message',
        'failed_restore' => 'Gagal memulihkan user: :message',
    ],

    // Merchants
    'merchants' => [
        'retrieved' => 'Merchants berhasil diambil',
        'created' => 'Merchant berhasil dibuat',
        'not_found' => 'Merchant tidak ditemukan',
        'updated' => 'Merchant berhasil diupdate',
        'deleted' => 'Merchant berhasil dihapus',
        'already_exists' => 'User sudah memiliki profil merchant',
        'forbidden_view' => 'Dilarang. Anda hanya dapat melihat profil merchant Anda sendiri.',
        'forbidden_update' => 'Dilarang. Anda hanya dapat mengupdate profil merchant Anda sendiri.',
        'forbidden_delete' => 'Dilarang. Merchants tidak dapat menghapus profil merchant.',
        'failed_retrieve' => 'Gagal mengambil merchants: :message',
        'failed_create' => 'Gagal membuat merchant: :message',
        'failed_update' => 'Gagal mengupdate merchant: :message',
        'failed_delete' => 'Gagal menghapus merchant: :message',
    ],

    // Dashboard
    'dashboard' => [
        'retrieved' => 'Data dashboard berhasil diambil',
        'super_admin' => 'Data Dashboard Super Admin',
        'admin' => 'Data Dashboard Admin',
        'merchant' => 'Data Dashboard Merchant',
        'merchant_not_found' => 'Profil merchant tidak ditemukan. Silakan hubungi admin.',
    ],

    // Settings
    'settings' => [
        'profile_retrieved' => 'Profil berhasil diambil',
        'profile_updated' => 'Profil berhasil diperbarui',
        'password_changed' => 'Password berhasil diubah',
        'current_password_wrong' => 'Password saat ini salah',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Nama wajib diisi',
        'name_max' => 'Nama maksimal 255 karakter',
        'email_required' => 'Email wajib diisi',
        'email_email' => 'Format email tidak valid',
        'email_unique' => 'Email sudah digunakan',
        'email_regex' => 'Format email tidak valid',
        'email_max' => 'Email maksimal 255 karakter',
        'password_required' => 'Password wajib diisi',
        'password_min' => 'Password minimal 6 karakter',
        'password_max' => 'Password maksimal 255 karakter',
        'password_confirmed' => 'Konfirmasi password tidak cocok',
        'role_required' => 'Role wajib diisi',
        'role_in' => 'Role tidak valid',
        'user_id_required' => 'User ID wajib diisi',
        'user_id_exists' => 'User tidak ditemukan',
        'business_name_required' => 'Nama bisnis wajib diisi',
        'business_name_max' => 'Nama bisnis maksimal 255 karakter',
        'phone_max' => 'Phone maksimal 20 karakter',
    ],
];

