import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { MessageCircle, CheckCircle, ExternalLink } from 'lucide-react';
import { getTelegramActivationLink } from '../services/api';

export default function TelegramActivation() {
    const { t } = useTranslation();
    const [activation_link, setActivationLink] = useState(null);
    const [is_activated, setIsActivated] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchActivationLink = async () => {
            try {
                setLoading(true);
                const response = await getTelegramActivationLink();
                if (response.status && response.data) {
                    setActivationLink(response.data.activation_link);
                    setIsActivated(response.data.is_activated);
                }
            } catch (error) {
                console.error('Error fetching Telegram activation link:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchActivationLink();
    }, []);

    if (loading) {
        return (
            <div className="animate-pulse">
                <div className="h-20 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
            </div>
        );
    }

    if (is_activated) {
        return (
            <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div className="flex items-center gap-3">
                    <CheckCircle className="w-5 h-5 text-green-600 dark:text-green-400" />
                    <div>
                        <p className="font-semibold text-green-900 dark:text-green-100">
                            {t('telegram.activated')}
                        </p>
                        <p className="text-sm text-green-700 dark:text-green-300">
                            {t('telegram.activated_description')}
                        </p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div className="flex items-start gap-3">
                <MessageCircle className="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                <div className="flex-1">
                    <h3 className="font-semibold text-blue-900 dark:text-blue-100 mb-1">
                        {t('telegram.activate_notifications')}
                    </h3>
                    <p className="text-sm text-blue-700 dark:text-blue-300 mb-3">
                        {t('telegram.activate_description')}
                    </p>
                    {activation_link && (
                        <a
                            href={activation_link}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors text-sm"
                        >
                            <span>{t('telegram.activate_button')}</span>
                            <ExternalLink className="w-4 h-4" />
                        </a>
                    )}
                </div>
            </div>
        </div>
    );
}

