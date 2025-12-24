import { useTranslation } from 'react-i18next';
import { X, AlertTriangle } from 'lucide-react';

export default function ConfirmationModal({
    is_open,
    on_close,
    on_confirm,
    title,
    message,
    confirm_text,
    cancel_text,
    confirm_variant = 'danger',
    loading = false,
}) {
    const { t } = useTranslation();

    if (!is_open) {
        return null;
    }

    const confirm_button_class = confirm_variant === 'danger'
        ? 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600'
        : 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600';

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-200"
            onClick={on_close}
        >
            <div
                className="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all duration-200 scale-100"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <div className="flex items-center gap-3">
                        <div className="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                            <AlertTriangle className="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                            {title}
                        </h2>
                    </div>
                    <button
                        onClick={on_close}
                        disabled={loading}
                        className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <X className="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    </button>
                </div>

                {/* Content */}
                <div className="p-6">
                    <p className="text-gray-700 dark:text-gray-300 mb-6">
                        {message}
                    </p>

                    {/* Actions */}
                    <div className="flex items-center gap-3 justify-end">
                        <button
                            onClick={on_close}
                            disabled={loading}
                            className="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {cancel_text || t('common.cancel')}
                        </button>
                        <button
                            onClick={on_confirm}
                            disabled={loading}
                            className={`px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${confirm_button_class}`}
                        >
                            {loading ? (
                                <span className="flex items-center gap-2">
                                    <span className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    {t('common.loading')}
                                </span>
                            ) : (
                                confirm_text || t('common.confirm')
                            )}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

