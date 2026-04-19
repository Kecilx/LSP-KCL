<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->latest()->get();
        $categories = Category::all();

        return Inertia::render('Items/Index', [
            'items' => $items,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|min:3|max:255|unique:items,name',
            // Mengubah min:0 menjadi gt:0 agar tidak boleh 0
            // 'price' => 'required|numeric|gt:0',
            'price' => 'required|numeric',
            // 'stock' => 'required|integer|gt:0',
            'stock' => 'required|integer',
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid di database.',
            'name.required' => 'Nama item wajib diisi.',
            'name.string' => 'Nama item harus berupa teks.',
            'name.min' => 'Nama item minimal 3 karakter.',
            'name.max' => 'Nama item maksimal 255 karakter.',
            'name.unique' => 'Item dengan nama ini sudah terdaftar.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            // 'price.gt' => 'Harga harus lebih besar dari 0.', // Update pesan error
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            // 'stock.gt' => 'Stok harus lebih besar dari 0.', // Update pesan error
        ]);

        Item::create($validated);

        return redirect()->back()->with('success', 'Item berhasil ditambahkan!');
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|min:3|max:255|unique:items,name,' . $item->id,
            // Mengubah min:0 menjadi gt:0 agar tidak boleh 0
            // 'price' => 'required|numeric|gt:0',
            'price' => 'required|numeric',
            // 'stock' => 'required|integer|gt:0',
            'stock' => 'required|integer',
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid di database.',
            'name.required' => 'Nama item wajib diisi.',
            'name.string' => 'Nama item harus berupa teks.',
            'name.min' => 'Nama item minimal 3 karakter.',
            'name.max' => 'Nama item maksimal 255 karakter.',
            'name.unique' => 'Item dengan nama ini sudah terdaftar.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            // 'price.gt' => 'Harga harus lebih besar dari 0.', // Update pesan error
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            // 'stock.gt' => 'Stok harus lebih besar dari 0.', // Update pesan error
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', 'Item berhasil diperbarui!');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->back()->with('success', 'Item berhasil dihapus!');
    }
}