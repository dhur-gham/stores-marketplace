import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

export const fetchStores = async () => {
    const response = await api.get('/stores');
    return response.data;
};

export const fetchStore = async (identifier) => {
    const response = await api.get(`/stores/${identifier}`);
    return response.data;
};

export const fetchStoreProducts = async (store_id, per_page = 15) => {
    const response = await api.get(`/stores/${store_id}/products`, {
        params: { per_page },
    });
    return response.data;
};

export const fetchLatestProducts = async () => {
    const response = await api.get('/products/latest');
    return response.data;
};

export const fetchProduct = async (identifier) => {
    const response = await api.get(`/products/${identifier}`);
    return response.data;
};

