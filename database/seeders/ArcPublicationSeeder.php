<?php

namespace Database\Seeders;

use App\Models\ArcPublication;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ArcPublicationSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTeamId = Team::first()?->id;

        $titles = [
            ['Provinsi Sumatera Selatan Dalam Angka 2026', 'Sumatera Selatan Province in Figures 2026'],
            ['Statistik Daerah Provinsi Sumatera Selatan 2026', 'Regional Statistics of Sumatera Selatan Province 2026'],
            ['Provinsi Sumatera Selatan Dalam Infografis 2026', 'Sumatera Selatan Province in Infographics 2026'],
            ['Statistik Kesejahteraan Rakyat Provinsi Sumatera Selatan 2026', 'Welfare Statistics of Sumatera Selatan Province 2026'],
            ['Statistik Pendidikan Provinsi Sumatera Selatan 2026', 'Education Statistics of Sumatera Selatan Province 2026'],
            ['Statistik Kesehatan Provinsi Sumatera Selatan 2026', 'Health Statistics of Sumatera Selatan Province 2026'],
            ['Statistik Tenaga Kerja Provinsi Sumatera Selatan 2026', 'Labor Force Statistics of Sumatera Selatan Province 2026'],
            ['Produk Domestik Regional Bruto Provinsi Sumatera Selatan Menurut Lapangan Usaha 2026', 'Gross Regional Domestic Product of Sumatera Selatan Province by Industry 2026'],
            ['Produk Domestik Regional Bruto Provinsi Sumatera Selatan Menurut Pengeluaran 2026', 'Gross Regional Domestic Product of Sumatera Selatan Province by Expenditure 2026'],
            ['Statistik Pertanian Tanaman Pangan Provinsi Sumatera Selatan 2026', 'Food Crop Agriculture Statistics of Sumatera Selatan Province 2026'],
        ];

        foreach ($titles as [$title, $titleEn]) {
            ArcPublication::firstOrCreate(
                ['title' => $title],
                ['title_en' => $titleEn, 'is_active' => true, 'team_id' => $defaultTeamId],
            );
        }
    }
}