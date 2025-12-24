import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ShoppingBag, Package, Calendar, DollarSign } from 'lucide-react';
import Header from '../components/Header';
import TelegramActivation from '../components/TelegramActivation';
import { getOrders } from '../services/api';

export default function Orders() {
    const { t } = useTranslation();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [current_page, setCurrentPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [per_page] = useState(15);

    useEffect(() => {
        loadOrders();
    }, [current_page]);

    const loadOrders = async () => {
        try {
            setLoading(true);
            const response = await getOrders(current_page, per_page);
            if (response.status && response.data) {
                setOrders(response.data);
                if (response.meta) {
                    setTotal(response.meta.total);
                }
            }
        } catch (error) {
            console.error('Error loading orders:', error);
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

    const getStatusColor = (status) => {
        const colors = {
            new: 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300',
            pending: 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-300',
            processing: 'bg-purple-100 dark:bg-purple-900/20 text-purple-800 dark:text-purple-300',
            completed: 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-300',
            cancelled: 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-300',
            refunded: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
        };
        return colors[status] || colors.new;
    };

    const getStatusLabel = (status) => {
        return t(`order.status.${status}`);
    };

    const formatDate = (date_string) => {
        const date = new Date(date_string);
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    };

    const total_pages = Math.ceil(total / per_page);

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-6xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-8 animate-pulse">
                            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-6" />
                            <div className="space-y-4">
                                {[...Array(5)].map((_, i) => (
                                    <div key={i} className="h-24 bg-gray-200 dark:bg-gray-700 rounded" />
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
                        <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <ShoppingBag className="w-8 h-8" />
                            {t('order.order_history')}
                        </h1>
                    </div>

                    {/* Telegram Activation */}
                    <div className="mb-6">
                        <TelegramActivation />
                    </div>

                    {orders.length === 0 ? (
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                            <Package className="w-16 h-16 mx-auto mb-4 text-gray-400" />
                            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {t('order.no_orders')}
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400 mb-6">
                                {t('order.no_orders_description')}
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
                            <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {orders.map((order) => (
                                        <Link
                                            key={order.id}
                                            to={`/orders/${order.id}`}
                                            className="block p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                        >
                                            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-3 mb-2">
                                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                                            {t('order.order_number')} #{order.id}
                                                        </h3>
                                                        <span
                                                            className={`px-3 py-1 text-xs font-semibold rounded-full ${getStatusColor(order.status)}`}
                                                        >
                                                            {getStatusLabel(order.status)}
                                                        </span>
                                                    </div>
                                                    <div className="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                                        <div className="flex items-center gap-1">
                                                            <Package className="w-4 h-4" />
                                                            <span>{order.store.name}</span>
                                                        </div>
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="w-4 h-4" />
                                                            <span>{formatDate(order.created_at)}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="flex items-center gap-1 text-xl font-bold text-gray-900 dark:text-white">
                                                        <DollarSign className="w-5 h-5" />
                                                        <span>{formatPrice(order.total)}</span>
                                                    </div>
                                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                        {t('order.view_details')} →
                                                    </p>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>

                            {/* Pagination */}
                            {total_pages > 1 && (
                                <div className="mt-6 flex items-center justify-center gap-2">
                                    <button
                                        onClick={() => setCurrentPage((p) => Math.max(1, p - 1))}
                                        disabled={current_page === 1}
                                        className="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        {t('common.previous')}
                                    </button>
                                    <span className="px-4 py-2 text-gray-700 dark:text-gray-300">
                                        {t('common.page')} {current_page} {t('common.of')} {total_pages}
                                    </span>
                                    <button
                                        onClick={() => setCurrentPage((p) => Math.min(total_pages, p + 1))}
                                        disabled={current_page === total_pages}
                                        className="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        {t('common.next')}
                                    </button>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}

