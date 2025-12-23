import { Link, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { CheckCircle, ShoppingBag, ArrowRight } from 'lucide-react';
import Header from '../components/Header';

export default function OrderConfirmation() {
    const { t } = useTranslation();
    const location = useLocation();
    const orders = location.state?.orders || [];

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                <div className="max-w-2xl mx-auto mt-12">
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
                        <div className="mb-6">
                            <div className="w-20 h-20 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                <CheckCircle className="w-12 h-12 text-green-600 dark:text-green-400" />
                            </div>
                            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {t('order.confirmation')}
                            </h1>
                            <p className="text-gray-600 dark:text-gray-400">
                                {t('order.placed_successfully')}
                            </p>
                        </div>

                        {orders.length > 0 && (
                            <div className="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <p className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {t('order.order_numbers')}:
                                </p>
                                <div className="flex flex-wrap gap-2 justify-center">
                                    {orders.map((order) => (
                                        <span
                                            key={order.id}
                                            className="px-3 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 rounded-full text-sm font-semibold"
                                        >
                                            #{order.id}
                                        </span>
                                    ))}
                                </div>
                            </div>
                        )}

                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                to="/orders"
                                className="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors"
                            >
                                <ShoppingBag className="w-5 h-5" />
                                {t('order.view_orders')}
                            </Link>
                            <Link
                                to="/"
                                className="inline-flex items-center justify-center gap-2 px-6 py-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 font-semibold rounded-lg transition-colors"
                            >
                                {t('cart.continue_shopping')}
                                <ArrowRight className="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

