<?php

namespace App\Data;

class WishlistShareMessages
{
    /**
     * Get all Gen Z-style messages in English.
     *
     * @return array<int, string>
     */
    public static function get_english_messages(): array
    {
        return [
            'Share it with people who care for you, and take their money 💸',
            'If you care for me, let me steal your budget with this stuff 🛍️',
            'Drop hints to your loved ones (or just send them this link) 🎁',
            'Help me manifest these items into my life ✨',
            'Treat me like the main character I am 🎬',
            'My wishlist is a vibe, check it out 🎯',
            'This is my shopping list for people who love me 🥰',
            'Send this to someone who owes you a favor 😏',
            'My wishlist is giving main character energy ✨',
            'If you\'re reading this, you should probably buy me something 🛒',
            'This is not a drill - these are the things I actually want 🚨',
            'Share with your wallet and your heart 💝',
            'My wishlist is a whole mood, don\'t miss out 🎨',
            'Treat yourself by treating me (it\'s the same thing) 🎁',
            'This link contains my hopes, dreams, and shopping list 📝',
            'Send this to people who want to make me happy 😊',
            'My wishlist is fire, check it out 🔥',
            'If you love me, prove it with this list 💕',
            'This is my curated collection of things I need (want) 🛍️',
            'Share this with your bank account and watch the magic happen ✨',
        ];
    }

    /**
     * Get all Gen Z-style messages in Arabic.
     *
     * @return array<int, string>
     */
    public static function get_arabic_messages(): array
    {
        return [
            'شاركها مع الناس اللي يهتمون فيك، وخد فلوسهم 💸',
            'إذا كنت تهتم فيني، خليني أسرق ميزانيتك بهذه الأشياء 🛍️',
            'أرسل تلميحات لأحبابك (أو بس أرسلهم الرابط) 🎁',
            'ساعدوني أحقق هذه الأمنيات في حياتي ✨',
            'عاملوني كأني البطل الرئيسي 🎬',
            'قائمة أمنياتي هي المزاج، شوفوها 🎯',
            'هذه قائمة التسوق للناس اللي يحبوني 🥰',
            'أرسل هذا لشخص مديونك معروف 😏',
            'قائمة أمنياتي تعطي طاقة البطل الرئيسي ✨',
            'إذا كنت تقرأ هذا، المفروض تشتري لي شي 🛒',
            'هذا مش مزحة - هذه الأشياء اللي أنا فعلاً أريدها 🚨',
            'شارك مع محفظتك وقلبك 💝',
            'قائمة أمنياتي مزاج كامل، ما تفوتها 🎨',
            'عامل نفسك بمعاملتي (نفس الشي) 🎁',
            'هذا الرابط يحتوي على آمالي، أحلامي، وقائمة التسوق 📝',
            'أرسل هذا للناس اللي يبون يسعدوني 😊',
            'قائمة أمنياتي نار، شوفوها 🔥',
            'إذا كنت تحبني، أثبتها بهذه القائمة 💕',
            'هذه مجموعتي المختارة من الأشياء اللي أحتاجها (أريدها) 🛍️',
            'شارك هذا مع حسابك البنكي وشوف السحر يحصل ✨',
        ];
    }

    /**
     * Get a random message in the specified language.
     *
     * @param  string  $language  Language code ('en' or 'ar')
     */
    public static function get_random_message(string $language = 'en'): string
    {
        $messages = $language === 'ar' ? self::get_arabic_messages() : self::get_english_messages();

        return $messages[array_rand($messages)];
    }

    /**
     * Get all messages for the specified language.
     *
     * @param  string  $language  Language code ('en' or 'ar')
     * @return array<int, string>
     */
    public static function get_all_messages(string $language = 'en'): array
    {
        return $language === 'ar' ? self::get_arabic_messages() : self::get_english_messages();
    }
}
