<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Neutral starter content for a new school's public website.
 * Insert-only: never overwrites existing rows, so it is safe to re-run.
 * Teachers, testimonials, gallery and news are intentionally NOT seeded
 * (they must be real people and photos). Edit all text in Admin > Website CMS.
 */
class WebsiteDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ph = 'Replace this text with a short description of your school.';

        // [key, value, type, section]
        $settings = [
            ['hero_headline', 'Welcome to Our School', 'text', 'hero'],
            ['hero_subtext', 'Nurturing curious minds and building strong character.', 'text', 'hero'],
            ['hero_cta_primary', 'Admission', 'text', 'hero'],
            ['hero_cta_primary_link', '/admissions', 'text', 'hero'],
            ['hero_cta_secondary', 'Student Portal', 'text', 'hero'],
            ['hero_cta_secondary_link', '/student/login', 'text', 'hero'],
            ['hero_bg_color', '#0f766e', 'color', 'hero'],
            ['about_headline', 'About Our School', 'text', 'about'],
            ['about_subtext', $ph, 'text', 'about'],
            ['about_body', $ph, 'text', 'about'],
            ['about_mission', $ph, 'text', 'about'],
            ['about_vision', $ph, 'text', 'about'],
            ['programs_headline', 'Our Programs', 'text', 'programs'],
            ['programs_subtext', 'Learning stages offered at our school.', 'text', 'programs'],
            ['why_headline', 'Why Choose Us', 'text', 'why'],
            ['why_subtext', 'What makes our school special.', 'text', 'why'],
            ['teachers_headline', 'Meet Our Teachers', 'text', 'teachers'],
            ['teachers_subtext', '', 'text', 'teachers'],
            ['gallery_headline', 'Gallery', 'text', 'gallery'],
            ['gallery_subtext', '', 'text', 'gallery'],
            ['testimonials_headline', 'What Parents Say', 'text', 'testimonials'],
            ['testimonials_subtext', '', 'text', 'testimonials'],
            ['news_headline', 'News & Events', 'text', 'news'],
            ['news_subtext', '', 'text', 'news'],
            ['contact_headline', 'Get in Touch', 'text', 'contact'],
            ['contact_subtext', 'We would love to hear from you.', 'text', 'contact'],
            ['contact_address', '', 'text', 'contact'],
            ['contact_phone', '', 'text', 'contact'],
            ['contact_email', '', 'text', 'contact'],
            ['contact_map_embed', '', 'text', 'contact'],
            ['footer_tagline', '', 'text', 'contact'],
            ['footer_facebook', '', 'text', 'contact'],
            ['footer_twitter', '', 'text', 'contact'],
            ['footer_instagram', '', 'text', 'contact'],
            ['footer_whatsapp', '', 'text', 'contact'],
            ['hero_image', '', 'image', 'hero'],
            ['about_image', '', 'image', 'about'],
            ['admissions_open', '1', 'text', 'hero'],
            ['font_family', 'Nunito', 'text', 'global'],
            ['about_values', "1. Replace this text with your school's values.\n2. Add one value per line.", 'text', 'about'],
            ['about_stat1_number', '', 'text', 'about'],
            ['about_stat1_label', '', 'text', 'about'],
            ['about_stat2_number', '', 'text', 'about'],
            ['about_stat2_label', '', 'text', 'about'],
            ['about_stat3_number', '', 'text', 'about'],
            ['about_stat3_label', '', 'text', 'about'],
            ['about_stat4_number', '', 'text', 'about'],
            ['about_stat4_label', '', 'text', 'about'],
            ['primary_color', '#0f766e', 'text', 'global'],
            ['secondary_color', '#14b8a6', 'text', 'global'],
        ];

        foreach ($settings as [$key, $value, $type, $section]) {
            if (! DB::table('website_settings')->where('key', $key)->exists()) {
                DB::table('website_settings')->insert([
                    'key' => $key, 'value' => $value, 'type' => $type, 'section' => $section,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $items = [
            'faq' => [
                ['title' => 'How do I apply for admission?', 'description' => 'Visit the Admissions page or contact the school office for the current admission process.'],
                ['title' => 'What are the school fees?', 'description' => 'Please contact the school office for the current fee structure.'],
                ['title' => 'How can I reach the school?', 'description' => 'Use the contact details at the bottom of this page.'],
            ],
            'feature' => [
                ['icon' => '👩‍🏫', 'title' => 'Caring Teachers', 'description' => $ph],
                ['icon' => '📚', 'title' => 'Quality Learning', 'description' => $ph],
                ['icon' => '🏫', 'title' => 'Safe Environment', 'description' => $ph],
            ],
            'program' => [
                ['icon' => '🌱', 'title' => 'Nursery', 'description' => $ph],
                ['icon' => '📖', 'title' => 'Primary', 'description' => $ph],
                ['icon' => '🎓', 'title' => 'Junior High School', 'description' => $ph],
            ],
        ];

        foreach ($items as $type => $rows) {
            if (DB::table('website_items')->where('type', $type)->exists()) {
                continue;
            }
            foreach ($rows as $i => $row) {
                DB::table('website_items')->insert([
                    'type' => $type,
                    'title' => $row['title'],
                    'description' => $row['description'] ?? null,
                    'icon' => $row['icon'] ?? null,
                    'sort_order' => $i + 1,
                    'is_active' => 1,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }
}
