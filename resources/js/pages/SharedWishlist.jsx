import { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Heart, ShoppingCart, ArrowLeft } from 'lucide-react';
import Header from '../components/Header';
import { getSharedWishlist } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';

export default function SharedWishlist() {
    const { t } = useTranslation();
    const { token } = useParams();
    const navigate = useNavigate();
    const { authenticated } = useAuth();
    const { addToCart } = useCart();
    const [loading, setLoading] = useState(true);
    const [shared_data, setSharedData] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (token) {
            loadSharedWishlist();
        }
    }, [token]);

    const loadSharedWishlist = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await getSharedWishlist(token);
            if (response.status && response.data) {
                setSharedData(response.data);
            } else {
                setError('not_found');
            }
        } catch (error) {
            console.error('Error loading shared wishlist:', error);
            setError('not_found');
        } finally {
            setLoading(false);
        }
    };

    const formatPrice = (price) => {
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(price);
        return `${formatted} IQD`;
    };

    const handleAddToCart = async (product_id) => {
        if (!authenticated) {
            navigate('/login');
            return;
        }
        await addToCart(product_id, 1);
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-6xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-8 animate-pulse">
                            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-6" />
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {[...Array(6)].map((_, i) => (
                                    <div key={i} className="h-64 bg-gray-200 dark:bg-gray-700 rounded" />
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    if (error === 'not_found' || !shared_data) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-4xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                            <div className="text-6xl mb-4">😕</div>
                            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {t('wishlist.shared.not_found')}
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400 mb-6">
                                {t('wishlist.shared.not_found_description')}
                            </p>
                            <Link
                                to="/"
                                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                            >
                                {t('wishlist.shared.back_home')}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    const { share, customer, wishlist_items } = shared_data;
    // Use the saved custom_message from the share, which includes selected messages
    const display_message = share.custom_message || null;

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                <div className="max-w-6xl mx-auto mt-12">
                    <div className="mb-6">
                        <Link
                            to="/"
                            className="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                        >
                            <ArrowLeft className="w-4 h-4" />
                            <span>{t('common.back')}</span>
                        </Link>
                    </div>

                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                        <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div className="flex items-center gap-3 mb-4">
                                <Heart className="w-8 h-8 fill-red-500 text-red-500" />
                                <div>
                                    <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                                        {t('wishlist.shared.title')}
                                    </h1>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {t('wishlist.shared.by')} {customer.name}
                                    </p>
                                </div>
                            </div>
                            {display_message ? (
                                <div className="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {t('wishlist.shared.message')}
                                    </p>
                                    <p className="text-gray-900 dark:text-white">{display_message}</p>
                                </div>
                            ) : null}
                        </div>
                    </div>

                    {wishlist_items.length === 0 ? (
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                            <div className="text-6xl mb-4">❤️</div>
                            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {t('wishlist.shared.empty')}
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400">
                                {t('wishlist.shared.empty_description')}
                            </p>
                        </div>
                    ) : (
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {wishlist_items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="bg-gray-50 dark:bg-gray-700/50 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow"
                                    >
                                        <Link
                                            to={`/product/${item.product.id}`}
                                            className="block aspect-square w-full bg-gray-100 dark:bg-gray-700 relative overflow-hidden"
                                        >
                                            {item.product.image ? (
                                                <img
                                                    src={item.product.image}
                                                    alt={item.product.name}
                                                    className="w-full h-full object-cover"
                                                />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center text-gray-400">
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
                                        </Link>
                                        <div className="p-4">
                                            <Link
                                                to={`/product/${item.product.id}`}
                                                className="block mb-2"
                                            >
                                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-1 line-clamp-2 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                                    {item.product.name}
                                                </h3>
                                                {item.product.store && (
                                                    <p className="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                                        {item.product.store.name}
                                                    </p>
                                                )}
                                                <p className="text-lg font-bold text-gray-900 dark:text-white">
                                                    {formatPrice(item.product.price)}
                                                </p>
                                            </Link>
                                            <div className="mt-4">
                                                {item.product.stock > 0 ? (
                                                    <button
                                                        onClick={() => handleAddToCart(item.product.id)}
                                                        className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors"
                                                    >
                                                        <ShoppingCart className="w-4 h-4" />
                                                        {authenticated
                                                            ? t('wishlist.shared.add_to_cart')
                                                            : t('wishlist.shared.login_to_add')}
                                                    </button>
                                                ) : (
                                                    <span className="w-full text-center text-sm text-red-600 dark:text-red-400 font-medium block py-2">
                                                        {t('product.out_of_stock')}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

