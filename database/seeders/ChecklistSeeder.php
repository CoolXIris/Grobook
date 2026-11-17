<?php

namespace Database\Seeders;

use App\Models\ChecklistItem;
use App\Models\ChecklistSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        if (ChecklistItem::exists()) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->sections() as $position => [$name, $items]) {
                $section = ChecklistSection::create(['name' => $name, 'position' => $position + 1]);

                foreach ($items as $itemPosition => [$number, $groupLabel, $content]) {
                    ChecklistItem::create([
                        'checklist_section_id' => $section->id,
                        'number' => $number,
                        'group_label' => $groupLabel,
                        'content' => $content,
                        'position' => $itemPosition + 1,
                        'is_active' => true,
                    ]);
                }
            }
        });
    }

    /**
     * @return array<int, array{0: string, 1: array<int, array{0: int, 1: ?string, 2: string}>}>
     */
    private function sections(): array
    {
        return [
            ['HALAMAN KOVER', [
                [1, 'KOVER DEPAN', "Nomor katalog diletakkan di kanan atas, nomornya harus sesuai dengan master katalog BPS. Format penulisan -> Katalog (tanpa 'BPS') : xxxxxxxx (nomor katalog).\nJika Publikasi 2 bahasa menggunakan penulisan : Katalog/Catalogue"],
                [2, 'KOVER DEPAN', "Pada halaman katalog:\na. Nomor publikasi apakah sama dengan yang ada di website?"],
                [3, 'KOVER DEPAN', "Penulisan nomor ISSN dibawah nomor katalog tanpa ada tanda titik dua atau tanda titik setelah tulisan ISSN.\nContoh: ISSN XXXX-XXXX"],
                [4, 'KOVER DEPAN', "Logo BPS harus sesuai perka dan tidak boleh dimodifikasi (Tidak boleh ditambahkan efek emboss, stroke, line, dll.)\nPenulisan Nama BPS Harus dalam dua baris jika publikasi menggunakan satu bahasa, dan tiga baris jika publikasi menggunakan dua bahasa. Hanya dapat disajikan menggunakan warna huruf biru (C=100, M=0, Y=0, K=0), hitam, atau putih dengan format huruf kapital dan font Arial Bold Italic. BPS-STATISTICS SUMATERA SELATAN PROVINCE"],
                [5, 'KOVER DEPAN', "Buku yang terbit secara rutin harus mencantumkan Edisi terbitan rutin. Edisi terbitan terdiri dari volume, nomor, dan tahun terbit. Volume menyatakan umur terbitan dan nomor menyatakan urutan terbit dalam satu tahun. Nomor hanya digunakan untuk publikasi yang terbit lebih dari sekali dalam satu tahun.\nVolume 1 dimulai saat terbitan pertama dan bertambah setiap tahun."],
                [6, 'KOVER DEPAN', "Jika Publikasi bukan hasil kerja sama, tidak boleh menggunakan logo lain seperti logo daerah, dll"],
                [7, 'KOVER DEPAN', "- Judul Harus terdapat Provinsi Sumatera Selatan\n- Jika periode publikasi ini bukan tahunan, seharusnya ada komponen 'Nomor' pada edisi terbitan publikasi --> \"Volume 4, Nomor xx, 2025\"."],
                [8, 'KOVER BELAKANG', "Logo Sensus BPS, BerAKHLAK dan #bangga melayani bangsa ditampilkan di pojok kanan atas pada kover belakang."],
                [9, 'KOVER BELAKANG', "Jenis huruf pada slogan \"Data Mencerdaskan Bangsa\" harus sesuai dengan template (warna boleh berubah). Logo dapat diunduh pada linktr.ee/pedomanpublikasi2023 >> Logo-Logo Publikasi."],
                [10, 'KOVER BELAKANG', "Logo BPS harus sesuai perka dan tidak boleh dimodifikasi (Tidak boleh ditambahkan efek emboss, stroke, line, dll.)\nPenulisan Nama BPS Harus dalam dua baris jika publikasi menggunakan satu bahasa, dan tiga baris jika publikasi menggunakan dua bahasa. Hanya dapat disajikan menggunakan warna huruf biru (C=100, M=0, Y=0, K=0), hitam, atau putih dengan format huruf kapital dan font Arial Bold Italic."],
                [11, 'KOVER BELAKANG', "Barcode diletakkan di bagian kanan bawah dan nomornya harus sesuai dengan ISSN/ISBN yang tertera pada kover depan dan halaman katalog"],
                [12, 'KOVER BELAKANG', "Penulisan Alamat BPS dan keterangan lainnya\nJalan Kapten Anwar Sastro No.1694 Palembang 30121\nTelepon: (0711) 351665\nEmail: bps1600@bps.go.id"],
            ]],
            ['HALAMAN PENDAHULUAN', [
                [13, 'HALAMAN JUDUL UTAMA', "- Tidak lagi menggunakan Kover Dalam berwarna hitam putih tetapi diganti dengan halaman judul utama. Terletak pada halaman ganjil.\n- Dibuat tanpa ilustrasi, tapi boleh dibuat berwarna.\n- Terhitung sebagai halaman i tapi tidak dicantumkan nomor halaman dan running title. Peletakan komponen sama seperti pada kover depan.\n- Susunan setelah Cover depan - Halaman Kosong ->\n- Tanpa Running Title"],
                [14, 'HALAMAN KATALOG', "Komponen Halaman Katalog terdiri dari Judul publikasi, Tahun Judul, Edisi Terbitan (jika memiliki ISSN), Penomoran publikasi (Nomor Katalog, ISSN/ISBN, Nomor Publikasi), Ukuran buku, Jumlah halaman, Penyusun Naskah, Penyunting, Pembuat Kover, Penerbit, Dicetak oleh, Sumber ilustrasi dan Pernyataan hak cipta."],
                [15, 'HALAMAN KATALOG', "Tanpa Running Title"],
                [16, 'HALAMAN KATALOG', "Ukuran buku\n- Apakah sama dengan website?\n- Apakah dengan aslinya?\n- Konsisten pada setiap halaman?"],
                [17, 'HALAMAN KATALOG', "Nomor (Tidak disingkat) Publikasi (tanpa spasi):"],
                [18, 'HALAMAN KATALOG', "Jumlah halaman romawi yang ditampilkan pada halaman katalog seharusnya genap, penomoran halaman romawi dimulai dari halaman judul utama. Terletak di balik halaman judul utama, tidak perlu ada spasi sebelum dan sesudah tanda \"+\"\nCek harus sama dengan Portal"],
                [19, 'HALAMAN KATALOG', "Penyusun Naskah, Penyunting = nama BPS provinsi/kabupaten/kota\nPembuat Kover = nama unit kerja eselon 2"],
                [20, 'HALAMAN KATALOG', "Pada bagian Diterbitkan oleh, lambang copyright © harus dicantumkan sebelum nama BPS (©(tanpa spasi)Badan Pusat Statistik Provinsi Sumatera Selatan)"],
                [21, 'HALAMAN KATALOG', "Bagian Dicetak oleh harus terisi dengan nama perusahaan percetakan. Jika Publikasi dicetak dalam, bagian dicetak oleh diisi dengan nama BPS. Jika publikasi tidak dicetak (hanya tersedia softcopy saja), maka pada bagian \"Dicetak oleh\" di Halaman Katalog dihapus."],
                [22, 'HALAMAN KATALOG', "Pernyataan hak cipta berbunyi : \"Dilarang mereproduksi dan/atau menggandakan sebagian atau seluruh isi buku ini untuk tujuan komersial tanpa izin tertulis dari [nama BPS penerbit]\""],
                [23, 'HALAMAN TIM PENYUSUN', "Terletak di halaman ganjil setelah halaman katalog."],
                [24, 'HALAMAN TIM PENYUSUN', "Komponen Halaman Tim Penyusun terdiri dari, Tulisan \"Tim Penyusun\" atau istilah lain dengan maksud yang sama, Judul publikasi, Tahun Judul, ISSN (jika ada) Diletakkan pada pojok kanan atas, Edisi Terbitan (jika ada ISSN) dan Nama anggota tim penyusun."],
                [25, 'HALAMAN TIM PENYUSUN', "Istilah yang digunakan untuk tim penyusun terdiri dari Pengarah, Penanggung jawab, Penyunting, Penulis naskah, Pengolah data, Penata letak, dan Penerjemah."],
                [26, 'HALAMAN TIM PENYUSUN', "Tidak ada nomor halaman"],
                [27, 'HALAMAN TIM PENYUSUN', "Harus nama individu"],
                [28, 'HALAMAN KATA PENGANTAR', "Kata pengantar harus berada di halaman ganjil.\nKomponen Halaman Kata Pengantar terdiri dari Tulisan \"Kata Pengantar\" atau istilah lain dengan maksud yang sama, Isi kata pengantar, Tempat, bulan, dan tahun kata pengantar ditandatangani, Jabatan penandatangan, Tanda tangan dan nama jelas pejabat penandatangan dan Nomor halaman."],
                [29, 'HALAMAN KATA PENGANTAR', "Kata pengantar harus ditandatangani oleh Kepala BPS Provinsi/Kabupaten/Kota atau pejabat pelaksana harian (plh).\nNIP pejabat penandatangan kata pengantar tidak boleh dicantumkan.\nGelar boleh dicantumkan konsisten dengan tim penyusun. Dibuat dalam format bold."],
                [30, 'HALAMAN KATA PENGANTAR', "Running Title tidak ada"],
                [31, 'HALAMAN DAFTAR ISI', "Halaman pertama terletak di halaman ganjil. Pencantuman judul bab dan subbab pada daftar isi maksimal adalah sampai tiga tingkat (bab - subbab - subsubbab)."],
                [32, 'HALAMAN DAFTAR ISI', "Komponen Halaman Daftar Isi terdiri dari Tulisan \"Daftar Isi\", Judul publikasi, Tahun Judul, ISSN (jika ada) di pojok kanan atas, Edisi Terbitan (jika ada ISSN) dan Nomor halaman."],
                [33, 'HALAMAN DAFTAR ISI', "Susunan Anatomi Buku harus sesuai:\n1. Halaman Kover\n- Kover Depan (wajib)\n- Punggung Buku (optional)\n- Kover Belakang (wajib)\n- Lidah Kover (optional)\n2. Halaman Pendahuluan\n- Halaman Judul Utama (wajib)\n- Halaman Katalog (wajib)\n- Halaman Tim Penyusun (wajib)\n- Halaman Sambutan (optional)\n- Halaman Kata Pengantar (wajib)\n- Halaman Abstraksi/Summary (optional)\n- Halaman Daftar Isi (wajib)\n- Halaman Daftar Tabel (optional)\n- Halaman Daftar Gambar (optional)\n- Halaman Daftar Lampiran (optional)\n- Halaman Penjelasan Umum (optional)\n- Halaman Penjelasan Teknis (optional)\n3. Halaman Isi\n4. Halaman Penutup\n- Daftar Pustaka (wajib)\n- Indeks (optional)\n- Daftar Istilah/Glosarium (optional)\n- Lampiran (optional)\nPenulisan subbab, hapus tanda titik setelah nomor terakhir."],
            ]],
            ['HALAMAN ISI', [
                [34, 'PEMBATAS BAB', "- Diletakkan di halaman ganjil.\n- Tetap dihitung sebagai halaman dan dibuat tanpa mencantumkan running title dan nomor halaman.\n- Seharusnya menampilkan sumber data"],
                [35, 'TABEL', "Nomor dan Judul Tabel\n- Judul tabel diletakkan di atas atau di samping tabel tanpa diakhiri dengan tanda titik setelah penomorannya\n- Huruf pertama setiap kata pada judul tabel ditulis dengan huruf kapital (Capitalize Each Word) kecuali kata sambung, kata penghubung, kata depan, dll.\n- Satuan diletakkan sebelum keterangan waktu (jika ada)\n- Pada judul gambar, tabel, dan lampiran, serta pada kolom dan stub tabel, satuan ditulis dengan huruf kecil kecuali satuan yang mengandung nama wilayah, dan simbol\n- Jika judul tabel memuat keterangan waktu, tambahkan tanda koma sebelum keterangan waktu (perhatikan urutan penulisan judul)\nContoh: Tabel 2.4 Realisasi Pendapatan Negara (miliar rupiah), 2020-2022\n- Satuan dalam kurung () huruf kecil\n- Antara judul kolom dan subjudul kolom pada tabel perlu diberikan garis horizontal diantaranya.\n- Tabel yang tidak cukup dicantumkan pada satu halaman maka pada setiap halaman lanjutannya perlu diberikan judul yang diawali dengan kata \"Lanjutan Tabel\" diikuti nomor tabel tanpa menampilkan judul lengkapnya.\n- Rentang waktu/nilai yang hanya menyajikan sebanyak dua rentang waktu/nilai maka dipisahkan dengan kata \"dan\".\n- Lebih dari dua rentang en dash tanpa spasi sebelum atau sesudah\n- Superscript pada tabel menggunakan angka/huruf bukan * (menyatakan angka sementara), tanpa tanda kurung"],
                [36, 'ISI TABEL', "- Isi tabel yang berupa angka harus rata kanan dan letaknya di tengah kolom\n- Angka negatif ditulis dengan tanda minus (-) dan tanpa spasi\n- Untuk nilai nol mutlak/tidak ada seharusnya disajikan dengan menggunakan tanda strip agak panjang atau en dash\n- Perlu diberikan nomor kolom"],
                [37, 'CATATAN DAN SUMBER', "- Catatan diletakkan di bawah tabel sebelum sumber dengan didahului kata \"Catatan:\" bukan keterangan\n- Ketentuan penulisan sumber data dari BPS: Badan Pusat Statistik, [nama sensus/survei/publikasi/dokumen/website]\nContoh: Badan Pusat Statistik, Survei Angkatan Kerja Nasional (Sakernas) Agustus"],
                [38, 'GAMBAR', "Ketentuan penulisan Nomor dan Judul Gambar sama seperti tabel.\nJudul gambar diletakkan di bawah atau di samping gambar tanpa diakhiri dengan tanda titik setelah penomorannya"],
            ]],
            ['HALAMAN PENUTUP', [
                [39, 'LAMPIRAN', "Ketentuan dalam pembuatan lampiran\n- Setiap lampiran harus diberikan judul pada bagian atas\n- Judul lampiran dimulai dengan kata \"Lampiran\" dan diikuti oleh nomor lampiran meskipun bentuknya tabel\n- Nomor lampiran menggunakan angka arab\n- Ketentuan penulisan judul dan nomor lampiran mengacu pada judul tabel\n- Harus ada running title"],
            ]],
            ['PENOMORAN HALAMAN DAN RUNNING TITLE', [
                [40, 'NOMOR HALAMAN', "Antara Kover Depan dan Halaman Judul Utama diberi 1 halaman kosong."],
                [41, 'NOMOR HALAMAN', "Halaman judul utama, halaman katalog, halaman tim penyusun, halaman pembatas bab, serta halaman kosong tidak perlu dicantumkan nomor halaman, namun tetap dihitung sebagai nomor halaman."],
                [42, 'NOMOR HALAMAN', "Penomoran halaman romawi dimulai dari kata pengantar, sedangkan halaman arab dimulai dari halaman isi"],
                [43, 'NOMOR HALAMAN', "Halaman pertama setiap bab harus bernomor ganjil."],
                [44, 'NOMOR HALAMAN', "Perlu ditambahkan halaman kosong sebelum kover belakang. Bila halaman isi terakhir berada pada halaman ganjil, maka ditambahkan 2 halaman kosong. Namun, jika berada pada halaman genap, maka ditambahkan 1 halaman kosong. Halaman kosong tersebut tidak dihitung sebagai halaman."],
                [45, 'RUNNING TITLE', "Running title dicantumkan mulai dari halaman isi sampai dengan halaman penutup (halaman arab)."],
                [46, 'RUNNING TITLE', "Running title pada halaman genap berisi judul buku, sedangkan pada halaman ganjil berisi judul bab."],
            ]],
            ['KETENTUAN BAHASA DAN TATA LETAK', [
                [47, null, "Halaman ISI - Bahasa Indonesia: Pemisah ribuan seharusnya menggunakan tanda titik sedangkan pemisah desimal menggunakan tanda koma\n- Bahasa Inggris: Pemisah ribuan seharusnya menggunakan tanda koma sedangkan pemisah desimal menggunakan tanda titik"],
                [48, null, "- Pada dual bahasa posisi paragraf harus sejajar\n- Widow/orphan paragraph diperhatikan"],
                [49, null, "- Cek semua kata rentang (contoh: 2023 - 2025) harus en-dash"],
                [50, null, "- Ukuran kertas konsisten di semua halaman, cek sama dengan halaman katalog"],
                [51, null, "Pada format dua kolom, baris pertama setiap paragraf pada narasi bahasa Indonesia dan narasi bahasa Inggris seharusnya diletakkan sejajar"],
                [52, null, "Cek margin harus sesuai"],
            ]],
            ['DAFTAR PUSTAKA', [
                [53, 'DAFTAR PUSTAKA', "- Running title.\n- Rincian pada daftar pustaka seharusnya disusun menurut abjad."],
                [54, 'DAFTAR PUSTAKA', "- Penulisan kata ulang murni pada judul seharusnya menggunakan capital each word, misal: \"Rata-Rata\"; \"Laki-Laki\"\n- Sesuai EYD, penulisan kata \"per\" di dalam judul seharusnya ditulis dengan huruf kecil dan terpisah dengan kata setelahnya\n- Penulisan \"non\" yang benar dalam bahasa Indonesia adalah dengan cara ditulis serangkai (tanpa spasi) dengan kata yang mengikutinya.\n- Bentuk yang baku adalah \"miliar\".\n- Perhatikan judul yang mengandung bulan"],
            ]],
            ['PEMANFAATAN ADOBE', [
                [55, null, "Penyusunan publikasi memanfaatkan aplikasi Adobe (Adobe Indesign, Adobe Illustrator, Adobe Acrobat, dll)"],
            ]],
        ];
    }
}