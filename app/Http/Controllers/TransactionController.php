<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Menampilkan halaman Transaksi (Point of Sales)
     */
    public function index()
    {
        // Ambil data item yang stoknya lebih dari 0
    }

    /**
     * Memproses transaksi (Checkout)
     */
    public function store(Request $request)
    {
        
    }
}
