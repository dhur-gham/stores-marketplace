import { useEffect } from 'react';
import { useTranslation } from 'react-i18next';

export function useLanguage() {
    const { i18n } = useTranslation();

    useEffect(() => {
        const current_lang = i18n.language;
        const is_rtl = current_lang === 'ar';
        
        document.documentElement.lang = current_lang;
        document.documentElement.dir = is_rtl ? 'rtl' : 'ltr';
        
        document.body.classList.toggle('rtl', is_rtl);
        document.body.classList.toggle('ltr', !is_rtl);
    }, [i18n.language]);

    return {
        language: i18n.language,
        is_rtl: i18n.language === 'ar',
        change_language: i18n.changeLanguage,
    };
}

