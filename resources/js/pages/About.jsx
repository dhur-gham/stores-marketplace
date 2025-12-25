import { useTranslation } from 'react-i18next';
import Header from '../components/Header';
import Footer from '../components/Footer';
import { Store, ShoppingBag, Users, TrendingUp, Shield, Zap, Globe, Package } from 'lucide-react';

export default function About() {
    const { t } = useTranslation();

    const features = [
        {
            icon: Store,
            title: t('about.features.store_management.title'),
            description: t('about.features.store_management.description'),
        },
        {
            icon: ShoppingBag,
            title: t('about.features.product_catalog.title'),
            description: t('about.features.product_catalog.description'),
        },
        {
            icon: Users,
            title: t('about.features.customer_base.title'),
            description: t('about.features.customer_base.description'),
        },
        {
            icon: TrendingUp,
            title: t('about.features.analytics.title'),
            description: t('about.features.analytics.description'),
        },
        {
            icon: Shield,
            title: t('about.features.secure.title'),
            description: t('about.features.secure.description'),
        },
        {
            icon: Zap,
            title: t('about.features.real_time.title'),
            description: t('about.features.real_time.description'),
        },
    ];

    const storeOwnerFeatures = [
        t('about.store_owner.create_store'),
        t('about.store_owner.add_products'),
        t('about.store_owner.manage_inventory'),
        t('about.store_owner.track_orders'),
        t('about.store_owner.view_analytics'),
        t('about.store_owner.set_delivery_prices'),
    ];

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
            <Header />
            
            <main className="flex-1 container mx-auto px-4 py-8 md:py-12">
                {/* Hero Section */}
                <div className="text-center mb-12 md:mb-16">
                    <h1 className="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                        {t('about.hero.title')}
                    </h1>
                    <p className="text-lg md:text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                        {t('about.hero.subtitle')}
                    </p>
                </div>

                {/* What is This Platform */}
                <section className="mb-16">
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 md:p-8 lg:p-12">
                        <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">
                            {t('about.what_is.title')}
                        </h2>
                        <div className="prose prose-lg dark:prose-invert max-w-none">
                            <p className="text-gray-600 dark:text-gray-400 mb-4 text-base md:text-lg leading-relaxed">
                                {t('about.what_is.description')}
                            </p>
                            <p className="text-gray-600 dark:text-gray-400 text-base md:text-lg leading-relaxed">
                                {t('about.what_is.description2')}
                            </p>
                        </div>
                    </div>
                </section>

                {/* Features Grid */}
                <section className="mb-16">
                    <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">
                        {t('about.features.title')}
                    </h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                        {features.map((feature, index) => {
                            const Icon = feature.icon;
                            return (
                                <div
                                    key={index}
                                    className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow"
                                >
                                    <div className="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
                                        <Icon className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        {feature.title}
                                    </h3>
                                    <p className="text-gray-600 dark:text-gray-400 text-sm md:text-base">
                                        {feature.description}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </section>

                {/* What Store Owners Can Do */}
                <section className="mb-16">
                    <div className="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl shadow-lg p-6 md:p-8 lg:p-12">
                        <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">
                            {t('about.store_owner.title')}
                        </h2>
                        <p className="text-gray-600 dark:text-gray-400 mb-8 text-base md:text-lg">
                            {t('about.store_owner.intro')}
                        </p>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {storeOwnerFeatures.map((feature, index) => (
                                <div
                                    key={index}
                                    className="flex items-start gap-3 bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm"
                                >
                                    <div className="w-6 h-6 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg
                                            className="w-4 h-4 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={2}
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </div>
                                    <p className="text-gray-700 dark:text-gray-300 font-medium">{feature}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Merchant CTA Section */}
                <section className="mb-16">
                    <div className="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl shadow-lg p-8 md:p-12">
                        <div className="text-center mb-8">
                            <Store className="w-16 h-16 md:w-20 md:h-20 text-green-600 dark:text-green-400 mx-auto mb-4" />
                            <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                                {t('about.merchant.title')}
                            </h2>
                            <p className="text-lg text-gray-600 dark:text-gray-400 mb-6 max-w-3xl mx-auto">
                                {t('about.merchant.description')}
                            </p>
                            <a
                                href="https://t.me/me_dhurgham"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white font-medium rounded-lg transition-colors text-base md:text-lg"
                            >
                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                                {t('about.merchant.contact_button')}
                            </a>
                        </div>
                        <div className="mt-8 pt-8 border-t border-green-200 dark:border-green-800">
                            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-4 text-center">
                                {t('about.merchant.benefits_title')}
                            </h3>
                            <ul className="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl mx-auto">
                                <li className="flex items-start gap-3">
                                    <svg className="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">{t('about.merchant.benefit1')}</span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <svg className="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">{t('about.merchant.benefit2')}</span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <svg className="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">{t('about.merchant.benefit3')}</span>
                                </li>
                                <li className="flex items-start gap-3">
                                    <svg className="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">{t('about.merchant.benefit4')}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </main>

            <Footer />
        </div>
    );
}

