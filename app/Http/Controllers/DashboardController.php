<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Transaction;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil statistik untuk ditampilkan di Dashboard
        $totalCategories = Category::count();
        $totalItems = Item::count();
        $totalTransactions = Transaction::count();
        $incomeToday = Transaction::whereDate('date', today())->sum('total');

        return Inertia::render('Dashboard', [
            'stats' => [
                'categories' => $totalCategories,
                'items' => $totalItems,
                'transactions' => $totalTransactions,
                'income_today' => $incomeToday
            ]
        ]);
    }
}
