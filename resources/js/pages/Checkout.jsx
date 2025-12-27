import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ShoppingCart, ArrowLeft, Package, MapPin, MessageCircle, ExternalLink } from 'lucide-react';
import Header from '../components/Header';
import { useCart } from '../contexts/CartContext';
import { useAuth } from '../contexts/AuthContext';
import { fetchCities, fetchStoreDeliveryPrices, getTelegramActivationLink } from '../services/api';
import { Link } from 'react-router-dom';

export default function Checkout() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const { cart_items, loading: cart_loading, placeOrder } = useCart();
    const { customer } = useAuth();
    const [cities, setCities] = useState([]);
    const [loading_cities, setLoadingCities] = useState(true);
    const [address_data, setAddressData] = useState({});
    const [delivery_prices, setDeliveryPrices] = useState({}); // Store delivery prices by store_id
    const [placing, setPlacing] = useState(false);
    const [error, setError] = useState(null);
    const [validation_errors, setValidationErrors] = useState({});
    const [telegram_link, setTelegramLink] = useState(null);
    const [telegram_activated, setTelegramActivated] = useState(false);
    const [loading_telegram, setLoadingTelegram] = useState(true);
    const [payment_method, setPaymentMethod] = useState('cod'); // 'cod' or 'online'

    // Group cart items by store
    const items_by_store = cart_items.reduce((acc, item) => {
        const store_id = item.product.store.id;
        if (!acc[store_id]) {
            acc[store_id] = {
                store: item.product.store,
                items: [],
            };
        }
        acc[store_id].items.push(item);
        return acc;
    }, {});

    const stores = Object.values(items_by_store);

    useEffect(() => {
        const loadCities = async () => {
            try {
                const response = await fetchCities();
                if (response.status && response.data) {
                    setCities(response.data);
                }
            } catch (error) {
                console.error('Error loading cities:', error);
            } finally {
                setLoadingCities(false);
            }
        };
        loadCities();
    }, []);

    // Load Telegram activation link
    useEffect(() => {
        const loadTelegramLink = async () => {
            if (customer) {
                try {
                    setLoadingTelegram(true);
                    const response = await getTelegramActivationLink();
                    if (response?.data) {
                        setTelegramLink(response.data.activation_link || null);
                        setTelegramActivated(response.data.is_activated || false);
                    }
                } catch (error) {
                    // Ignore errors
                } finally {
                    setLoadingTelegram(false);
                }
            }
        };
        loadTelegramLink();
    }, [customer]);

    // Load delivery prices for physical stores
    useEffect(() => {
        const loadDeliveryPrices = async () => {
            const physical_stores = stores.filter((sg) => sg.store.type === 'physical');
            const prices = {};

            for (const store_group of physical_stores) {
                try {
                    const response = await fetchStoreDeliveryPrices(store_group.store.id);
                    if (response.status && response.data) {
                        // Convert array to object keyed by city_id for easy lookup
                        prices[store_group.store.id] = response.data.reduce((acc, item) => {
                            acc[item.city_id] = item.price;
                            return acc;
                        }, {});
                    }
                } catch (error) {
                    console.error(`Error loading delivery prices for store ${store_group.store.id}:`, error);
                }
            }

            setDeliveryPrices(prices);
        };

        if (stores.length > 0) {
            loadDeliveryPrices();
        }
    }, [cart_items]);

    const formatPrice = (price) => {
        const formatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(price);
        return `${formatted} IQD`;
    };

    // Calculate delivery price for a store based on selected city
    const getDeliveryPrice = (store_id, city_id) => {
        if (!city_id || !delivery_prices[store_id]) {
            return 0;
        }
        return delivery_prices[store_id][city_id] || 0;
    };

    // Calculate totals per store
    const getStoreTotal = (store_group) => {
        const items_total = store_group.items.reduce((sum, item) => sum + item.subtotal, 0);
        const store = store_group.store;
        
        if (store.type === 'physical') {
            const city_id = address_data[store.id]?.city_id;
            const delivery_price = city_id ? getDeliveryPrice(store.id, parseInt(city_id)) : 0;
            return items_total + delivery_price;
        }
        
        return items_total;
    };

    const getGrandTotal = () => {
        return stores.reduce((sum, store_group) => {
            return sum + getStoreTotal(store_group);
        }, 0);
    };

    const handleAddressChange = (store_id, field, value) => {
        setAddressData((prev) => ({
            ...prev,
            [store_id]: {
                ...prev[store_id],
                [field]: value,
            },
        }));
        // Clear validation error for this field when user types
        const error_key = `address_data.${store_id}.${field}`;
        if (validation_errors[error_key]) {
            setValidationErrors((prev) => {
                const new_errors = { ...prev };
                delete new_errors[error_key];
                return new_errors;
            });
        }
        // Also clear general error
        if (error) {
            setError(null);
        }
    };

    const validateAddresses = () => {
        for (const store_group of stores) {
            const store = store_group.store;
            if (store.type === 'physical') {
                const store_address = address_data[store.id];
                if (!store_address || !store_address.city_id || !store_address.address || store_address.address.trim() === '') {
                    return false;
                }
            }
        }
        return true;
    };

    const getValidationError = (store_id, field) => {
        const error_key = `address_data.${store_id}.${field}`;
        return validation_errors[error_key]?.[0] || null;
    };

    const formatValidationError = (error_message) => {
        // Format Laravel validation errors to be more user-friendly and localized
        if (!error_message) return '';
        
        // Extract the error message and localize it
        let message = error_message
            .replace(/address_data\.\d+\.address/gi, t('checkout.address'))
            .replace(/address_data\.\d+\.city_id/gi, t('checkout.city'))
            .replace(/the address field/gi, t('checkout.address'))
            .replace(/the city field/gi, t('checkout.city'));
        
        // Localize common error patterns
        if (message.includes('must be at least') && message.includes('characters')) {
            const min_match = message.match(/at least (\d+)/);
            if (min_match) {
                if (message.toLowerCase().includes('address')) {
                    return t('checkout.validation.address_min', { min: min_match[1] });
                }
            }
        }
        
        if (message.includes('is required') || message.includes('required')) {
            if (message.toLowerCase().includes('address')) {
                return t('checkout.validation.address_required');
            }
            if (message.toLowerCase().includes('city')) {
                return t('checkout.validation.city_required');
            }
        }
        
        if (message.includes('invalid') || message.includes('does not exist')) {
            if (message.toLowerCase().includes('city')) {
                return t('checkout.validation.city_invalid');
            }
        }
        
        return message;
    };

    const handlePlaceOrder = async () => {
        if (!validateAddresses()) {
            setError(t('checkout.address_required'));
            return;
        }

        setPlacing(true);
        setError(null);
        setValidationErrors({});

        const result = await placeOrder(address_data, payment_method);

        if (result.success) {
            const orders = result.data.orders;
            
            // Online payment temporarily disabled - only COD available
            // if (payment_method === 'online' && orders.length > 0) {
            //     // For multiple orders, redirect to payment for the first one
            //     // In production, you might want to handle multiple orders differently
            //     navigate(`/payment/${orders[0].id}`, { 
            //         state: { 
            //             orders: orders,
            //             payment_method: 'online'
            //         } 
            //     });
            // } else {
            //     // COD - go to confirmation page
            //     navigate('/order-confirmation', { state: { orders: orders } });
            // }
            
            // COD - go to confirmation page
            navigate('/order-confirmation', { state: { orders: orders } });
        } else {
            if (result.errors) {
                // Handle validation errors
                setValidationErrors(result.errors);
                // Show first error as general message
                const first_error_key = Object.keys(result.errors)[0];
                const first_error = result.errors[first_error_key]?.[0];
                if (first_error) {
                    setError(formatValidationError(first_error));
                }
            } else {
                setError(result.error || t('checkout.order_failed'));
            }
            setPlacing(false);
        }
    };

    if (cart_loading) {
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

    if (cart_items.length === 0) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="max-w-4xl mx-auto mt-12 text-center">
                        <div className="bg-white dark:bg-gray-800 rounded-xl p-12">
                            <ShoppingCart className="w-16 h-16 mx-auto mb-4 text-gray-400" />
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {t('checkout.empty_cart')}
                            </h2>
                            <Link
                                to="/cart"
                                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                            >
                                {t('cart.continue_shopping')}
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
                            to="/cart"
                            className="inline-flex items-center gap-2 px-3 py-2 min-h-[44px] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors touch-manipulation rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 -ml-3"
                        >
                            <ArrowLeft className="w-5 h-5 sm:w-4 sm:h-4" />
                            <span className="text-base sm:text-sm font-medium">{t('common.back')}</span>
                        </Link>
                    </div>

                    <div className="mb-6">
                        <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <ShoppingCart className="w-8 h-8" />
                            {t('checkout.title')}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">{t('checkout.review_order')}</p>
                    </div>

                    {error && (
                        <div className="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p className="text-red-600 dark:text-red-400">{error}</p>
                        </div>
                    )}

                    {/* Telegram Activation Banner */}
                    {!loading_telegram && !telegram_activated && (
                        <div className="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div className="flex items-start gap-3">
                                <MessageCircle className="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                                <div className="flex-1">
                                    <h3 className="font-semibold text-blue-900 dark:text-blue-100 mb-1">
                                        {t('telegram.activate_notifications')}
                                    </h3>
                                    <p className="text-sm text-blue-700 dark:text-blue-300 mb-3">
                                        {t('telegram.activate_description')} {t('telegram.activate_before_order') || 'Activate now to receive order updates!'}
                                    </p>
                                    {telegram_link ? (
                                        <a
                                            href={telegram_link}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm"
                                        >
                                            <span>{t('telegram.activate_button')}</span>
                                            <ExternalLink className="w-4 h-4" />
                                        </a>
                                    ) : (
                                        <p className="text-xs text-gray-600 dark:text-gray-400">
                                            {t('telegram.loading_link') || 'Loading activation link...'}
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    <div className="space-y-6 mb-8">
                        {stores.map((store_group) => {
                            const store = store_group.store;
                            const store_items = store_group.items;
                            const is_physical = store.type === 'physical';
                            const items_total = store_items.reduce((sum, item) => sum + item.subtotal, 0);
                            const selected_city_id = address_data[store.id]?.city_id;
                            const delivery_price = is_physical && selected_city_id ? getDeliveryPrice(store.id, parseInt(selected_city_id)) : 0;
                            const store_total = items_total + delivery_price;

                            return (
                                <div key={store.id} className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <div className="mb-4 flex items-center gap-3">
                                        <Package className="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                        <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                                            {store.name}
                                        </h2>
                                        <span className="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300">
                                            {is_physical ? t('stores.physical') : t('stores.digital')}
                                        </span>
                                    </div>

                                    {/* Order Items */}
                                    <div className="mb-6 space-y-3">
                                        {store_items.map((item) => (
                                            <div key={item.id} className="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                                <div className="flex-1">
                                                    <p className="font-medium text-gray-900 dark:text-white">
                                                        {item.product.name}
                                                    </p>
                                                    <p className="text-sm text-gray-500 dark:text-gray-400">
                                                        {t('cart.quantity')}: {item.quantity} × {formatPrice(item.price)}
                                                    </p>
                                                </div>
                                                <p className="font-semibold text-gray-900 dark:text-white">
                                                    {formatPrice(item.subtotal)}
                                                </p>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Address Form for Physical Stores */}
                                    {is_physical ? (
                                        <div className="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                                <MapPin className="w-4 h-4" />
                                                {t('checkout.delivery_address')}
                                            </h3>
                                            <div className="space-y-4">
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        {t('checkout.city')} *
                                                    </label>
                                                    <select
                                                        value={address_data[store.id]?.city_id || ''}
                                                        onChange={(e) => handleAddressChange(store.id, 'city_id', e.target.value)}
                                                        className={`w-full px-4 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${
                                                            getValidationError(store.id, 'city_id')
                                                                ? 'border-red-500 dark:border-red-500'
                                                                : 'border-gray-300 dark:border-gray-600'
                                                        }`}
                                                        required
                                                    >
                                                        <option value="">{t('checkout.select_city')}</option>
                                                        {cities.map((city) => (
                                                            <option key={city.id} value={city.id}>
                                                                {city.name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    {getValidationError(store.id, 'city_id') && (
                                                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                                                            {formatValidationError(getValidationError(store.id, 'city_id'))}
                                                        </p>
                                                    )}
                                                </div>
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        {t('checkout.address')} *
                                                    </label>
                                                    <textarea
                                                        value={address_data[store.id]?.address || ''}
                                                        onChange={(e) => handleAddressChange(store.id, 'address', e.target.value)}
                                                        rows={3}
                                                        className={`w-full px-4 py-2 border rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 ${
                                                            getValidationError(store.id, 'address')
                                                                ? 'border-red-500 dark:border-red-500'
                                                                : 'border-gray-300 dark:border-gray-600'
                                                        }`}
                                                        placeholder={t('checkout.address_placeholder')}
                                                        required
                                                    />
                                                    {getValidationError(store.id, 'address') && (
                                                        <p className="mt-1 text-sm text-red-600 dark:text-red-400">
                                                            {formatValidationError(getValidationError(store.id, 'address'))}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                            <p className="text-sm text-blue-700 dark:text-blue-300">
                                                {t('checkout.no_address_needed')}
                                            </p>
                                        </div>
                                    )}

                                    {/* Store Total */}
                                    <div className="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div className="flex justify-between items-center">
                                            <span className="text-gray-600 dark:text-gray-400">{t('checkout.items_total')}</span>
                                            <span className="font-semibold text-gray-900 dark:text-white">
                                                {formatPrice(items_total)}
                                            </span>
                                        </div>
                                        {is_physical && (
                                            <div className="flex justify-between items-center mt-2">
                                                <span className="text-gray-600 dark:text-gray-400">{t('checkout.delivery')}</span>
                                                <span className="font-semibold text-gray-900 dark:text-white">
                                                    {selected_city_id ? formatPrice(delivery_price) : t('checkout.select_city_first')}
                                                </span>
                                            </div>
                                        )}
                                        <div className="flex justify-between items-center mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                            <span className="text-base font-semibold text-gray-900 dark:text-white">{t('checkout.total')}</span>
                                            <span className="text-lg font-bold text-gray-900 dark:text-white">
                                                {formatPrice(store_total)}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Grand Total */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <div className="flex justify-between items-center">
                            <span className="text-lg font-semibold text-gray-900 dark:text-white">{t('checkout.total')}</span>
                            <span className="text-2xl font-bold text-gray-900 dark:text-white">
                                {formatPrice(getGrandTotal())}
                            </span>
                        </div>
                    </div>

                    {/* Payment Method Selection */}
                    <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {t('checkout.payment_method')}
                        </h3>
                        <div className="space-y-3">
                            <label className={`flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:bg-gray-50 dark:hover:bg-gray-700/50 ${
                                payment_method === 'cod'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-gray-200 dark:border-gray-700'
                            }`}>
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    checked={payment_method === 'cod'}
                                    onChange={(e) => setPaymentMethod(e.target.value)}
                                    className="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                />
                                <div className="ml-3 flex-1">
                                    <div className="flex items-center justify-between">
                                        <span className="font-medium text-gray-900 dark:text-white">
                                            {t('checkout.payment_methods.cod')}
                                        </span>
                                    </div>
                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {t('checkout.payment_methods.cod_description')}
                                    </p>
                                </div>
                            </label>
                            
                            {/* Online payment temporarily disabled */}
                            {/* <label className={`flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:bg-gray-50 dark:hover:bg-gray-700/50 ${
                                payment_method === 'online'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-gray-200 dark:border-gray-700'
                            }`}>
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="online"
                                    checked={payment_method === 'online'}
                                    onChange={(e) => setPaymentMethod(e.target.value)}
                                    className="w-4 h-4 text-blue-600 focus:ring-blue-500"
                                />
                                <div className="ml-3 flex-1">
                                    <div className="flex items-center justify-between">
                                        <span className="font-medium text-gray-900 dark:text-white">
                                            {t('checkout.payment_methods.online')}
                                        </span>
                                        <span className="px-2 py-1 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-300">
                                            {t('checkout.payment_methods.secure')}
                                        </span>
                                    </div>
                                    <p className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {t('checkout.payment_methods.online_description')}
                                    </p>
                                </div>
                            </label> */}
                        </div>
                    </div>

                    {/* Place Order Button */}
                    <button
                        onClick={handlePlaceOrder}
                        disabled={placing || !validateAddresses()}
                        className={`w-full py-4 px-6 rounded-lg font-semibold text-white transition-colors flex items-center justify-center gap-2 ${
                            placing || !validateAddresses()
                                ? 'bg-gray-400 cursor-not-allowed dark:bg-gray-600'
                                : 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600'
                        }`}
                    >
                        {placing ? (
                            <>
                                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                {t('checkout.placing')}
                            </>
                        ) : (
                            <>
                                <ShoppingCart className="w-5 h-5" />
                                {t('checkout.place_order')}
                            </>
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}

