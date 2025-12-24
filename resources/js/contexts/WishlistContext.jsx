import { createContext, useContext, useState, useEffect } from 'react';
import { useAuth } from './AuthContext';
import * as wishlistApi from '../services/api';

const WishlistContext = createContext(null);

export function WishlistProvider({ children }) {
    const { authenticated, customer } = useAuth();
    const [wishlist_items, setWishlistItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [wishlist_count, setWishlistCount] = useState(0);

    const fetchWishlist = async () => {
        if (!authenticated) {
            setWishlistItems([]);
            setWishlistCount(0);
            setLoading(false);
            return;
        }

        try {
            setLoading(true);
            const response = await wishlistApi.getWishlist();
            if (response.status && response.data) {
                setWishlistItems(response.data.items || []);
                setWishlistCount(response.data.count || 0);
            }
        } catch (error) {
            console.error('Error fetching wishlist:', error);
            setWishlistItems([]);
            setWishlistCount(0);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchWishlist();
    }, [authenticated, customer?.id]);

    const addToWishlist = async (product_id) => {
        try {
            const response = await wishlistApi.addToWishlist(product_id);
            if (response.status && response.data) {
                setWishlistItems(response.data.items || []);
                setWishlistCount(response.data.count || 0);
                return { success: true, data: response.data };
            }
            return { success: false, error: 'Failed to add to wishlist' };
        } catch (error) {
            console.error('Error adding to wishlist:', error);
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to add to wishlist',
            };
        }
    };

    const removeFromWishlist = async (wishlist_item_id) => {
        try {
            const response = await wishlistApi.removeFromWishlist(wishlist_item_id);
            if (response.status && response.data) {
                setWishlistItems(response.data.items || []);
                setWishlistCount(response.data.count || 0);
                return { success: true, data: response.data };
            }
            return { success: false, error: 'Failed to remove from wishlist' };
        } catch (error) {
            console.error('Error removing from wishlist:', error);
            return {
                success: false,
                error: error.response?.data?.message || 'Failed to remove from wishlist',
            };
        }
    };

    const isInWishlist = (product_id) => {
        return wishlist_items.some((item) => item.product_id === product_id);
    };

    const value = {
        wishlist_items,
        loading,
        wishlist_count,
        fetchWishlist,
        addToWishlist,
        removeFromWishlist,
        isInWishlist,
    };

    return <WishlistContext.Provider value={value}>{children}</WishlistContext.Provider>;
}

export function useWishlist() {
    const context = useContext(WishlistContext);
    if (!context) {
        throw new Error('useWishlist must be used within a WishlistProvider');
    }
    return context;
}

