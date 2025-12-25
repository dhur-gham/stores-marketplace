import { useState, useRef, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { User, LogOut, ChevronDown, Globe, ShoppingCart, Package, Heart } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import { useWishlist } from '../contexts/WishlistContext';
import { useLanguage } from '../hooks/useLanguage';

export default function Header() {
    const { t, i18n } = useTranslation();
    const { authenticated, customer, logout } = useAuth();
    const { cart_count } = useCart();
    const { wishlist_count } = useWishlist();
    const { is_rtl } = useLanguage();
    const [dropdown_open, setDropdownOpen] = useState(false);
    const [lang_dropdown_open, setLangDropdownOpen] = useState(false);
    const dropdown_ref = useRef(null);
    const lang_dropdown_ref = useRef(null);

    const languages = [
        { code: 'en', name: 'English', flag: '🇬🇧' },
        { code: 'ar', name: 'العربية', flag: '🇸🇦' },
    ];

    // Close dropdowns when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdown_ref.current && !dropdown_ref.current.contains(event.target)) {
                setDropdownOpen(false);
            }
            if (lang_dropdown_ref.current && !lang_dropdown_ref.current.contains(event.target)) {
                setLangDropdownOpen(false);
            }
        };

        if (dropdown_open || lang_dropdown_open) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [dropdown_open, lang_dropdown_open]);

    const changeLanguage = (lang_code) => {
        i18n.changeLanguage(lang_code);
        setLangDropdownOpen(false);
    };

    // Get user initials for avatar
    const getInitials = (name) => {
        if (!name) return 'U';
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    return (
        <header className="sticky top-0 z-50 w-full py-4 md:py-6 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 backdrop-blur-sm bg-opacity-95 dark:bg-opacity-95">
            <div className="container mx-auto px-3 md:px-4 flex items-center justify-between gap-2">
                <Link to="/" className="flex-1 text-center min-w-0">
                    <h1 className="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate">
                        {t('header.title')}
                    </h1>
                </Link>
                <div className="flex-shrink-0 flex items-center gap-1.5 sm:gap-2 md:gap-3">
                    {/* Wishlist Icon - Only show when authenticated */}
                    {authenticated && (
                        <Link
                            to="/wishlist"
                            className="relative p-1.5 sm:p-2 text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                            title={t('wishlist.title')}
                        >
                            <Heart className="w-4 h-4 sm:w-5 sm:h-5" />
                            {wishlist_count > 0 && (
                                <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                    {wishlist_count > 99 ? '99+' : wishlist_count}
                                </span>
                            )}
                        </Link>
                    )}
                    {/* Cart Icon - Only show when authenticated */}
                    {authenticated && (
                        <Link
                            to="/cart"
                            className="relative p-1.5 sm:p-2 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                            title={t('cart.title')}
                        >
                            <ShoppingCart className="w-4 h-4 sm:w-5 sm:h-5" />
                            {cart_count > 0 && (
                                <span className="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                    {cart_count > 99 ? '99+' : cart_count}
                                </span>
                            )}
                        </Link>
                    )}
                    {/* Language Switcher - Always visible */}
                    <div className="relative" ref={lang_dropdown_ref}>
                        <button
                            onClick={() => setLangDropdownOpen(!lang_dropdown_open)}
                            className="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                            aria-label="Language menu"
                        >
                            <Globe className="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-600 dark:text-gray-300" />
                            <span className="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">
                                {languages.find((l) => l.code === i18n.language)?.flag || '🌐'}
                            </span>
                            <ChevronDown
                                className={`w-2.5 h-2.5 sm:w-3 sm:h-3 text-gray-500 dark:text-gray-400 transition-transform ${
                                    lang_dropdown_open ? 'rotate-180' : ''
                                }`}
                            />
                        </button>

                        {lang_dropdown_open && (
                            <div className="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                                {languages.map((lang) => (
                                    <button
                                        key={lang.code}
                                        onClick={() => changeLanguage(lang.code)}
                                        className={`w-full px-4 py-2 text-left text-sm flex items-center gap-3 transition-colors ${
                                            i18n.language === lang.code
                                                ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'
                                        }`}
                                    >
                                        <span className="text-lg">{lang.flag}</span>
                                        <span>{lang.name}</span>
                                        {i18n.language === lang.code && (
                                            <span className="ml-auto text-blue-600 dark:text-blue-400">✓</span>
                                        )}
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    {authenticated ? (
                        <div className="relative" ref={dropdown_ref}>
                            <button
                                onClick={() => setDropdownOpen(!dropdown_open)}
                                className="flex items-center gap-1 sm:gap-2 p-1 sm:p-2 rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                aria-label="User menu"
                            >
                                <div className="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-600 dark:bg-blue-500 flex items-center justify-center text-white font-semibold text-xs sm:text-sm">
                                    {getInitials(customer?.name)}
                                </div>
                                <ChevronDown
                                    className={`w-3 h-3 sm:w-4 sm:h-4 text-gray-600 dark:text-gray-300 transition-transform hidden sm:block ${
                                        dropdown_open ? 'rotate-180' : ''
                                    }`}
                                />
                            </button>

                            {dropdown_open && (
                                <div className="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                                    <div className="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                        <p className="text-sm font-medium text-gray-900 dark:text-white">
                                            {customer?.name}
                                        </p>
                                        {customer?.email && (
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                                                {customer.email}
                                            </p>
                                        )}
                                    </div>
                                    <Link
                                        to="/orders"
                                        onClick={() => setDropdownOpen(false)}
                                        className="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center gap-2 transition-colors"
                                    >
                                        <Package className="w-4 h-4" />
                                        {t('header.orders')}
                                    </Link>
                                    <button
                                        onClick={() => {
                                            setDropdownOpen(false);
                                            logout();
                                        }}
                                        className="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2 transition-colors"
                                    >
                                        <LogOut className="w-4 h-4" />
                                        {t('header.logout')}
                                    </button>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="flex items-center gap-1.5 sm:gap-2 md:gap-3">
                            <Link
                                to="/login"
                                className="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors whitespace-nowrap"
                            >
                                {t('header.login')}
                            </Link>
                            <Link
                                to="/register"
                                className="px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors whitespace-nowrap"
                            >
                                {t('header.register')}
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}

