import { createContext, useContext, useState, useEffect } from 'react';
import { useAuth } from './AuthContext';
import * as cartApi from '../services/api';
import * as orderApi from '../services/api';

const CartContext = createContext(null);

export function CartProvider({ children }) {
    const { authenticated, customer } = useAuth();
    const [cart_items, setCartItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [cart_count, setCartCount] = useState(0);
    const [cart_total, setCartTotal] = useState(0);

    const fetchCart = async () => {
        if (!authenticated) {
            setCartItems([]);
            setCartCount(0);
            setCartTotal(0);
            setLoading(false);
            return;
        }

        try {
            setLoading(true);
            const response = await cartApi.getCart();
            if (response.status && response.data) {
                setCartItems(response.data.items || []);
                setCartCount(response.data.count || 0);
                setCartTotal(response.data.total || 0);
            }
        } catch (error) {
            console.error('Error fetching cart:', error);
            setCartItems([]);
            setCartCount(0);
            setCartTotal(0);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCart();
    }, [authenticated, customer?.id]);

    const addToCart = async (product_id, quantity = 1) => {
        try {
            const response = await cartApi.addToCart(product_id, quantity);
            if (response.status && response.data) {
                setCartItems(response.data.items || []);
                setCartCount(response.data.count || 0);
                setCartTotal(response.data.total || 0);
                return { success: true, data: response.data };
            }
            return { success: false, error: 'Failed to add to cart' };
        } catch (error) {
            console.error('Error adding to cart:', error);
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to add to cart',
            };
        }
    };

    const updateCartItem = async (cart_item_id, quantity) => {
        try {
            const response = await cartApi.updateCartItem(cart_item_id, quantity);
            if (response.status && response.data) {
                setCartItems(response.data.items || []);
                setCartCount(response.data.count || 0);
                setCartTotal(response.data.total || 0);
                return { success: true, data: response.data };
            }
            return { success: false, error: 'Failed to update cart' };
        } catch (error) {
            console.error('Error updating cart:', error);
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to update cart',
            };
        }
    };

    const removeFromCart = async (cart_item_id) => {
        try {
            const response = await cartApi.removeFromCart(cart_item_id);
            if (response.status && response.data) {
                setCartItems(response.data.items || []);
                setCartCount(response.data.count || 0);
                setCartTotal(response.data.total || 0);
                return { success: true, data: response.data };
            }
            return { success: false, error: 'Failed to remove from cart' };
        } catch (error) {
            console.error('Error removing from cart:', error);
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to remove from cart',
            };
        }
    };

    const clearCart = async () => {
        try {
            const response = await cartApi.clearCart();
            if (response.status && response.data) {
                setCartItems([]);
                setCartCount(0);
                setCartTotal(0);
                return { success: true, data: response.data };
            }
            return { success: false, error: 'Failed to clear cart' };
        } catch (error) {
            console.error('Error clearing cart:', error);
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to clear cart',
            };
        }
    };

    const placeOrder = async (address_data = {}, payment_method = 'cod') => {
        try {
            const response = await orderApi.placeOrder(address_data, payment_method);
            if (response.status && response.data) {
                // Clear cart after successful order
                setCartItems([]);
                setCartCount(0);
                setCartTotal(0);
                return { success: true, data: response.data };
            }
            return { success: false, error: 'Failed to place order', errors: null };
        } catch (error) {
            console.error('Error placing order:', error);
            // Handle validation errors
            if (error.response?.data?.errors) {
                return {
                    success: false,
                    error: null,
                    errors: error.response.data.errors,
                };
            }
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to place order',
                errors: null,
            };
        }
    };

    const value = {
        cart_items,
        loading,
        cart_count,
        cart_total,
        fetchCart,
        addToCart,
        updateCartItem,
        removeFromCart,
        clearCart,
        placeOrder,
    };

    return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
    const context = useContext(CartContext);
    if (!context) {
        throw new Error('useCart must be used within a CartProvider');
    }
    return context;
}

