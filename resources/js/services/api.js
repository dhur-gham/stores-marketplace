import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add token to requests if available
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle 401 responses
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('auth_token');
            // Only redirect if not already on login/register page and not during initial auth check
            const current_path = window.location.pathname;
            if (!current_path.includes('/login') && !current_path.includes('/register')) {
                // Use setTimeout to avoid redirect during initial page load
                setTimeout(() => {
                    if (window.location.pathname === current_path) {
                        window.location.href = '/login';
                    }
                }, 100);
            }
        }
        return Promise.reject(error);
    }
);

// Payment API functions
export const getPaymentClientKey = async () => {
    const response = await api.get('/payment/client-key');
    return response.data;
};

export const processPayment = async (order_id, payment_token, customer_data = {}) => {
    const response = await api.post('/payment/process', {
        order_id,
        payment_token,
        ...customer_data,
    });
    return response.data;
};

export const fetchStores = async (search = null, per_page = 15, page = 1) => {
    const params = {
        per_page,
        page,
    };
    
    if (search && search.trim() !== '') {
        params.search = search.trim();
    }
    
    const response = await api.get('/stores', { params });
    return response.data;
};

export const fetchStore = async (identifier) => {
    const response = await api.get(`/stores/${identifier}`);
    return response.data;
};

export const fetchStoreDeliveryPrices = async (identifier) => {
    const response = await api.get(`/stores/${identifier}/delivery-prices`);
    return response.data;
};

export const fetchStoreProducts = async (identifier, per_page = 15) => {
    const response = await api.get(`/stores/${identifier}/products`, {
        params: { per_page },
    });
    return response.data;
};

export const fetchLatestProducts = async () => {
    const response = await api.get('/products/latest');
    return response.data;
};

export const fetchAllProducts = async ({
    search = null,
    store_id = null,
    type = null,
    price_min = null,
    price_max = null,
    sort_by = null,
    sort_order = 'desc',
    per_page = 15,
    page = 1,
} = {}) => {
    const params = {
        per_page,
        page,
    };
    
    if (search && search.trim() !== '') {
        params.search = search.trim();
    }
    
    if (store_id !== null) {
        params.store_id = store_id;
    }
    
    if (type) {
        params.type = type;
    }
    
    if (price_min !== null) {
        params.price_min = price_min;
    }
    
    if (price_max !== null) {
        params.price_max = price_max;
    }
    
    if (sort_by) {
        params.sort_by = sort_by;
        params.sort_order = sort_order;
    }
    
    const response = await api.get('/products', { params });
    return response.data;
};

export const fetchProduct = async (identifier) => {
    const response = await api.get(`/products/${identifier}`);
    return response.data;
};

// Auth methods
export const register = async (data) => {
    const response = await api.post('/auth/register', data);
    if (response.data.token) {
        localStorage.setItem('auth_token', response.data.token);
    }
    return response.data;
};

export const login = async (data) => {
    const response = await api.post('/auth/login', data);
    if (response.data.token) {
        localStorage.setItem('auth_token', response.data.token);
    }
    return response.data;
};

export const logout = async () => {
    await api.post('/auth/logout');
    localStorage.removeItem('auth_token');
};

export const getCurrentUser = async () => {
    const response = await api.get('/auth/user');
    return response.data;
};

// Telegram methods
export const getTelegramActivationLink = async () => {
    const response = await api.get('/telegram/activation-link');
    return response.data;
};

// Cart methods
export const getCart = async () => {
    const response = await api.get('/cart');
    return response.data;
};

export const addToCart = async (product_id, quantity = 1) => {
    const response = await api.post('/cart', {
        product_id,
        quantity,
    });
    return response.data;
};

export const updateCartItem = async (cart_item_id, quantity) => {
    const response = await api.put(`/cart/${cart_item_id}`, {
        quantity,
    });
    return response.data;
};

export const removeFromCart = async (cart_item_id) => {
    const response = await api.delete(`/cart/${cart_item_id}`);
    return response.data;
};

export const clearCart = async () => {
    const response = await api.delete('/cart');
    return response.data;
};

// Order methods
export const placeOrder = async (address_data = {}, payment_method = 'cod') => {
    const response = await api.post('/orders', {
        address_data,
        payment_method,
    });
    return response.data;
};

export const getOrders = async (page = 1, per_page = 15) => {
    const response = await api.get('/orders', {
        params: { page, per_page },
    });
    return response.data;
};

export const getOrder = async (order_id) => {
    const response = await api.get(`/orders/${order_id}`);
    return response.data;
};

// Cities
export const fetchCities = async () => {
    const response = await api.get('/cities');
    return response.data;
};

// Wishlist methods
export const getWishlist = async () => {
    const response = await api.get('/wishlist');
    return response.data;
};

export const addToWishlist = async (product_id) => {
    const response = await api.post('/wishlist', {
        product_id,
    });
    return response.data;
};

export const removeFromWishlist = async (wishlist_item_id) => {
    const response = await api.delete(`/wishlist/${wishlist_item_id}`);
    return response.data;
};

export const checkWishlist = async (product_id) => {
    const response = await api.get(`/wishlist/check/${product_id}`);
    return response.data;
};

// Wishlist share methods
export const getShareLink = async () => {
    const response = await api.get('/wishlist/share');
    return response.data;
};

export const generateShareLink = async (custom_message = null) => {
    const response = await api.post('/wishlist/share', {
        custom_message,
    });
    return response.data;
};

export const getSharedWishlist = async (token) => {
    const response = await api.get(`/wishlist/share/${token}`);
    return response.data;
};

export const updateShareMessage = async (message) => {
    const response = await api.put('/wishlist/share/message', {
        custom_message: message,
    });
    return response.data;
};

export const toggleShare = async (is_active) => {
    const response = await api.put('/wishlist/share/toggle', {
        is_active,
    });
    return response.data;
};

// Saved Addresses API functions
export const getSavedAddresses = async () => {
    const response = await api.get('/saved-addresses');
    return response.data;
};

export const createSavedAddress = async (data) => {
    const response = await api.post('/saved-addresses', data);
    return response.data;
};

export const updateSavedAddress = async (id, data) => {
    const response = await api.put(`/saved-addresses/${id}`, data);
    return response.data;
};

export const deleteSavedAddress = async (id) => {
    const response = await api.delete(`/saved-addresses/${id}`);
    return response.data;
};

