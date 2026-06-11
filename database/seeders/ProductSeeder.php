<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Kredit Pensiun',
            'description' => 'Produk pinjaman untuk pensiunan',
        ]);
        Product::create([
            'name' => 'Kredit Mikro',
            'description' => 'Produk pinjaman mikro untuk UMKM',
        ]);
    }
}
