import { useState, useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import { CreditCard, Lock, AlertCircle, CheckCircle } from 'lucide-react';

export default function PaymentForm({ order_id, order_total, onSuccess, onError }) {
    const { t } = useTranslation();
    const form_ref = useRef(null);
    const [client_key, setClientKey] = useState(null);
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState(null);
    const [paylib_loaded, setPaylibLoaded] = useState(false);

    // Load PayTabs client key
    useEffect(() => {
        const fetchClientKey = async () => {
            try {
                const response = await fetch('/api/v1/payment/client-key', {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.status && data.data?.client_key) {
                    setClientKey(data.data.client_key);
                } else {
                    setErrors(t('payment.errors.client_key_failed'));
                }
            } catch (error) {
                console.error('Failed to fetch client key:', error);
                setErrors(t('payment.errors.client_key_failed'));
            }
        };

        fetchClientKey();
    }, [t]);

    // Load PayTabs paylib.js script
    useEffect(() => {
        if (!client_key) return;

        // Check if script already loaded
        if (window.paylib) {
            setPaylibLoaded(true);
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://secure-iraq.paytabs.com/payment/js/paylib.js';
        script.async = true;
        script.onload = () => {
            setPaylibLoaded(true);
        };
        script.onerror = () => {
            setErrors(t('payment.errors.script_load_failed'));
        };
        document.body.appendChild(script);

        // Listen for CORS errors
        const handleCorsError = (event) => {
            if (event.message && event.message.includes('CORS')) {
                const is_localhost = window.location.hostname === 'localhost' || 
                                    window.location.hostname === '127.0.0.1' ||
                                    window.location.hostname.includes('localhost');
                if (is_localhost) {
                    setErrors(t('payment.errors.cors_localhost'));
                }
            }
        };
        window.addEventListener('error', handleCorsError);

        return () => {
            // Cleanup script if component unmounts
            const existing_script = document.querySelector('script[src*="paylib.js"]');
            if (existing_script) {
                existing_script.remove();
            }
            window.removeEventListener('error', handleCorsError);
        };
    }, [client_key, t]);

    // Initialize PayTabs when component is ready
    useEffect(() => {
        if (!paylib_loaded || !window.paylib || !client_key || !form_ref.current) {
            return;
        }

        // Initialize PayTabs managed form
        window.paylib.inlineForm({
            key: client_key,
            form: form_ref.current,
            autoSubmit: false, // We'll handle submission manually
            callback: async (response) => {
                setLoading(true);
                setErrors(null);

                if (response.error) {
                    // Handle PayTabs errors
                    let error_message = response.error.message || t('payment.errors.payment_failed');
                    
                    // Check for CORS errors
                    if (error_message.includes('CORS') || error_message.includes('Access-Control')) {
                        const is_localhost = window.location.hostname === 'localhost' || 
                                            window.location.hostname === '127.0.0.1' ||
                                            window.location.hostname.includes('localhost');
                        if (is_localhost) {
                            error_message = t('payment.errors.cors_localhost');
                        }
                    }
                    
                    setErrors(error_message);
                    setLoading(false);
                    if (onError) {
                        onError(error_message);
                    }
                    // Display error in PayTabs error container
                    const error_container = document.getElementById('paymentErrors');
                    if (error_container && window.paylib) {
                        window.paylib.handleError(error_container, response);
                    }
                    return;
                }

                // Payment token received, send to backend
                const payment_token = response.token;
                if (!payment_token) {
                    const error_message = t('payment.errors.token_missing');
                    setErrors(error_message);
                    setLoading(false);
                    if (onError) {
                        onError(error_message);
                    }
                    return;
                }

                // Send payment token to backend
                try {
                    const payment_response = await fetch('/api/v1/payment/process', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: order_id,
                            payment_token: payment_token,
                        }),
                    });

                    const payment_data = await payment_response.json();

                    if (payment_data.status && payment_data.data) {
                        // Payment processed successfully
                        if (onSuccess) {
                            onSuccess(payment_data.data);
                        }
                    } else {
                        const error_message = payment_data.message || t('payment.errors.payment_failed');
                        setErrors(error_message);
                        if (onError) {
                            onError(error_message);
                        }
                    }
                } catch (api_error) {
                    console.error('Payment API error:', api_error);
                    const error_message = t('payment.errors.api_error');
                    setErrors(error_message);
                    if (onError) {
                        onError(error_message);
                    }
                } finally {
                    setLoading(false);
                }
            },
        });
    }, [paylib_loaded, client_key, order_id, t, onSuccess, onError]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!paylib_loaded || !window.paylib) {
            setErrors(t('payment.errors.script_not_loaded'));
            return;
        }

        if (!client_key) {
            setErrors(t('payment.errors.client_key_missing'));
            return;
        }

        // PayTabs will intercept the form submission and call the callback
        // The form will be submitted normally, but PayTabs will process it first
        setLoading(true);
        setErrors(null);
    };

    if (!client_key) {
        return (
            <div className="flex items-center justify-center p-8">
                <div className="text-center">
                    <div className="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p className="text-gray-600 dark:text-gray-400">{t('payment.loading')}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div className="flex items-center gap-3 mb-6">
                <div className="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <CreditCard className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 className="text-xl font-bold text-gray-900 dark:text-white">
                        {t('payment.title')}
                    </h3>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                        {t('payment.subtitle')}
                    </p>
                </div>
            </div>

            {errors && (
                <div className="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-start gap-3">
                    <AlertCircle className="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <p className="text-sm text-red-700 dark:text-red-300">{errors}</p>
                </div>
            )}

            <form ref={form_ref} onSubmit={handleSubmit} id="payment-form" className="space-y-5">
                <div id="paymentErrors" className="text-red-600 dark:text-red-400 text-sm"></div>

                {/* Card Number */}
                <div>
                    <label htmlFor="card_number" className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        {t('payment.fields.card_number')}
                    </label>
                    <div className="relative">
                        <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <CreditCard className="w-5 h-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            id="card_number"
                            data-paylib="number"
                            size="20"
                            maxLength="19"
                            placeholder="1234 5678 9012 3456"
                            required
                            className="w-full ps-10 pe-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/50 dark:text-white transition-all duration-200"
                        />
                    </div>
                </div>

                {/* Expiry Date */}
                <div>
                    <label className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        {t('payment.fields.expiry_date')}
                    </label>
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label htmlFor="exp_month" className="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                {t('payment.fields.month')}
                            </label>
                            <input
                                type="text"
                                id="exp_month"
                                data-paylib="expmonth"
                                size="2"
                                maxLength="2"
                                placeholder="MM"
                                required
                                className="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/50 dark:text-white transition-all duration-200"
                            />
                        </div>
                        <div>
                            <label htmlFor="exp_year" className="block text-xs text-gray-500 dark:text-gray-400 mb-1">
                                {t('payment.fields.year')}
                            </label>
                            <input
                                type="text"
                                id="exp_year"
                                data-paylib="expyear"
                                size="4"
                                maxLength="4"
                                placeholder="YYYY"
                                required
                                className="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/50 dark:text-white transition-all duration-200"
                            />
                        </div>
                    </div>
                </div>

                {/* CVV */}
                <div>
                    <label htmlFor="cvv" className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        {t('payment.fields.cvv')}
                    </label>
                    <div className="relative">
                        <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <Lock className="w-5 h-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            id="cvv"
                            data-paylib="cvv"
                            size="4"
                            maxLength="4"
                            placeholder="123"
                            required
                            className="w-full ps-10 pe-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/50 dark:text-white transition-all duration-200"
                        />
                    </div>
                </div>

                {/* Order Total Display */}
                <div className="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div className="flex justify-between items-center">
                        <span className="text-lg font-semibold text-gray-700 dark:text-gray-300">
                            {t('payment.total')}
                        </span>
                        <span className="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {order_total?.toLocaleString()} IQD
                        </span>
                    </div>
                </div>

                {/* Submit Button */}
                <button
                    type="submit"
                    disabled={loading || !paylib_loaded}
                    className="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
                >
                    {loading ? (
                        <>
                            <span className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            {t('payment.processing')}
                        </>
                    ) : (
                        <>
                            <Lock className="w-5 h-5" />
                            {t('payment.submit')}
                        </>
                    )}
                </button>

                {/* Security Notice */}
                <div className="flex items-start gap-2 text-xs text-gray-500 dark:text-gray-400 pt-2">
                    <Lock className="w-4 h-4 flex-shrink-0 mt-0.5" />
                    <p>{t('payment.security_notice')}</p>
                </div>
            </form>
        </div>
    );
}

