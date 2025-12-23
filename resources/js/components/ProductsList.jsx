import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ArrowRight, Sparkles } from 'lucide-react';
import ProductCard from './ProductCard';

export default function ProductsList({ products, loading, limit = null, showViewAll = false }) {
    const { t } = useTranslation();
    
    if (loading) {
        const skeleton_count = limit || 6;
        return (
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-4">
                {[...Array(skeleton_count)].map((_, i) => (
                    <div
                        key={i}
                        className="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700/50 animate-pulse"
                    >
                        <div className="aspect-square w-full bg-gray-200 dark:bg-gray-700" />
                        <div className="p-3">
                            <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-16 mb-1.5" />
                            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-1.5" />
                            <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-20" />
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    if (!products || products.length === 0) {
        return (
            <div className="text-center py-12 text-gray-500 dark:text-gray-400">
                <p className="text-lg">{t('products.no_products')}</p>
            </div>
        );
    }

    const display_products = limit ? products.slice(0, limit) : products;

    return (
        <div>
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-4">
                {display_products.map((product) => (
                    <ProductCard key={product.id} product={product} />
                ))}
            </div>
            {showViewAll && (
                <div className="mt-6 text-center">
                    <Link
                        to="/products"
                        className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                    >
                        <Sparkles className="w-5 h-5" />
                        <span>{t('products.explore_all_products')}</span>
                        <ArrowRight className="w-5 h-5" />
                    </Link>
                </div>
            )}
        </div>
    );
}

