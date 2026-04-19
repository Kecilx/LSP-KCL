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
        $items = Item::with('category')->where('stock', '>', 0)->latest()->get();

        return Inertia::render('Transactions/Index', [
            'items' => $items,
            'cashierName' => Auth::user()->name 
        ]);
    }

    /**
     * Memproses transaksi (Checkout)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input dari Vue
        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:items,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|gte:total_amount',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                
                // 2. Simpan Data Utama ke tabel `transactions`
                // Sesuai Model: pay_total (bukan paid), change dihapus jika tidak ada di ERD
                $transaction = Transaction::create([
                    'user_id'   => Auth::id(),
                    'date'      => now(),
                    'total'     => $validated['total_amount'],
                    'pay_total' => $validated['paid_amount'], 
                ]);

                // 3. Proses Detail Transaksi dan Kurangi Stok Item
                foreach ($validated['cart'] as $cartItem) {
                    $item = Item::lockForUpdate()->find($cartItem['id']);

                    if ($item->stock < $cartItem['quantity']) {
                        throw new \Exception("Stok item '{$item->name}' tidak mencukupi.");
                    }

                    // Kurangi stok
                    $item->decrement('stock', $cartItem['quantity']);

                    // Simpan detail transaksi
                    // Sesuai Model: qty (bukan quantity), price dihapus jika tidak ada di ERD
                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'item_id'        => $item->id,
                        'qty'            => $cartItem['quantity'],
                        'subtotal'       => $cartItem['subtotal'],
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Transaksi berhasil disimpan!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }
}