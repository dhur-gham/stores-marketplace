import { useTranslation } from 'react-i18next';
import ProductCard from './ProductCard';

export default function ProductsList({ products, loading }) {
    const { t } = useTranslation();
    if (loading) {
        return (
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-4">
                {[...Array(6)].map((_, i) => (
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

    return (
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-4">
            {products.map((product) => (
                <ProductCard key={product.id} product={product} />
            ))}
        </div>
    );
}

