import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { I18nextProvider } from 'react-i18next';
import i18n from './i18n/config';
import Home from './pages/Home';
import StoreDetail from './pages/StoreDetail';
import ProductDetail from './pages/ProductDetail';
import { useLanguage } from './hooks/useLanguage';
import '../css/app.css';

function AppContent() {
    useLanguage();
    
    return (
        <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/store/:storeId" element={<StoreDetail />} />
            <Route path="/product/:productId" element={<ProductDetail />} />
        </Routes>
    );
}

function App() {
    return (
        <I18nextProvider i18n={i18n}>
            <BrowserRouter>
                <AppContent />
            </BrowserRouter>
        </I18nextProvider>
    );
}

const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(
        <StrictMode>
            <App />
        </StrictMode>
    );
}
