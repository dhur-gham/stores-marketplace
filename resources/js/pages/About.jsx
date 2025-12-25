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

                {/* CTA Section */}
                <section className="text-center">
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 md:p-12">
                        <Globe className="w-16 h-16 md:w-20 md:h-20 text-blue-600 dark:text-blue-400 mx-auto mb-6" />
                        <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            {t('about.cta.title')}
                        </h2>
                        <p className="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                            {t('about.cta.description')}
                        </p>
                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <a
                                href="/stores"
                                className="px-6 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors"
                            >
                                {t('about.cta.browse_stores')}
                            </a>
                            <a
                                href="/products"
                                className="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg transition-colors"
                            >
                                {t('about.cta.explore_products')}
                            </a>
                        </div>
                    </div>
                </section>
            </main>

            <Footer />
        </div>
    );
}

