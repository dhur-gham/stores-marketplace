import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ShoppingCart, Plus, Minus, Trash2, ArrowLeft } from 'lucide-react';
import Header from '../components/Header';
import ConfirmationModal from '../components/ConfirmationModal';
import { useCart } from '../contexts/CartContext';

export default function Cart() {
    const { t } = useTranslation();
    const { cart_items, loading, cart_total, updateCartItem, removeFromCart, clearCart } = useCart();
    const [confirm_modal, setConfirmModal] = useState({
        is_open: false,
        type: null, // 'remove' or 'clear'
        cart_item_id: null,
    });

    const formatPrice = (price) => {
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(price);
        return `${formatted} IQD`;
    };

    const handleQuantityChange = async (cart_item_id, new_quantity) => {
        if (new_quantity < 1) {
            return;
        }
        await updateCartItem(cart_item_id, new_quantity);
    };

    const handleRemove = (cart_item_id) => {
        setConfirmModal({
            is_open: true,
            type: 'remove',
            cart_item_id,
        });
    };

    const handleClearCart = () => {
        setConfirmModal({
            is_open: true,
            type: 'clear',
            cart_item_id: null,
        });
    };

    const handleConfirmAction = async () => {
        if (confirm_modal.type === 'remove' && confirm_modal.cart_item_id) {
            await removeFromCart(confirm_modal.cart_item_id);
        } else if (confirm_modal.type === 'clear') {
            await clearCart();
        }
        setConfirmModal({
            is_open: false,
            type: null,
            cart_item_id: null,
        });
    };

    const getConfirmModalProps = () => {
        if (confirm_modal.type === 'remove') {
            return {
                title: t('cart.remove'),
                message: t('cart.confirm_remove'),
                confirm_text: t('cart.remove'),
            };
        } else if (confirm_modal.type === 'clear') {
            return {
                title: t('cart.clear_cart'),
                message: t('cart.confirm_clear'),
                confirm_text: t('cart.clear_cart'),
            };
        }
        return {
            title: '',
            message: '',
            confirm_text: '',
        };
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-4xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-8 animate-pulse">
                            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-6" />
                            <div className="space-y-4">
                                {[...Array(3)].map((_, i) => (
                                    <div key={i} className="h-32 bg-gray-200 dark:bg-gray-700 rounded" />
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
                <div className="max-w-4xl mx-auto mt-12">
                    <div className="mb-6">
                        <Link
                            to="/"
                            className="inline-flex items-center gap-2 px-3 py-2 min-h-[44px] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors touch-manipulation rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 -ml-3"
                        >
                            <ArrowLeft className="w-5 h-5 sm:w-4 sm:h-4" />
                            <span className="text-base sm:text-sm font-medium">{t('cart.continue_shopping')}</span>
                        </Link>
                    </div>

                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <div className="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                <ShoppingCart className="w-8 h-8" />
                                {t('cart.title')}
                            </h1>
                            {cart_items.length > 0 && (
                                <button
                                    onClick={handleClearCart}
                                    className="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                >
                                    {t('cart.clear_cart')}
                                </button>
                            )}
                        </div>

                        {cart_items.length === 0 ? (
                            <div className="p-12 text-center">
                                <div className="text-6xl mb-4">🛒</div>
                                <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                    {t('cart.empty')}
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-6">
                                    {t('cart.empty_description')}
                                </p>
                                <Link
                                    to="/"
                                    className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                                >
                                    {t('cart.continue_shopping')}
                                </Link>
                            </div>
                        ) : (
                            <>
                                <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {cart_items.map((item) => (
                                        <div key={item.id} className="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <div className="flex flex-col md:flex-row gap-4">
                                                <Link
                                                    to={`/product/${item.product.id}`}
                                                    className="flex-shrink-0 w-24 h-24 md:w-32 md:h-32 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700"
                                                >
                                                    {item.product.image ? (
                                                        <img
                                                            src={item.product.image}
                                                            alt={item.product.name}
                                                            className="w-full h-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full flex items-center justify-center text-gray-400">
                                                            <ShoppingCart className="w-8 h-8" />
                                                        </div>
                                                    )}
                                                </Link>
                                                <div className="flex-1 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                    <div className="flex-1">
                                                        <Link
                                                            to={`/product/${item.product.id}`}
                                                            className="text-lg font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors block mb-1"
                                                        >
                                                            {item.product.name}
                                                        </Link>
                                                        <p className="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                                            {item.product.store.name}
                                                        </p>
                                                        <p className="text-lg font-bold text-gray-900 dark:text-white">
                                                            {formatPrice(item.price)}
                                                        </p>
                                                    </div>
                                                    <div className="flex items-center gap-4">
                                                        <div className="flex items-center gap-2">
                                                            <button
                                                                onClick={() => handleQuantityChange(item.id, item.quantity - 1)}
                                                                disabled={item.quantity <= 1}
                                                                className="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                            >
                                                                <Minus className="w-4 h-4" />
                                                            </button>
                                                            <span className="w-12 text-center font-semibold text-gray-900 dark:text-white">
                                                                {item.quantity}
                                                            </span>
                                                            <button
                                                                onClick={() => handleQuantityChange(item.id, item.quantity + 1)}
                                                                disabled={item.quantity >= item.product.stock}
                                                                className="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                            >
                                                                <Plus className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                        <div className="text-right min-w-[100px]">
                                                            <p className="text-lg font-bold text-gray-900 dark:text-white">
                                                                {formatPrice(item.subtotal)}
                                                            </p>
                                                            <p className="text-xs text-gray-500 dark:text-gray-400">
                                                                {t('cart.subtotal')}
                                                            </p>
                                                        </div>
                                                        <button
                                                            onClick={() => handleRemove(item.id)}
                                                            className="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                                            title={t('cart.remove')}
                                                        >
                                                            <Trash2 className="w-5 h-5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                    <div className="flex items-center justify-between mb-4">
                                        <span className="text-lg font-semibold text-gray-900 dark:text-white">
                                            {t('cart.total')}
                                        </span>
                                        <span className="text-2xl font-bold text-gray-900 dark:text-white">
                                            {formatPrice(cart_total)}
                                        </span>
                                    </div>
                                    <Link
                                        to="/checkout"
                                        className="w-full block text-center py-3 px-6 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors"
                                    >
                                        {t('order.proceed_to_checkout')}
                                    </Link>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>
            <ConfirmationModal
                is_open={confirm_modal.is_open}
                on_close={() => setConfirmModal({ is_open: false, type: null, cart_item_id: null })}
                on_confirm={handleConfirmAction}
                {...getConfirmModalProps()}
                cancel_text={t('common.cancel')}
                confirm_variant="danger"
            />
        </div>
    );
}

