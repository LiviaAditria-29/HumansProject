<?php

declare(strict_types=1);

const APP_NAME = 'Sistem Data Karyawan';
const BASE_URL = '/humansproject';

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}