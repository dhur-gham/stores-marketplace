import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { ArrowLeft, User, MapPin, Edit2, Trash2, Save, X, Plus, MessageCircle, ExternalLink } from 'lucide-react';
import { Link } from 'react-router-dom';
import Header from '../components/Header';
import { useAuth } from '../contexts/AuthContext';
import { fetchCities, getSavedAddresses, createSavedAddress, updateSavedAddress, deleteSavedAddress, getTelegramActivationLink } from '../services/api';

export default function Settings() {
    const { t } = useTranslation();
    const { customer } = useAuth();
    const [active_section, setActiveSection] = useState('profile'); // 'profile' or 'addresses'
    const [cities, setCities] = useState([]);
    const [loading_cities, setLoadingCities] = useState(true);
    const [saved_addresses, setSavedAddresses] = useState([]);
    const [loading_addresses, setLoadingAddresses] = useState(true);
    const [editing_address_id, setEditingAddressId] = useState(null);
    const [show_add_address, setShowAddAddress] = useState(false);
    const [saving_address, setSavingAddress] = useState(false);
    const [deleting_address_id, setDeletingAddressId] = useState(null);
    const [error, setError] = useState(null);
    const [telegram_link, setTelegramLink] = useState(null);
    const [telegram_activated, setTelegramActivated] = useState(false);
    const [loading_telegram, setLoadingTelegram] = useState(true);
    
    // Form state for new/editing address
    const [address_form, setAddressForm] = useState({
        label: '',
        city_id: '',
        address: '',
        is_default: false,
    });

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

    useEffect(() => {
        const loadSavedAddresses = async () => {
            if (customer) {
                try {
                    setLoadingAddresses(true);
                    const response = await getSavedAddresses();
                    if (response.status && response.data) {
                        setSavedAddresses(response.data);
                    }
                } catch (error) {
                    console.error('Error loading saved addresses:', error);
                } finally {
                    setLoadingAddresses(false);
                }
            }
        };
        loadSavedAddresses();
    }, [customer]);

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

    const handleEditAddress = (address) => {
        setEditingAddressId(address.id);
        setAddressForm({
            label: address.label,
            city_id: String(address.city_id),
            address: address.address,
            is_default: address.is_default,
        });
        setError(null);
    };

    const handleCancelEdit = () => {
        setEditingAddressId(null);
        setShowAddAddress(false);
        setAddressForm({
            label: '',
            city_id: '',
            address: '',
            is_default: false,
        });
        setError(null);
    };

    const handleSaveAddress = async () => {
        if (!address_form.label || address_form.label.trim() === '') {
            setError(t('settings.addresses.label_required'));
            return;
        }

        if (!address_form.city_id) {
            setError(t('settings.addresses.city_required'));
            return;
        }

        if (!address_form.address || address_form.address.trim() === '') {
            setError(t('settings.addresses.address_required'));
            return;
        }

        setSavingAddress(true);
        setError(null);

        try {
            const address_data = {
                label: address_form.label.trim(),
                city_id: parseInt(address_form.city_id),
                address: address_form.address.trim(),
                is_default: address_form.is_default,
            };

            let response;
            if (editing_address_id) {
                response = await updateSavedAddress(editing_address_id, address_data);
            } else {
                response = await createSavedAddress(address_data);
            }

            if (response.status) {
                // Reload addresses
                const addresses_response = await getSavedAddresses();
                if (addresses_response.status && addresses_response.data) {
                    setSavedAddresses(addresses_response.data);
                }
                handleCancelEdit();
            }
        } catch (error) {
            console.error('Error saving address:', error);
            setError(error.response?.data?.message || t('settings.addresses.save_failed'));
        } finally {
            setSavingAddress(false);
        }
    };

    const handleDeleteAddress = async (address_id) => {
        if (!window.confirm(t('settings.addresses.confirm_delete'))) {
            return;
        }

        setDeletingAddressId(address_id);
        setError(null);

        try {
            const response = await deleteSavedAddress(address_id);
            if (response.status) {
                // Reload addresses
                const addresses_response = await getSavedAddresses();
                if (addresses_response.status && addresses_response.data) {
                    setSavedAddresses(addresses_response.data);
                }
            }
        } catch (error) {
            console.error('Error deleting address:', error);
            setError(error.response?.data?.message || t('settings.addresses.delete_failed'));
        } finally {
            setDeletingAddressId(null);
        }
    };

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
                            <span className="text-base sm:text-sm font-medium">{t('common.back')}</span>
                        </Link>
                    </div>

                    <div className="mb-6">
                        <h1 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                            <User className="w-8 h-8" />
                            {t('settings.title')}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">{t('settings.description')}</p>
                    </div>

                    {/* Section Selector Dropdown */}
                    <div className="mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <label className="block text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            {t('settings.select_section')}
                        </label>
                        <select
                            value={active_section}
                            onChange={(e) => {
                                setActiveSection(e.target.value);
                                handleCancelEdit(); // Reset form when switching sections
                            }}
                            className="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-medium"
                        >
                            <option value="profile">{t('settings.sections.profile')}</option>
                            <option value="addresses">{t('settings.sections.addresses')}</option>
                        </select>
                    </div>

                    {error && (
                        <div className="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p className="text-red-600 dark:text-red-400">{error}</p>
                        </div>
                    )}

                    {/* Profile Section (Read-only) */}
                    {active_section === 'profile' && (
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                <User className="w-5 h-5" />
                                {t('settings.profile.title')}
                            </h2>
                            <div className="space-y-4">
                                <div className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <label className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {t('settings.profile.name')}
                                    </label>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">
                                        {customer?.name || t('common.no_data')}
                                    </p>
                                </div>
                                <div className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <label className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {t('settings.profile.email')}
                                    </label>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">
                                        {customer?.email || t('common.no_data')}
                                    </p>
                                </div>
                                <div className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <label className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {t('settings.profile.phone')}
                                    </label>
                                    <p className="text-base font-medium text-gray-900 dark:text-white">
                                        {customer?.phone || t('common.no_data')}
                                    </p>
                                </div>
                                {customer?.city && (
                                    <div className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <label className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            {t('settings.profile.city')}
                                        </label>
                                        <p className="text-base font-medium text-gray-900 dark:text-white">
                                            {customer.city.name}
                                        </p>
                                    </div>
                                )}
                                {customer?.address && (
                                    <div className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <label className="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                            {t('settings.profile.address')}
                                        </label>
                                        <p className="text-base font-medium text-gray-900 dark:text-white">
                                            {customer.address}
                                        </p>
                                    </div>
                                )}
                            </div>

                            {/* Telegram Activation Banner */}
                            {!loading_telegram && !telegram_activated && (
                                <div className="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
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
                        </div>
                    )}

                    {/* Saved Addresses Section (Editable) */}
                    {active_section === 'addresses' && (
                        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div className="flex items-center justify-between mb-6">
                                <h2 className="text-xl font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <MapPin className="w-5 h-5" />
                                    {t('settings.addresses.title')}
                                </h2>
                                {!show_add_address && !editing_address_id && (
                                    <button
                                        onClick={() => {
                                            setShowAddAddress(true);
                                            setAddressForm({
                                                label: '',
                                                city_id: '',
                                                address: '',
                                                is_default: saved_addresses.length === 0,
                                            });
                                            setError(null);
                                        }}
                                        className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                    >
                                        <Plus className="w-4 h-4" />
                                        {t('settings.addresses.add_new')}
                                    </button>
                                )}
                            </div>

                            {loading_addresses ? (
                                <div className="text-center py-8">
                                    <p className="text-gray-500 dark:text-gray-400">{t('common.loading')}</p>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {/* Add/Edit Address Form */}
                                    {(show_add_address || editing_address_id) && (
                                        <div className="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-blue-200 dark:border-blue-800">
                                            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                                {editing_address_id
                                                    ? t('settings.addresses.edit_address')
                                                    : t('settings.addresses.add_address')}
                                            </h3>
                                            <div className="space-y-4">
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        {t('settings.addresses.label')} *
                                                    </label>
                                                    <input
                                                        type="text"
                                                        value={address_form.label}
                                                        onChange={(e) =>
                                                            setAddressForm({ ...address_form, label: e.target.value })
                                                        }
                                                        placeholder={t('settings.addresses.label_placeholder')}
                                                        className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        disabled={saving_address}
                                                    />
                                                </div>
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        {t('settings.addresses.city')} *
                                                    </label>
                                                    <select
                                                        value={address_form.city_id}
                                                        onChange={(e) =>
                                                            setAddressForm({ ...address_form, city_id: e.target.value })
                                                        }
                                                        className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        disabled={saving_address || loading_cities}
                                                    >
                                                        <option value="">{t('settings.addresses.select_city')}</option>
                                                        {cities.map((city) => (
                                                            <option key={city.id} value={city.id}>
                                                                {city.name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                        {t('settings.addresses.address')} *
                                                    </label>
                                                    <textarea
                                                        value={address_form.address}
                                                        onChange={(e) =>
                                                            setAddressForm({ ...address_form, address: e.target.value })
                                                        }
                                                        rows={3}
                                                        placeholder={t('settings.addresses.address_placeholder')}
                                                        className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        disabled={saving_address}
                                                    />
                                                </div>
                                                <div className="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        id="is_default"
                                                        checked={address_form.is_default}
                                                        onChange={(e) =>
                                                            setAddressForm({ ...address_form, is_default: e.target.checked })
                                                        }
                                                        className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                        disabled={saving_address}
                                                    />
                                                    <label htmlFor="is_default" className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                        {t('settings.addresses.set_as_default')}
                                                    </label>
                                                </div>
                                                <div className="flex gap-2">
                                                    <button
                                                        onClick={handleSaveAddress}
                                                        disabled={saving_address}
                                                        className="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    >
                                                        <Save className="w-4 h-4" />
                                                        {saving_address ? t('common.saving') : t('common.confirm')}
                                                    </button>
                                                    <button
                                                        onClick={handleCancelEdit}
                                                        disabled={saving_address}
                                                        className="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    >
                                                        <X className="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    {/* Addresses List */}
                                    {saved_addresses.length === 0 && !show_add_address && !editing_address_id ? (
                                        <div className="text-center py-8">
                                            <MapPin className="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-4" />
                                            <p className="text-gray-500 dark:text-gray-400 mb-4">
                                                {t('settings.addresses.no_addresses')}
                                            </p>
                                            <button
                                                onClick={() => {
                                                    setShowAddAddress(true);
                                                    setAddressForm({
                                                        label: '',
                                                        city_id: '',
                                                        address: '',
                                                        is_default: true,
                                                    });
                                                }}
                                                className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                            >
                                                <Plus className="w-4 h-4" />
                                                {t('settings.addresses.add_first')}
                                            </button>
                                        </div>
                                    ) : (
                                        saved_addresses.map((address) => (
                                            <div
                                                key={address.id}
                                                className={`p-4 rounded-lg border ${
                                                    address.is_default
                                                        ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800'
                                                        : 'bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-700'
                                                }`}
                                            >
                                                {editing_address_id === address.id ? null : (
                                                    <div className="flex items-start justify-between gap-4">
                                                        <div className="flex-1">
                                                            <div className="flex items-center gap-2 mb-2">
                                                                <h3 className="font-semibold text-gray-900 dark:text-white">
                                                                    {address.label}
                                                                </h3>
                                                                {address.is_default && (
                                                                    <span className="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                                                                        {t('settings.addresses.default')}
                                                                    </span>
                                                                )}
                                                            </div>
                                                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                                {address.city?.name || ''}
                                                            </p>
                                                            <p className="text-sm text-gray-700 dark:text-gray-300">
                                                                {address.address}
                                                            </p>
                                                        </div>
                                                        <div className="flex items-center gap-2">
                                                            <button
                                                                onClick={() => handleEditAddress(address)}
                                                                className="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                                title={t('settings.addresses.edit')}
                                                            >
                                                                <Edit2 className="w-4 h-4" />
                                                            </button>
                                                            <button
                                                                onClick={() => handleDeleteAddress(address.id)}
                                                                disabled={deleting_address_id === address.id}
                                                                className="p-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors disabled:opacity-50"
                                                                title={t('settings.addresses.delete')}
                                                            >
                                                                {deleting_address_id === address.id ? (
                                                                    <div className="w-4 h-4 border-2 border-red-600 border-t-transparent rounded-full animate-spin" />
                                                                ) : (
                                                                    <Trash2 className="w-4 h-4" />
                                                                )}
                                                            </button>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ))
                                    )}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

