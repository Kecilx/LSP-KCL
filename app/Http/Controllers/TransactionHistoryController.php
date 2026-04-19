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
        $transactions = Transaction::with('user')
            ->latest('date')
            ->get()
            ->map(function ($transaction) {
                /**
                 * Karena frontend (Vue) mencari 'paid' dan 'change', 
                 * kita buat properti tersebut secara manual di sini 
                 * tanpa mengubah struktur database.
                 */
                $transaction->paid = $transaction->pay_total;
                $transaction->change = $transaction->pay_total - $transaction->total;
                return $transaction;
            });

        return Inertia::render('History/Index', [
            'transactions' => $transactions
        ]);
    }

    /**
     * Menampilkan detail transaksi spesifik
     * Digunakan untuk modal detail atau cetak ulang struk
     */
    public function show(Transaction $transaction)
    {
        // Load relasi detail (termasuk item) dan kasir
        $transaction->load(['details.item', 'user']);

        // Tambahkan kalkulasi untuk detail tunggal
        $transaction->paid = $transaction->pay_total;
        $transaction->change = $transaction->pay_total - $transaction->total;

        // Jika request meminta JSON (untuk modal popup)
        if (request()->wantsJson()) {
            return response()->json([
                'transaction' => $transaction
            ]);
        }

        // Jika ingin render ke halaman terpisah
        return Inertia::render('History/Show', [
            'transaction' => $transaction
        ]);
    }
}