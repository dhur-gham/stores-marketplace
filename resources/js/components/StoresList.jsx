import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

export default function StoresList({ stores, loading }) {
    const { t } = useTranslation();
    if (loading) {
        return (
            <div className="flex gap-4 overflow-x-auto pb-4 px-4 md:px-0">
                {[...Array(5)].map((_, i) => (
                    <div
                        key={i}
                        className="flex-shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"
                    />
                ))}
            </div>
        );
    }

    if (!stores || stores.length === 0) {
        return (
            <div className="text-center py-8 text-gray-500 dark:text-gray-400">
                {t('stores.no_stores')}
            </div>
        );
    }

    return (
        <div className="flex gap-4 overflow-x-auto pb-4 px-4 md:px-0 scrollbar-hide">
            {stores.map((store) => (
                <Link
                    key={store.id}
                    to={`/store/${store.id}`}
                    className="flex-shrink-0 flex flex-col items-center gap-2 group"
                >
                    <div className="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 group-hover:border-blue-500 dark:group-hover:border-blue-400 transition-all transform group-hover:scale-105">
                        {store.image ? (
                            <img
                                src={store.image}
                                alt={store.name}
                                className="w-full h-full object-cover"
                            />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 text-xs font-medium">
                                {store.name.charAt(0).toUpperCase()}
                            </div>
                        )}
                    </div>
                    <span className="text-xs md:text-sm text-gray-700 dark:text-gray-300 text-center max-w-[80px] truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        {store.name}
                    </span>
                </Link>
            ))}
        </div>
    );
}

