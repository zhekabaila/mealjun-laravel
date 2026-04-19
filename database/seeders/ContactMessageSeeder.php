<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            [
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@email.com',
                'message' => 'Halo Mealjun! Saya tertarik untuk menjadi reseller produk Mealjun. Bagaimana caranya? Apakah ada syarat dan ketentuan khusus? Mohon informasinya.',
                'is_read' => true,
                'replied_at' => now(),
                'reply_message' => 'Terima kasih atas minat Anda! Kami sangat senang Anda ingin menjadi reseller. Mohon hubungi kami melalui WhatsApp untuk diskusi lebih lanjut mengenai persyaratan dan benefit menjadi reseller.',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nur@email.com',
                'message' => 'Apakah bisa custom cake untuk acara pernikahan saya? Budget berapa ya untuk cake ukuran besar untuk 100 orang?',
                'is_read' => true,
                'replied_at' => now(),
                'reply_message' => 'Kami bisa custom cake sesuai keinginan Anda! Untuk harga dan detail lebih lanjut, silakan hubungi tim kami via WhatsApp. Kami siap membantu mewujudkan acara spesial Anda.',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'message' => 'Berapa lama estimasi pengiriman ke Bandung? Apakah ada biaya pengiriman?',
                'is_read' => true,
                'replied_at' => now(),
                'reply_message' => 'Estimasi pengiriman ke Bandung adalah 1-2 hari kerja. Biaya pengiriman akan dihitung sesuai dengan berat produk dan jarak pengiriman. Hubungi kami untuk detail lebih lanjut.',
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'rina.wijaya@email.com',
                'message' => 'Apakah ada diskon untuk pembelian dalam jumlah besar? Saya ingin pesan 50 brownies untuk acara kantor saya.',
                'is_read' => false,
                'replied_at' => null,
                'reply_message' => null,
            ],
            [
                'name' => 'Dewi Putri',
                'email' => 'dewi.putri@email.com',
                'message' => 'Apakah produk Mealjun bisa untuk menu katering? Berapa harga per porsi?',
                'is_read' => false,
                'replied_at' => null,
                'reply_message' => null,
            ],
            [
                'name' => 'Roni Saputra',
                'email' => 'roni.saputra@email.com',
                'message' => 'Produk Anda sangat enak! Apakah bisa melayani subscription bulanan?',
                'is_read' => true,
                'replied_at' => now(),
                'reply_message' => 'Terima kasih atas apresiasi Anda! Kami sedang mempertimbangkan layanan subscription. Untuk informasi lebih lanjut, silakan hubungi kami via WhatsApp.',
            ],
            [
                'name' => 'Nurul Aini',
                'email' => 'nurul.aini@email.com',
                'message' => 'Apakah ada opsi pembayaran cicilan untuk pembelian kue custom?',
                'is_read' => false,
                'replied_at' => null,
                'reply_message' => null,
            ],
        ];

        foreach ($messages as $message) {
            ContactMessage::create(array_merge($message, [
                'created_at' => now(),
            ]));
        }
    }
}
