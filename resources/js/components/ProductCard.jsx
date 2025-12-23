import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ShoppingCart, Check } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';

export default function ProductCard({ product }) {
    const { t } = useTranslation();
    const { authenticated } = useAuth();
    const { addToCart } = useCart();
    const [adding, setAdding] = useState(false);
    const [added, setAdded] = useState(false);
    
    const formatPrice = (price) => {
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(price);
        return `${formatted} IQD`;
    };

    const isLowStock = product.stock !== undefined && product.stock > 0 && product.stock < 5;
    const isOutOfStock = product.stock === 0 || product.stock === undefined;

    const handleAddToCart = async (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        if (isOutOfStock || adding) {
            return;
        }

        setAdding(true);
        const result = await addToCart(product.id, 1);
        setAdding(false);
        
        if (result.success) {
            setAdded(true);
            setTimeout(() => setAdded(false), 2000);
        }
    };

    return (
        <Link
            to={`/product/${product.id}`}
            className="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700/50 block group"
        >
            <div className="aspect-square w-full bg-gray-50 dark:bg-gray-700/50 relative overflow-hidden">
                {product.image ? (
                    <img
                        src={product.image}
                        alt={product.name}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-600">
                        <svg
                            className="w-12 h-12"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                )}
                {isLowStock && (
                    <div className="absolute top-2 start-2">
                        <span className="text-[10px] font-semibold bg-orange-500 text-white px-2 py-1 rounded-full shadow-sm">
                            {t('product.low_stock')}
                        </span>
                    </div>
                )}
            </div>
            <div className="p-3">
                {product.store && (
                    <div className="mb-1.5">
                        <span className="inline-block px-1.5 py-0.5 text-[10px] font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded">
                            {product.store.name}
                        </span>
                    </div>
                )}
                <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-1.5 line-clamp-2 leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {product.name}
                </h3>
                <div className="flex items-center justify-between gap-2">
                    <span className="text-base font-bold text-gray-900 dark:text-white">
                        {formatPrice(product.price)}
                    </span>
                    {authenticated && !isOutOfStock && (
                        <button
                            onClick={handleAddToCart}
                            disabled={adding || added}
                            className="p-2 rounded-lg bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                            title={added ? t('cart.item_added') : t('cart.add_to_cart')}
                        >
                            {added ? (
                                <Check className="w-4 h-4" />
                            ) : (
                                <ShoppingCart className="w-4 h-4" />
                            )}
                        </button>
                    )}
                    {isOutOfStock && (
                        <span className="text-xs text-red-600 dark:text-red-400 font-medium">
                            {t('cart.out_of_stock')}
                        </span>
                    )}
                </div>
            </div>
        </Link>
    );
}
