import { useTranslation } from 'react-i18next';
import { MessageCircle, Mail, ArrowLeft, HelpCircle, Phone, ExternalLink } from 'lucide-react';
import { Link } from 'react-router-dom';
import Header from '../components/Header';
import ArrowIcon from '../components/ArrowIcon';

export default function Help() {
    const { t } = useTranslation();

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />

                <div className="max-w-4xl mx-auto mt-12">
                    <div className="mb-6">
                        <Link
                            to="/"
                            className="inline-flex items-center gap-2 px-3 py-2 min-h-[44px] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors touch-manipulation rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 -ml-3"
                        >
                            <ArrowIcon className="w-5 h-5 sm:w-4 sm:h-4" />
                            <span className="text-base sm:text-sm font-medium">{t('common.back')}</span>
                        </Link>
                    </div>

                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
                        <div className="p-6 md:p-8">
                            <div className="flex items-center gap-3 mb-6">
                                <div className="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                    <HelpCircle className="w-8 h-8 text-blue-600 dark:text-blue-400" />
                                </div>
                                <h1 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
                                    {t('help.title')}
                                </h1>
                            </div>

                            <p className="text-lg text-gray-600 dark:text-gray-400 mb-8">
                                {t('help.description')}
                            </p>

                            <div className="grid md:grid-cols-2 gap-6 mb-8">
                                {/* Telegram Support */}
                                <div className="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                            <MessageCircle className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                                            {t('help.telegram.title')}
                                        </h2>
                                    </div>
                                    <p className="text-gray-600 dark:text-gray-400 mb-4">
                                        {t('help.telegram.description')}
                                    </p>
                                    <a
                                        href="https://t.me/dhurgham"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                                    >
                                        <MessageCircle className="w-5 h-5" />
                                        <span>{t('help.telegram.button')}</span>
                                        <ExternalLink className="w-4 h-4" />
                                    </a>
                                </div>

                                {/* Email Support */}
                                <div className="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-6 hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                                            <Mail className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                                            {t('help.email.title')}
                                        </h2>
                                    </div>
                                    <p className="text-gray-600 dark:text-gray-400 mb-4">
                                        {t('help.email.description')}
                                    </p>
                                    <a
                                        href="mailto:hello@dhurgham.dev"
                                        className="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors"
                                    >
                                        <Mail className="w-5 h-5" />
                                        <span>hello@dhurgham.dev</span>
                                    </a>
                                </div>
                            </div>

                            {/* FAQ Section */}
                            <div className="border-t border-gray-200 dark:border-gray-700 pt-8">
                                <h2 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                                    {t('help.faq.title')}
                                </h2>
                                <div className="space-y-4">
                                    <div className="border-l-4 border-blue-500 pl-4 py-2">
                                        <h3 className="font-semibold text-gray-900 dark:text-white mb-2">
                                            {t('help.faq.how_to_order.question')}
                                        </h3>
                                        <p className="text-gray-600 dark:text-gray-400">
                                            {t('help.faq.how_to_order.answer')}
                                        </p>
                                    </div>
                                    <div className="border-l-4 border-blue-500 pl-4 py-2">
                                        <h3 className="font-semibold text-gray-900 dark:text-white mb-2">
                                            {t('help.faq.delivery.question')}
                                        </h3>
                                        <p className="text-gray-600 dark:text-gray-400">
                                            {t('help.faq.delivery.answer')}
                                        </p>
                                    </div>
                                    <div className="border-l-4 border-blue-500 pl-4 py-2">
                                        <h3 className="font-semibold text-gray-900 dark:text-white mb-2">
                                            {t('help.faq.payment.question')}
                                        </h3>
                                        <p className="text-gray-600 dark:text-gray-400">
                                            {t('help.faq.payment.answer')}
                                        </p>
                                    </div>
                                    <div className="border-l-4 border-blue-500 pl-4 py-2">
                                        <h3 className="font-semibold text-gray-900 dark:text-white mb-2">
                                            {t('help.faq.returns.question')}
                                        </h3>
                                        <p className="text-gray-600 dark:text-gray-400">
                                            {t('help.faq.returns.answer')}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

