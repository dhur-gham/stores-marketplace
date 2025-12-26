import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ShoppingCart, Plus, Minus, Check, Heart } from 'lucide-react';
import Header from '../components/Header';
import ArrowIcon from '../components/ArrowIcon';
import { fetchProduct } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import { useWishlist } from '../contexts/WishlistContext';

export default function ProductDetail() {
    const { t } = useTranslation();
    const { productId } = useParams();
    const navigate = useNavigate();
    const { authenticated } = useAuth();
    const { cart_items, addToCart, updateCartItem } = useCart();
    const { addToWishlist, removeFromWishlist, isInWishlist, wishlist_items } = useWishlist();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [quantity, setQuantity] = useState(1);
    const [adding, setAdding] = useState(false);
    const [added, setAdded] = useState(false);
    const [wishlist_toggling, setWishlistToggling] = useState(false);

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

    const cart_item = cart_items.find((item) => item.product_id === product?.id);
    const current_cart_quantity = cart_item?.quantity || 0;

    const handleAddToCart = async () => {
        if (!authenticated) {
            navigate('/login');
            return;
        }

        if (product.stock === 0 || adding) {
            return;
        }

        setAdding(true);
        
        if (cart_item) {
            const new_quantity = cart_item.quantity + quantity;
            const result = await updateCartItem(cart_item.id, new_quantity);
            if (result.success) {
                setAdded(true);
                setTimeout(() => setAdded(false), 2000);
            }
        } else {
            const result = await addToCart(product.id, quantity);
            if (result.success) {
                setAdded(true);
                setTimeout(() => setAdded(false), 2000);
            }
        }
        
        setAdding(false);
    };

    const handleQuantityChange = (delta) => {
        const new_quantity = Math.max(1, Math.min(product.stock, quantity + delta));
        setQuantity(new_quantity);
    };

    const handleWishlistToggle = async () => {
        if (!authenticated || wishlist_toggling || !product) {
            return;
        }

        setWishlistToggling(true);
        // Prefer API data if available, otherwise check context
        const in_wishlist = product.in_wishlist ?? isInWishlist(product.id);
        
        if (in_wishlist) {
            const wishlist_item = wishlist_items.find((item) => item.product_id === product.id);
            if (wishlist_item) {
                await removeFromWishlist(wishlist_item.id);
            }
        } else {
            await addToWishlist(product.id);
        }
        
        setWishlistToggling(false);
    };

    // Check wishlist status - prefer API data if available, otherwise check context
    const in_wishlist = authenticated ? (product?.in_wishlist ?? isInWishlist(product?.id)) : false;

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
                    className="mb-6 flex items-center gap-2 px-3 py-2 min-h-[44px] text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors touch-manipulation rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 -ml-3"
                >
                    <ArrowIcon className="w-5 h-5 sm:w-4 sm:h-4" />
                    <span className="text-base sm:text-sm font-medium">{t('common.back')}</span>
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
                                        to={`/store/${product.store.slug}`}
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

                            {authenticated && (
                                <div className="mt-8 space-y-4">
                                    {product.stock > 0 && (
                                        <div className="flex items-center gap-3">
                                            <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {t('cart.quantity')}:
                                            </label>
                                            <div className="flex items-center gap-2">
                                                <button
                                                    onClick={() => handleQuantityChange(-1)}
                                                    disabled={quantity <= 1}
                                                    className="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    <Minus className="w-4 h-4" />
                                                </button>
                                                <span className="w-12 text-center font-semibold text-gray-900 dark:text-white">
                                                    {quantity}
                                                </span>
                                                <button
                                                    onClick={() => handleQuantityChange(1)}
                                                    disabled={quantity >= product.stock}
                                                    className="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                                >
                                                    <Plus className="w-4 h-4" />
                                                </button>
                                            </div>
                                            {current_cart_quantity > 0 && (
                                                <span className="text-sm text-gray-500 dark:text-gray-400">
                                                    ({t('cart.in_cart')}: {current_cart_quantity})
                                                </span>
                                            )}
                                        </div>
                                    )}
                                    <div className="flex gap-3">
                                        <button
                                            onClick={handleAddToCart}
                                            disabled={product.stock === 0 || adding || added}
                                            className={`flex-1 py-3 px-6 rounded-lg font-semibold text-white transition-colors flex items-center justify-center gap-2 ${
                                                product.stock > 0 && !added
                                                    ? 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600'
                                                    : added
                                                    ? 'bg-green-600 dark:bg-green-500'
                                                    : 'bg-gray-400 cursor-not-allowed dark:bg-gray-600'
                                            }`}
                                        >
                                            {added ? (
                                                <>
                                                    <Check className="w-5 h-5" />
                                                    {t('cart.item_added')}
                                                </>
                                            ) : adding ? (
                                                <>
                                                    <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                                    {t('cart.adding')}
                                                </>
                                            ) : product.stock > 0 ? (
                                                <>
                                                    <ShoppingCart className="w-5 h-5" />
                                                    {t('cart.add_to_cart')}
                                                </>
                                            ) : (
                                                t('cart.out_of_stock')
                                            )}
                                        </button>
                                        <button
                                            onClick={handleWishlistToggle}
                                            disabled={wishlist_toggling}
                                            className={`px-6 py-3 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2 border-2 ${
                                                in_wishlist
                                                    ? 'bg-red-50 dark:bg-red-900/20 border-red-500 dark:border-red-400 text-red-600 dark:text-red-400'
                                                    : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                                            } disabled:opacity-50 disabled:cursor-not-allowed`}
                                            title={in_wishlist ? t('wishlist.remove') : t('wishlist.add')}
                                        >
                                            <Heart
                                                className={`w-5 h-5 ${
                                                    in_wishlist ? 'fill-red-500 text-red-500' : ''
                                                }`}
                                            />
                                        </button>
                                    </div>
                                </div>
                            )}
                            {!authenticated && product.stock > 0 && (
                                <div className="mt-8">
                                    <Link
                                        to="/login"
                                        className="w-full py-3 px-6 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition-colors flex items-center justify-center gap-2"
                                    >
                                        <ShoppingCart className="w-5 h-5" />
                                        {t('cart.login_to_add')}
                                    </Link>
                                </div>
                            )}
                            {product.stock === 0 && (
                                <div className="mt-8">
                                    <button
                                        disabled
                                        className="w-full py-3 px-6 rounded-lg font-semibold text-white bg-gray-400 cursor-not-allowed dark:bg-gray-600"
                                    >
                                        {t('cart.out_of_stock')}
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

