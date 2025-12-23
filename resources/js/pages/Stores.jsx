import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Search, X, Store as StoreIcon, Package } from 'lucide-react';
import Header from '../components/Header';
import { fetchStores } from '../services/api';

export default function Stores() {
    const { t } = useTranslation();
    const [stores, setStores] = useState([]);
    const [loading, setLoading] = useState(true);
    const [search_term, setSearchTerm] = useState('');
    const [debounced_search, setDebouncedSearch] = useState('');
    const [current_page, setCurrentPage] = useState(1);
    const [total_pages, setTotalPages] = useState(1);
    const [total, setTotal] = useState(0);

    // Debounce search input
    useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(search_term);
            setCurrentPage(1); // Reset to first page on new search
        }, 300);

        return () => clearTimeout(timer);
    }, [search_term]);

    // Fetch stores when search or page changes
    useEffect(() => {
        const loadStores = async () => {
            try {
                setLoading(true);
                const response = await fetchStores(debounced_search || null, 12, current_page);
                
                if (response.status && response.data) {
                    setStores(response.data);
                    setTotalPages(response.meta?.last_page || 1);
                    setTotal(response.meta?.total || 0);
                }
            } catch (error) {
                console.error('Error loading stores:', error);
            } finally {
                setLoading(false);
            }
        };

        loadStores();
    }, [debounced_search, current_page]);

    const clearSearch = () => {
        setSearchTerm('');
    };

    const getInitials = (name) => {
        if (!name) return 'S';
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                
                <div className="max-w-7xl mx-auto mt-12">
                    {/* Page Header */}
                    <div className="mb-8">
                        <h1 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2">
                            {t('stores.all_stores')}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            {t('stores.discover_stores')}
                        </p>
                    </div>

                    {/* Search Bar */}
                    <div className="mb-8">
                        <div className="relative max-w-2xl mx-auto">
                            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Search className="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="text"
                                value={search_term}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder={t('stores.search_placeholder')}
                                className="w-full pl-12 pr-12 py-4 text-lg bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white transition-all shadow-sm hover:shadow-md"
                            />
                            {search_term && (
                                <button
                                    onClick={clearSearch}
                                    className="absolute inset-y-0 right-0 pr-4 flex items-center"
                                >
                                    <X className="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" />
                                </button>
                            )}
                        </div>
                    </div>

                    {/* Results Count */}
                    {!loading && (
                        <div className="mb-6 text-sm text-gray-600 dark:text-gray-400">
                            {total > 0 ? (
                                <span>
                                    {t('stores.found_stores')} <span className="font-semibold text-gray-900 dark:text-white">{total}</span> {total !== 1 ? t('stores.stores') : t('stores.store')}
                                    {debounced_search && (
                                        <span> {t('stores.matching')} "<span className="font-semibold">{debounced_search}</span>"</span>
                                    )}
                                </span>
                            ) : (
                                <span>{t('stores.no_stores_found')}</span>
                            )}
                        </div>
                    )}

                    {/* Loading State */}
                    {loading ? (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            {[...Array(8)].map((_, i) => (
                                <div
                                    key={i}
                                    className="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm animate-pulse"
                                >
                                    <div className="w-20 h-20 rounded-full bg-gray-200 dark:bg-gray-700 mx-auto mb-4" />
                                    <div className="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mx-auto mb-2" />
                                    <div className="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mx-auto" />
                                </div>
                            ))}
                        </div>
                    ) : stores.length > 0 ? (
                        <>
                            {/* Stores Grid */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                                {stores.map((store) => (
                                    <Link
                                        key={store.id}
                                        to={`/store/${store.id}`}
                                        className="group bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 dark:border-gray-700"
                                    >
                                        <div className="flex flex-col items-center text-center">
                                            <div className="w-20 h-20 rounded-full overflow-hidden bg-gradient-to-br from-blue-500 to-purple-600 mb-4 border-4 border-white dark:border-gray-800 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                                {store.image ? (
                                                    <img
                                                        src={store.image}
                                                        alt={store.name}
                                                        className="w-full h-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="w-full h-full flex items-center justify-center text-white font-bold text-xl">
                                                        {getInitials(store.name)}
                                                    </div>
                                                )}
                                            </div>
                                            <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                {store.name}
                                            </h3>
                                            {store.bio && (
                                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                                    {store.bio}
                                                </p>
                                            )}
                                            <div className="flex items-center gap-4 mt-auto">
                                                <div className="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                                                    <Package className="w-4 h-4" />
                                                    <span className="font-medium">{store.products_count}</span>
                                                </div>
                                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                    store.type === 'digital' 
                                                        ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                                }`}>
                                                    {store.type === 'digital' ? t('stores.digital') : t('stores.physical')}
                                                </span>
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>

                            {/* Pagination */}
                            {total_pages > 1 && (
                                <div className="flex justify-center items-center gap-2 mt-8">
                                    <button
                                        onClick={() => setCurrentPage((prev) => Math.max(1, prev - 1))}
                                        disabled={current_page === 1}
                                        className="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        {t('common.previous')}
                                    </button>
                                    <span className="px-4 py-2 text-gray-700 dark:text-gray-300">
                                        {t('common.page')} {current_page} {t('common.of')} {total_pages}
                                    </span>
                                    <button
                                        onClick={() => setCurrentPage((prev) => Math.min(total_pages, prev + 1))}
                                        disabled={current_page === total_pages}
                                        className="px-4 py-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        {t('common.next')}
                                    </button>
                                </div>
                            )}
                        </>
                    ) : (
                        /* Empty State */
                        <div className="text-center py-16">
                            <div className="text-6xl mb-4">🛍️</div>
                            <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {t('stores.no_stores_found')}
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400 mb-6">
                                {debounced_search 
                                    ? `${t('stores.no_stores_matching')} "${debounced_search}"`
                                    : t('stores.no_stores_available')
                                }
                            </p>
                            {debounced_search && (
                                <button
                                    onClick={clearSearch}
                                    className="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors"
                                >
                                    {t('stores.clear_search')}
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

