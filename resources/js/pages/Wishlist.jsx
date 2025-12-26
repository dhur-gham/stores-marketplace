import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Heart, Trash2, ArrowLeft, ShoppingCart, Share2 } from 'lucide-react';
import Header from '../components/Header';
import ShareWishlistModal from '../components/ShareWishlistModal';
import ConfirmationModal from '../components/ConfirmationModal';
import { useWishlist } from '../contexts/WishlistContext';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../contexts/AuthContext';

export default function Wishlist() {
    const { t } = useTranslation();
    const { authenticated } = useAuth();
    const { wishlist_items, loading, removeFromWishlist } = useWishlist();
    const { addToCart } = useCart();
    const [show_share_modal, setShowShareModal] = useState(false);
    const [confirm_modal, setConfirmModal] = useState({
        is_open: false,
        wishlist_item_id: null,
    });

    const formatPrice = (price) => {
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(price);
        return `${formatted} IQD`;
    };

    const handleRemove = (wishlist_item_id) => {
        setConfirmModal({
            is_open: true,
            wishlist_item_id,
        });
    };

    const handleConfirmRemove = async () => {
        if (confirm_modal.wishlist_item_id) {
            await removeFromWishlist(confirm_modal.wishlist_item_id);
            setConfirmModal({
                is_open: false,
                wishlist_item_id: null,
            });
        }
    };

    const handleAddToCart = async (product_id) => {
        await addToCart(product_id, 1);
    };

    if (!authenticated) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-4xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {t('wishlist.login_required')}
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400 mb-6">
                                {t('wishlist.login_required_description')}
                            </p>
                            <Link
                                to="/login"
                                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                            >
                                {t('header.login')}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

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

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                <div className="max-w-6xl mx-auto mt-12">
                    <div className="mb-6">
                        <Link
                            to="/"
                            className="inline-flex items-center gap-2 px-3 py-2 min-h-[44px] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors touch-manipulation rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 -ml-3"
                        >
                            <ArrowLeft className="w-5 h-5 sm:w-4 sm:h-4" />
                            <span className="text-base sm:text-sm font-medium">{t('wishlist.continue_shopping')}</span>
                        </Link>
                    </div>

                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div className="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <Heart className="w-8 h-8 fill-red-500 text-red-500" />
                                {t('wishlist.title')}
                            </h1>
                            {wishlist_items.length > 0 && (
                                <button
                                    onClick={() => setShowShareModal(true)}
                                    className="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                >
                                    <Share2 className="w-4 h-4" />
                                    {t('wishlist.share.share_button')}
                                </button>
                            )}
                        </div>

                        {wishlist_items.length === 0 ? (
                            <div className="p-12 text-center">
                                <div className="text-6xl mb-4">❤️</div>
                                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    {t('wishlist.empty')}
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-6">
                                    {t('wishlist.empty_description')}
                                </p>
                                <Link
                                    to="/"
                                    className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                                >
                                    {t('wishlist.continue_shopping')}
                                </Link>
                            </div>
                        ) : (
                            <div className="p-6">
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
                                                <div className="flex items-center gap-2 mt-4">
                                                    {item.product.stock > 0 ? (
                                                        <button
                                                            onClick={() => handleAddToCart(item.product.id)}
                                                            className="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium rounded-lg transition-colors"
                                                        >
                                                            <ShoppingCart className="w-4 h-4" />
                                                            {t('wishlist.add_to_cart')}
                                                        </button>
                                                    ) : (
                                                        <span className="flex-1 text-center text-sm text-red-600 dark:text-red-400 font-medium">
                                                            {t('product.out_of_stock')}
                                                        </span>
                                                    )}
                                                    <button
                                                        onClick={() => handleRemove(item.id)}
                                                        className="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                        title={t('wishlist.remove')}
                                                    >
                                                        <Trash2 className="w-5 h-5" />
                                                    </button>
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
            <ShareWishlistModal
                is_open={show_share_modal}
                on_close={() => setShowShareModal(false)}
            />
            <ConfirmationModal
                is_open={confirm_modal.is_open}
                on_close={() => setConfirmModal({ is_open: false, wishlist_item_id: null })}
                on_confirm={handleConfirmRemove}
                title={t('wishlist.remove')}
                message={t('wishlist.confirm_remove')}
                confirm_text={t('wishlist.remove')}
                cancel_text={t('common.cancel')}
                confirm_variant="danger"
            />
        </div>
    );
}

