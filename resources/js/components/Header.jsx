import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import LanguageSwitcher from './LanguageSwitcher';

export default function Header() {
    const { t } = useTranslation();

    return (
        <header className="w-full py-8">
            <div className="container mx-auto px-4 flex items-center justify-between">
                <Link to="/" className="flex-1 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        {t('header.title')}
                    </h1>
                </Link>
                <div className="flex-shrink-0">
                    <LanguageSwitcher />
                </div>
            </div>
        </header>
    );
}

