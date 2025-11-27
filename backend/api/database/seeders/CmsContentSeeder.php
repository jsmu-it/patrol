<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CmsContent;

class CmsContentSeeder extends Seeder
{
    public function run()
    {
        $contents = [
            [
                'key' => 'home_hero_title',
                'title' => 'Professional Security Services',
                'subtitle' => null,
                'body' => null,
            ],
            [
                'key' => 'home_hero_subtitle',
                'title' => null,
                'subtitle' => 'Securing your assets with integrity, professionalism, and modern technology.',
                'body' => null,
            ],
            [
                'key' => 'home_hero_image',
                'title' => 'Hero Image',
                'subtitle' => null,
                'body' => null,
            ],
            [
                'key' => 'about_us',
                'title' => 'About Us',
                'subtitle' => null,
                'body' => '<p>JSMU Guard is a leading security service provider committed to excellence.</p>',
            ],
            [
                'key' => 'visi',
                'title' => 'Vision',
                'subtitle' => null,
                'body' => '<p>To become the most trusted security partner in the region.</p>',
            ],
            [
                'key' => 'misi',
                'title' => 'Mission',
                'subtitle' => null,
                'body' => '<ul><li>Provide professional guards</li><li>Utilize modern technology</li></ul>',
            ],
            [
                'key' => 'hsse',
                'title' => 'HSSE Policy',
                'subtitle' => null,
                'body' => '<p>We prioritize Health, Safety, Security, and Environment in all our operations.</p>',
            ],
            [
                'key' => 'archipelago',
                'title' => 'Archipelago Coverage',
                'subtitle' => null,
                'body' => '<p>Serving across the nation.</p>',
            ],
        ];

        foreach ($contents as $content) {
            CmsContent::firstOrCreate(['key' => $content['key']], $content);
        }
    }
}
