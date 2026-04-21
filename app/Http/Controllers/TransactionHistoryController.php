<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    /**
     * Menampilkan daftar riwayat transaksi
     */
    public function index()
    {
        // Mengambil semua transaksi beserta nama user (kasir)
    }

    /**
     * Menampilkan detail transaksi spesifik
     * Digunakan untuk modal detail atau cetak ulang struk
     */
    public function show(Transaction $transaction)
    {
        
    }
}
