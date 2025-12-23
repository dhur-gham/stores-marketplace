import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Header from '../components/Header';
import ProductsList from '../components/ProductsList';
import ArrowIcon from '../components/ArrowIcon';
import { fetchStore, fetchStoreProducts } from '../services/api';

export default function StoreDetail() {
    const { t } = useTranslation();
    const { storeId } = useParams();
    const navigate = useNavigate();
    const [store, setStore] = useState(null);
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [productsLoading, setProductsLoading] = useState(true);

    useEffect(() => {
        const loadStoreData = async () => {
            try {
                setLoading(true);
                setProductsLoading(true);

                const [storeResponse, productsResponse] = await Promise.all([
                    fetchStore(storeId),
                    fetchStoreProducts(storeId, 50),
                ]);

                if (storeResponse.status && storeResponse.data) {
                    setStore(storeResponse.data);
                } else {
                    navigate('/');
                }
                setLoading(false);

                if (productsResponse.status && productsResponse.data) {
                    setProducts(productsResponse.data);
                }
                setProductsLoading(false);
            } catch (error) {
                console.error('Error loading store data:', error);
                setLoading(false);
                setProductsLoading(false);
                navigate('/');
            }
        };

        if (storeId) {
            loadStoreData();
        }
    }, [storeId, navigate]);

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto px-4 py-8">
                    <Header />
                    <div className="flex items-center justify-center min-h-[60vh]">
                        <div className="text-center">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                            <p className="text-gray-600 dark:text-gray-400">{t('store.loading')}</p>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    if (!store) {
        return null;
    }

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                
                <button
                    onClick={() => navigate('/')}
                    className="mb-6 flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                >
                    <ArrowIcon className="w-5 h-5 me-2" />
                    {t('store.back_to_stores')}
                </button>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
                    <div className="md:flex">
                        <div className="md:w-1/3">
                            <div className="aspect-square w-full bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                                {store.image ? (
                                    <img
                                        src={store.image}
                                        alt={store.name}
                                        className="w-full h-full object-cover"
                                    />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg className="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                )}
                            </div>
                        </div>
                        <div className="md:w-2/3 p-6 md:p-8">
                            <div className="mb-4">
                                <span className={`inline-block px-3 py-1 text-sm font-medium rounded-full ${
                                    store.type === 'digital' 
                                        ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' 
                                        : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                }`}>
                                    {store.type === 'digital' ? t('store.digital_store') : t('store.physical_store')}
                                </span>
                            </div>
                            <h1 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                                {store.name}
                            </h1>
                            {store.bio && (
                                <p className="text-lg text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                                    {store.bio}
                                </p>
                            )}
                            <div className="flex items-center text-gray-600 dark:text-gray-400">
                                <svg className="w-5 h-5 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span className="font-medium">
                                    {store.products_count} {store.products_count === 1 ? t('store.product') : t('store.products')}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <section>
                    <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        {t('store.products')}
                    </h2>
                    {products.length === 0 && !productsLoading ? (
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
                            <svg className="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p className="text-lg text-gray-600 dark:text-gray-400">{t('store.no_products')}</p>
                        </div>
                    ) : (
                        <ProductsList products={products} loading={productsLoading} />
                    )}
                </section>
            </div>
        </div>
    );
}

