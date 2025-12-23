import { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, Package, MapPin, Calendar, DollarSign, ShoppingBag } from 'lucide-react';
import Header from '../components/Header';
import { getOrder } from '../services/api';

export default function OrderDetail() {
    const { t } = useTranslation();
    const { orderId } = useParams();
    const navigate = useNavigate();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        loadOrder();
    }, [orderId]);

    const loadOrder = async () => {
        try {
            setLoading(true);
            const response = await getOrder(orderId);
            if (response.status && response.data) {
                setOrder(response.data);
            } else {
                setError('Order not found');
            }
        } catch (error) {
            console.error('Error loading order:', error);
            if (error.response?.status === 404) {
                setError('Order not found');
            } else {
                setError('Failed to load order');
            }
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

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-4xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-8 animate-pulse">
                            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-6" />
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    if (error || !order) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-4xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-12 text-center">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {error || 'Order not found'}
                            </h2>
                            <Link
                                to="/orders"
                                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                            >
                                <ArrowLeft className="w-4 h-4" />
                                {t('order.back_to_orders')}
                            </Link>
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
                            to="/orders"
                            className="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                        >
                            <ArrowLeft className="w-4 h-4" />
                            <span>{t('order.back_to_orders')}</span>
                        </Link>
                    </div>

                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        {/* Header */}
                        <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                        {t('order.order_number')} #{order.id}
                                    </h1>
                                    <div className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <Calendar className="w-4 h-4" />
                                        <span>{formatDate(order.created_at)}</span>
                                    </div>
                                </div>
                                <span
                                    className={`px-4 py-2 text-sm font-semibold rounded-full ${getStatusColor(order.status)}`}
                                >
                                    {getStatusLabel(order.status)}
                                </span>
                            </div>
                        </div>

                        {/* Store Info */}
                        <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div className="flex items-center gap-3">
                                {order.store.image && (
                                    <img
                                        src={order.store.image}
                                        alt={order.store.name}
                                        className="w-12 h-12 rounded-full object-cover"
                                    />
                                )}
                                <div>
                                    <h2 className="font-semibold text-gray-900 dark:text-white">{order.store.name}</h2>
                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                        {order.store.type === 'physical' ? t('stores.physical') : t('stores.digital')}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Delivery Address (if physical) */}
                        {order.store.type === 'physical' && order.address && (
                            <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                    <MapPin className="w-4 h-4" />
                                    {t('order.delivery_address')}
                                </h3>
                                <p className="text-gray-700 dark:text-gray-300">{order.address}</p>
                                {order.city && (
                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">{order.city.name}</p>
                                )}
                            </div>
                        )}

                        {/* Order Items */}
                        <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                {t('order.items')}
                            </h3>
                            <div className="space-y-4">
                                {order.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                    >
                                        {item.product.image && (
                                            <img
                                                src={item.product.image}
                                                alt={item.product.name}
                                                className="w-20 h-20 rounded-lg object-cover"
                                            />
                                        )}
                                        <div className="flex-1">
                                            <Link
                                                to={`/product/${item.product.id}`}
                                                className="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                                            >
                                                {item.product.name}
                                            </Link>
                                            <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                {t('cart.quantity')}: {item.quantity} × {formatPrice(item.price)}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-semibold text-gray-900 dark:text-white">
                                                {formatPrice(item.subtotal)}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Totals */}
                        <div className="p-6 bg-gray-50 dark:bg-gray-700/50">
                            <div className="space-y-2">
                                <div className="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>{t('order.subtotal')}</span>
                                    <span>{formatPrice(order.items_total)}</span>
                                </div>
                                {order.delivery_price > 0 && (
                                    <div className="flex justify-between text-gray-600 dark:text-gray-400">
                                        <span>{t('order.delivery_price')}</span>
                                        <span>{formatPrice(order.delivery_price)}</span>
                                    </div>
                                )}
                                <div className="flex justify-between items-center pt-2 border-t border-gray-300 dark:border-gray-600">
                                    <span className="text-lg font-semibold text-gray-900 dark:text-white">
                                        {t('order.total')}
                                    </span>
                                    <span className="text-2xl font-bold text-gray-900 dark:text-white">
                                        {formatPrice(order.total)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

