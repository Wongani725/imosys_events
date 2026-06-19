<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttireSponsorSeeder extends Seeder
{
    public function run()
    {
        // Attire sizes for both events
        $sizes = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
        foreach (['IIA-GF-2026', 'IIA-AC-2026'] as $eventId) {
            foreach ($sizes as $size) {
                DB::table('attire_sizes')->updateOrInsert(
                    ['name' => $size, 'event_id' => $eventId],
                    ['name' => $size, 'event_id' => $eventId]
                );
            }
        }

        // Sponsors
        $sponsors = [
            ['name' => 'National Bank of Malawi', 'event_id' => 'IIA-GF-2026', 'file_path' => 'images/placeholder.png'],
            ['name' => 'Malawi Telecommunications Ltd', 'event_id' => 'IIA-GF-2026', 'file_path' => 'images/placeholder.png'],
            ['name' => 'Sunbird Hotels & Resorts', 'event_id' => 'IIA-GF-2026', 'file_path' => 'images/placeholder.png'],
            ['name' => 'Standard Bank Malawi', 'event_id' => 'IIA-AC-2026', 'file_path' => 'images/placeholder.png'],
            ['name' => 'Airtel Malawi', 'event_id' => 'IIA-AC-2026', 'file_path' => 'images/placeholder.png'],
            ['name' => 'TNM', 'event_id' => 'IIA-AC-2026', 'file_path' => 'images/placeholder.png'],
            ['name' => 'Press Corporation Limited', 'event_id' => 'IIA-AC-2026', 'file_path' => 'images/placeholder.png'],
        ];

        foreach ($sponsors as $sponsor) {
            DB::table('sponsor_ads')->updateOrInsert(
                ['name' => $sponsor['name'], 'event_id' => $sponsor['event_id']],
                $sponsor
            );
        }

        $this->createPlaceholderImage();
    }

    private function createPlaceholderImage()
    {
        $dir = public_path('images');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $path = $dir . '/placeholder.png';
        if (!file_exists($path)) {
            $img = imagecreatetruecolor(400, 200);
            $bg = imagecolorallocate($img, 0, 97, 152);
            $text = imagecolorallocate($img, 255, 255, 255);
            imagefill($img, 0, 0, $bg);
            imagestring($img, 5, 120, 90, 'Sponsor Logo', $text);
            imagepng($img, $path);
            imagedestroy($img);
        }
    }
}
