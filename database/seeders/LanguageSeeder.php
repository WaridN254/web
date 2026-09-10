<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LanguageSeeder extends Seeder
{
    protected array $languages = [
        // Global languages
        ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'locale' => 'en', 'direction' => 'ltr', 'is_default' => true],
        ['code' => 'fr', 'name' => 'French', 'native_name' => 'Francais', 'locale' => 'fr', 'direction' => 'ltr'],
        ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Espanol', 'locale' => 'es', 'direction' => 'ltr'],
        ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Portugues', 'locale' => 'pt', 'direction' => 'ltr'],
        ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'locale' => 'de', 'direction' => 'ltr'],
        ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'locale' => 'it', 'direction' => 'ltr'],
        ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'locale' => 'nl', 'direction' => 'ltr'],
        ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'locale' => 'ar', 'direction' => 'rtl'],
        ['code' => 'zh', 'name' => 'Chinese Simplified', 'native_name' => '简体中文', 'locale' => 'zh-CN', 'direction' => 'ltr'],
        ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'locale' => 'ja', 'direction' => 'ltr'],
        ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'locale' => 'ko', 'direction' => 'ltr'],
        ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'locale' => 'ru', 'direction' => 'ltr'],
        ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'locale' => 'hi', 'direction' => 'ltr'],
        ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Turkce', 'locale' => 'tr', 'direction' => 'ltr'],

        // African languages
        ['code' => 'sw', 'name' => 'Swahili', 'native_name' => 'Kiswahili', 'locale' => 'sw', 'direction' => 'ltr'],
        ['code' => 'lg', 'name' => 'Luganda', 'native_name' => 'Luganda', 'locale' => 'lg', 'direction' => 'ltr'],
        ['code' => 'zu', 'name' => 'Zulu', 'native_name' => 'isiZulu', 'locale' => 'zu', 'direction' => 'ltr'],
        ['code' => 'am', 'name' => 'Amharic', 'native_name' => 'አማርኛ', 'locale' => 'am', 'direction' => 'ltr'],
        ['code' => 'ha', 'name' => 'Hausa', 'native_name' => 'Hausa', 'locale' => 'ha', 'direction' => 'ltr'],
        ['code' => 'yo', 'name' => 'Yoruba', 'native_name' => 'Yoruba', 'locale' => 'yo', 'direction' => 'ltr'],
        ['code' => 'ig', 'name' => 'Igbo', 'native_name' => 'Igbo', 'locale' => 'ig', 'direction' => 'ltr'],
    ];

    public function run(): void
    {
        foreach ($this->languages as $lang) {
            Language::updateOrCreate(
                ['code' => $lang['code']],
                [
                    'name' => $lang['name'],
                    'native_name' => $lang['native_name'],
                    'locale' => $lang['locale'],
                    'direction' => $lang['direction'],
                    'is_active' => true,
                    'is_default' => $lang['is_default'] ?? false,
                ]
            );
        }
    }
}
