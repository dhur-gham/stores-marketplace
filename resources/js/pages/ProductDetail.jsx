import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Header from '../components/Header';
import ArrowIcon from '../components/ArrowIcon';
import { fetchProduct } from '../services/api';

export default function ProductDetail() {
    const { t } = useTranslation();
    const { productId } = useParams();
    const navigate = useNavigate();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadProduct = async () => {
            try {
                setLoading(true);
                const productResponse = await fetchProduct(productId);

                if (productResponse.status && productResponse.data) {
                    setProduct(productResponse.data);
                } else {
                    navigate('/');
                }
                setLoading(false);
            } catch (error) {
                console.error('Error loading product:', error);
                setLoading(false);
                navigate('/');
            }
        };

        if (productId) {
            loadProduct();
        }
    }, [productId, navigate]);

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
                    <div className="flex items-center justify-center min-h-[60vh]">
                        <div className="text-center">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                            <p className="text-gray-600 dark:text-gray-400">{t('product.loading')}</p>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    if (!product) {
        return null;
    }

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                
                <button
                    onClick={() => navigate(-1)}
                    className="mb-6 flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                >
                    <ArrowIcon className="w-5 h-5 me-2" />
                    {t('common.back')}
                </button>

                <div className="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden mb-8">
                    <div className="md:flex">
                        <div className="md:w-1/2">
                            <div className="aspect-square w-full bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                                {product.image ? (
                                    <img
                                        src={product.image}
                                        alt={product.name}
                                        className="w-full h-full object-cover"
                                    />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                        <svg className="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                )}
                            </div>
                        </div>
                        <div className="md:w-1/2 p-6 md:p-8">
                            <div className="mb-4">
                                {product.store && (
                                    <Link
                                        to={`/store/${product.store.id}`}
                                        className="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors"
                                    >
                                        {product.store.image && (
                                            <img
                                                src={product.store.image}
                                                alt={product.store.name}
                                                className="w-6 h-6 rounded-full object-cover"
                                            />
                                        )}
                                        <span>{product.store.name}</span>
                                    </Link>
                                )}
                            </div>
                            
                            <h1 className="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                                {product.name}
                            </h1>

                            {product.sku && (
                                <p className="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    {t('product.sku')}: {product.sku}
                                </p>
                            )}

                            <div className="mb-6">
                                <span className="text-4xl font-bold text-gray-900 dark:text-white">
                                    {formatPrice(product.price)}
                                </span>
                            </div>

                            {product.description && (
                                <div className="mb-6">
                                    <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                        {t('product.description')}
                                    </h2>
                                    <p className="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line">
                                        {product.description}
                                    </p>
                                </div>
                            )}

                            {product.stock > 0 && product.stock < 5 && (
                                <div className="border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <div className="flex items-center gap-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-3">
                                        <svg className="w-5 h-5 text-orange-600 dark:text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span className="font-semibold text-orange-700 dark:text-orange-300">{t('product.low_stock')}</span>
                                    </div>
                                </div>
                            )}

                            <div className="mt-8">
                                <button
                                    disabled={product.stock === 0}
                                    className={`w-full py-3 px-6 rounded-lg font-semibold text-white transition-colors ${
                                        product.stock > 0
                                            ? 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600'
                                            : 'bg-gray-400 cursor-not-allowed dark:bg-gray-600'
                                    }`}
                                >
                                    {product.stock > 0 ? t('product.add_to_cart') : t('product.out_of_stock')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

