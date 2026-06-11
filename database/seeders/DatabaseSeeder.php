<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);

        // Seed account groups
        $this->call(AccountGroupSeeder::class);
        // Seed accounts
        $this->call(AccountSeeder::class);
        // Seed akun tambahan channeling/back-to-back loan
        $this->call(ChannelingAccountSeeder::class);
        // Seed master pendana
        $this->call(LenderSeeder::class);
        // Seed contoh pinjaman channeling
        $this->call(LoanSeeder::class);
        // Seed contoh jurnal channeling
        $this->call(ChannelingJournalSeeder::class);
        // Seed template produk PLATINUM-MANTAP-Sendiri
        $this->call(PlatinumMantapSendiriTemplateSeeder::class);
        // Seed referensi dropdown simulasi KB (bank tujuan & area)
        $this->call(KbReferenceOptionSeeder::class);
        // Seed master product_struct untuk simulasi KB
        $this->call(ProductStructSeeder::class);
        // Seed products
        $this->call(ProductSeeder::class);
        // Seed konfigurasi dynamic product engine
        $this->call(ProductEngineSeeder::class);
        // Seed product templates
        $this->call(ProductTemplateSeeder::class);
        // Seed contoh hasil OCR simulasi
        $this->call(SimulationFieldSeeder::class);
    }
}
