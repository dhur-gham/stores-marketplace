import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import Header from '../components/Header';
import Footer from '../components/Footer';
import StoresList from '../components/StoresList';
import ProductsList from '../components/ProductsList';
import { fetchStores, fetchLatestProducts } from '../services/api';

export default function Home() {
    const { t } = useTranslation();
    const [stores, setStores] = useState([]);
    const [products, setProducts] = useState([]);
    const [storesLoading, setStoresLoading] = useState(true);
    const [productsLoading, setProductsLoading] = useState(true);

    useEffect(() => {
        const loadData = async () => {
            try {
                setStoresLoading(true);
                setProductsLoading(true);

                const [storesResponse, productsResponse] = await Promise.all([
                    fetchStores(),
                    fetchLatestProducts(),
                ]);

                if (storesResponse.status && storesResponse.data) {
                    setStores(storesResponse.data);
                }
                setStoresLoading(false);

                if (productsResponse.status && productsResponse.data) {
                    setProducts(productsResponse.data);
                }
                setProductsLoading(false);
            } catch (error) {
                console.error('Error loading data:', error);
                setStoresLoading(false);
                setProductsLoading(false);
            }
        };

        loadData();
    }, []);

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
            <Header />
            <div className="container mx-auto px-4 py-8 flex-1">
                <section className="my-12">
                    <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        {t('home.stores')}
                    </h2>
                    <StoresList stores={stores} loading={storesLoading} limit={5} showViewAll={true} />
                </section>

                <section className="my-12">
                    <h2 className="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        {t('home.latest_products')}
                    </h2>
                    <ProductsList products={products} loading={productsLoading} limit={5} showViewAll={true} />
                </section>
            </div>
            <Footer />
        </div>
    );
}

