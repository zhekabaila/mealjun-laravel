<?php

namespace Database\Seeders;

use App\Models\CaptionTemplate;
use Illuminate\Database\Seeder;

class CaptionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CaptionTemplate::create([
            'tone' => 'friendly',
            'template_text' => "😍 Hei teman-teman! Kenalin nih produk favorit kita: _{name}_ rasa {flavor}!\n\nGak bakal nyesel deh nyobain. Harganya cuma {price} aja loh! 🎉\n\n{description}\n\n#Mealjun #Yummy #{flavor}",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        CaptionTemplate::create([
            'tone' => 'professional',
            'template_text' => "Kami dengan bangga mempersembahkan _{name}_ — varian {flavor} dari Mealjun.\n\nDibuat dengan bahan pilihan berkualitas tinggi.\n\n{description}\n\nHarga: {price}\n\nPesan sekarang melalui tautan di bio.",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        CaptionTemplate::create([
            'tone' => 'playful',
            'template_text' => "🎊 STOP SCROLLING! 🛑\n\nKamu belum coba {name} rasa {flavor}?!\nKami serius ini wajib masuk bucket list snack kamu! 🤤\n\n{description}\n\nCuman {price} bro! Buruan sebelum kehabisan! 🔥\n\n#Mealjun #MustTry",
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
