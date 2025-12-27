import { useState, useEffect } from 'react';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, CreditCard, Package, CheckCircle } from 'lucide-react';
import Header from '../components/Header';
import PaymentForm from '../components/PaymentForm';
import { getOrder } from '../services/api';

export default function Payment() {
    const { orderId } = useParams();
    const navigate = useNavigate();
    const location = useLocation();
    const { t } = useTranslation();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const loadOrder = async () => {
            try {
                setLoading(true);
                const response = await getOrder(orderId);
                if (response.status && response.data) {
                    setOrder(response.data);
                    
                    // Check if order is already paid
                    if (response.data.payment_status === 'completed') {
                        navigate(`/orders/${orderId}?payment=success`);
                    }
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

        if (orderId) {
            loadOrder();
        }
    }, [orderId, navigate]);

    const handlePaymentSuccess = (paymentData) => {
        // Check if redirect is required (3D Secure)
        if (paymentData.requires_redirect && paymentData.redirect_url) {
            // Redirect to PayTabs 3D Secure page
            window.location.href = paymentData.redirect_url;
            return;
        }

        // Payment completed successfully
        // Redirect to order confirmation page
        navigate(`/orders/${orderId}?payment=success`, {
            state: { 
                message: t('payment.success.payment_completed'),
                orders: location.state?.orders || []
            }
        });
    };

    const handlePaymentError = (error) => {
        // Error is already displayed in PaymentForm component
        console.error('Payment error:', error);
    };

    const formatPrice = (price) => {
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(price);
        return `${formatted} IQD`;
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-2xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-8 animate-pulse">
                            <div className="h-8 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-6" />
                            <div className="h-64 bg-gray-200 dark:bg-gray-700 rounded" />
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
                    <div className="max-w-2xl mx-auto mt-12">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-12 text-center">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {error || 'Order not found'}
                            </h2>
                            <button
                                onClick={() => navigate('/orders')}
                                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                            >
                                <ArrowLeft className="w-4 h-4" />
                                {t('common.back')}
                            </button>
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
                <div className="max-w-2xl mx-auto mt-12">
                    <div className="mb-6">
                        <button
                            onClick={() => navigate('/checkout')}
                            className="inline-flex items-center gap-2 px-3 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <ArrowLeft className="w-5 h-5" />
                            <span className="font-medium">{t('common.back')}</span>
                        </button>
                    </div>

                    <div className="mb-6">
                        <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <CreditCard className="w-8 h-8" />
                            {t('payment.title')}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">{t('payment.subtitle')}</p>
                    </div>

                    {/* Order Summary */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
                        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <Package className="w-5 h-5" />
                            {t('orders.order_summary')}
                        </h2>
                        <div className="space-y-2">
                            <div className="flex justify-between">
                                <span className="text-gray-600 dark:text-gray-400">{t('orders.order_id')}:</span>
                                <span className="font-semibold text-gray-900 dark:text-white">#{order.id}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600 dark:text-gray-400">{t('orders.store')}:</span>
                                <span className="font-semibold text-gray-900 dark:text-white">{order.store.name}</span>
                            </div>
                            <div className="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span className="text-lg font-semibold text-gray-900 dark:text-white">{t('payment.total')}:</span>
                                <span className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {formatPrice(order.total)}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Payment Form */}
                    <PaymentForm
                        order_id={order.id}
                        order_total={order.total}
                        onSuccess={handlePaymentSuccess}
                        onError={handlePaymentError}
                    />
                </div>
            </div>
        </div>
    );
}

