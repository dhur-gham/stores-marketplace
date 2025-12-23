import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { Search, X, Filter, ChevronDown } from 'lucide-react';
import Header from '../components/Header';
import ProductsList from '../components/ProductsList';
import { fetchAllProducts, fetchStores } from '../services/api';

export default function Products() {
    const { t } = useTranslation();
    const [products, setProducts] = useState([]);
    const [stores, setStores] = useState([]);
    const [loading, setLoading] = useState(true);
    const [storesLoading, setStoresLoading] = useState(true);
    const [search_term, setSearchTerm] = useState('');
    const [debounced_search, setDebouncedSearch] = useState('');
    const [current_page, setCurrentPage] = useState(1);
    const [total_pages, setTotalPages] = useState(1);
    const [total, setTotal] = useState(0);
    
    // Filters
    const [store_id, setStoreId] = useState('');
    const [type, setType] = useState('');
    const [price_min, setPriceMin] = useState('');
    const [price_max, setPriceMax] = useState('');
    const [sort_by, setSortBy] = useState('created_at');
    const [sort_order, setSortOrder] = useState('desc');
    const [show_filters, setShowFilters] = useState(false);

    // Debounce search input
    useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(search_term);
            setCurrentPage(1);
        }, 300);

        return () => clearTimeout(timer);
    }, [search_term]);

    // Load stores for filter dropdown
    useEffect(() => {
        const loadStores = async () => {
            try {
                setStoresLoading(true);
                const response = await fetchStores();
                if (response.status && response.data) {
                    setStores(response.data);
                }
            } catch (error) {
                console.error('Error loading stores:', error);
            } finally {
                setStoresLoading(false);
            }
        };

        loadStores();
    }, []);

    // Fetch products when filters, search, or page changes
    useEffect(() => {
        const loadProducts = async () => {
            try {
                setLoading(true);
                const params = {
                    search: debounced_search || null,
                    store_id: store_id ? parseInt(store_id) : null,
                    type: type || null,
                    price_min: price_min ? parseInt(price_min) : null,
                    price_max: price_max ? parseInt(price_max) : null,
                    sort_by: sort_by || null,
                    sort_order: sort_order || 'desc',
                    per_page: 12,
                    page: current_page,
                };
                
                const response = await fetchAllProducts(params);
                
                if (response.status && response.data) {
                    setProducts(response.data);
                    setTotalPages(response.meta?.last_page || 1);
                    setTotal(response.meta?.total || 0);
                }
            } catch (error) {
                console.error('Error loading products:', error);
            } finally {
                setLoading(false);
            }
        };

        loadProducts();
    }, [debounced_search, store_id, type, price_min, price_max, sort_by, sort_order, current_page]);

    const clearSearch = () => {
        setSearchTerm('');
    };

    const clearFilters = () => {
        setStoreId('');
        setType('');
        setPriceMin('');
        setPriceMax('');
        setSortBy('created_at');
        setSortOrder('desc');
    };

    const hasActiveFilters = store_id || type || price_min || price_max;

    const handleSortChange = (value) => {
        if (value === 'created_at-desc') {
            setSortBy('created_at');
            setSortOrder('desc');
        } else if (value === 'name-asc') {
            setSortBy('name');
            setSortOrder('asc');
        } else if (value === 'name-desc') {
            setSortBy('name');
            setSortOrder('desc');
        } else if (value === 'price-asc') {
            setSortBy('price');
            setSortOrder('asc');
        } else if (value === 'price-desc') {
            setSortBy('price');
            setSortOrder('desc');
        } else if (value === 'store_name-asc') {
            setSortBy('store_name');
            setSortOrder('asc');
        }
    };

    const getSortValue = () => {
        if (sort_by === 'created_at' && sort_order === 'desc') return 'created_at-desc';
        if (sort_by === 'name' && sort_order === 'asc') return 'name-asc';
        if (sort_by === 'name' && sort_order === 'desc') return 'name-desc';
        if (sort_by === 'price' && sort_order === 'asc') return 'price-asc';
        if (sort_by === 'price' && sort_order === 'desc') return 'price-desc';
        if (sort_by === 'store_name' && sort_order === 'asc') return 'store_name-asc';
        return 'created_at-desc';
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                
                <div className="max-w-7xl mx-auto mt-12">
                    {/* Page Header */}
                    <div className="mb-8">
                        <h1 className="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-2">
                            {t('products.all_products')}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            {t('products.discover_products')}
                        </p>
                    </div>

                    {/* Search Bar and Sort */}
                    <div className="mb-6 flex flex-col md:flex-row gap-4">
                        <div className="flex-1 relative">
                            <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <Search className="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="text"
                                value={search_term}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                placeholder={t('products.search_placeholder')}
                                className="w-full pl-12 pr-12 py-3 text-lg bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white transition-all shadow-sm hover:shadow-md"
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
                        <div className="flex gap-3">
                            <button
                                onClick={() => setShowFilters(!show_filters)}
                                className={`px-4 py-3 flex items-center gap-2 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors ${
                                    hasActiveFilters ? 'border-blue-500 dark:border-blue-400' : ''
                                }`}
                            >
                                <Filter className="h-5 w-5 text-gray-600 dark:text-gray-300" />
                                <span className="font-medium text-gray-700 dark:text-gray-300">{t('products.filters')}</span>
                                {hasActiveFilters && (
                                    <span className="w-2 h-2 bg-blue-500 rounded-full"></span>
                                )}
                            </button>
                            <div className="relative">
                                <select
                                    value={getSortValue()}
                                    onChange={(e) => handleSortChange(e.target.value)}
                                    className="appearance-none px-4 py-3 pr-10 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer"
                                >
                                    <option value="created_at-desc">{t('products.sort_latest')}</option>
                                    <option value="name-asc">{t('products.sort_name_asc')}</option>
                                    <option value="name-desc">{t('products.sort_name_desc')}</option>
                                    <option value="price-asc">{t('products.sort_price_asc')}</option>
                                    <option value="price-desc">{t('products.sort_price_desc')}</option>
                                    <option value="store_name-asc">{t('products.sort_store_name')}</option>
                                </select>
                                <ChevronDown className="absolute right-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none" />
                            </div>
                        </div>
                    </div>

                    {/* Filters Panel */}
                    {show_filters && (
                        <div className="mb-6 p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {/* Store Filter */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {t('products.filter_by_store')}
                                    </label>
                                    <select
                                        value={store_id}
                                        onChange={(e) => {
                                            setStoreId(e.target.value);
                                            setCurrentPage(1);
                                        }}
                                        className="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">{t('products.all_stores')}</option>
                                        {stores.map((store) => (
                                            <option key={store.id} value={store.id}>
                                                {store.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Type Filter */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {t('products.filter_by_type')}
                                    </label>
                                    <select
                                        value={type}
                                        onChange={(e) => {
                                            setType(e.target.value);
                                            setCurrentPage(1);
                                        }}
                                        className="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">{t('products.all_types')}</option>
                                        <option value="digital">{t('products.type_digital')}</option>
                                        <option value="physical">{t('products.type_physical')}</option>
                                    </select>
                                </div>

                                {/* Price Range */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {t('products.price_range')}
                                    </label>
                                    <div className="flex gap-2">
                                        <input
                                            type="number"
                                            value={price_min}
                                            onChange={(e) => {
                                                setPriceMin(e.target.value);
                                                setCurrentPage(1);
                                            }}
                                            placeholder={t('products.min_price')}
                                            className="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                        <input
                                            type="number"
                                            value={price_max}
                                            onChange={(e) => {
                                                setPriceMax(e.target.value);
                                                setCurrentPage(1);
                                            }}
                                            placeholder={t('products.max_price')}
                                            className="flex-1 px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>
                                </div>
                            </div>
                            {hasActiveFilters && (
                                <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <button
                                        onClick={clearFilters}
                                        className="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                                    >
                                        {t('products.clear_filters')}
                                    </button>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Results Count */}
                    {!loading && (
                        <div className="mb-6 text-sm text-gray-600 dark:text-gray-400">
                            {total > 0 ? (
                                <span>
                                    {t('products.found_products')} <span className="font-semibold text-gray-900 dark:text-white">{total}</span> {total !== 1 ? t('products.products') : t('products.product')}
                                    {debounced_search && (
                                        <span> {t('products.matching')} "<span className="font-semibold">{debounced_search}</span>"</span>
                                    )}
                                </span>
                            ) : (
                                <span>{t('products.no_products_found')}</span>
                            )}
                        </div>
                    )}

                    {/* Products Grid */}
                    <ProductsList products={products} loading={loading} />

                    {/* Pagination */}
                    {!loading && total_pages > 1 && (
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
                </div>
            </div>
        </div>
    );
}

