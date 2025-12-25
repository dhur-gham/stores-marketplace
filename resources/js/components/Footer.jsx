import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

export default function Footer() {
    const { t } = useTranslation();

    return (
        <footer className="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
            <div className="container mx-auto px-4 py-8">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {/* About Section */}
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {t('footer.about_title')}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            {t('footer.about_description')}
                        </p>
                        <Link
                            to="/about"
                            className="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors inline-flex items-center gap-1"
                        >
                            {t('footer.learn_more')}
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {t('footer.quick_links')}
                        </h3>
                        <ul className="space-y-2">
                            <li>
                                <Link
                                    to="/stores"
                                    className="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                >
                                    {t('footer.stores')}
                                </Link>
                            </li>
                            <li>
                                <Link
                                    to="/products"
                                    className="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                >
                                    {t('footer.products')}
                                </Link>
                            </li>
                            <li>
                                <Link
                                    to="/about"
                                    className="text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                >
                                    {t('footer.about')}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Contact/Info */}
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {t('footer.platform')}
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            {t('footer.tagline')}
                        </p>
                    </div>
                </div>

                {/* Copyright */}
                <div className="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700 text-center">
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                        © {new Date().getFullYear()} {t('header.title')}. {t('footer.rights_reserved')}
                    </p>
                </div>
            </div>
        </footer>
    );
}

