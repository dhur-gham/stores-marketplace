import { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { X, Copy, Check, Share2 } from 'lucide-react';
import { getShareLink, generateShareLink, updateShareMessage, toggleShare } from '../services/api';

export default function ShareWishlistModal({ is_open, on_close }) {
    const { t, i18n } = useTranslation();
    const [loading, setLoading] = useState(false);
    const [share_data, setShareData] = useState(null);
    const [selected_message, setSelectedMessage] = useState(null);
    const [custom_message, setCustomMessage] = useState('');
    const [link_copied, setLinkCopied] = useState(false);
    const [updating, setUpdating] = useState(false);

    const language = i18n.language || 'en';
    const messages = Array.from({ length: 20 }, (_, i) => ({
        id: i + 1,
        text: t(`wishlist.share.messages.message_${i + 1}`),
    }));

    useEffect(() => {
        if (is_open) {
            loadShareLink();
        }
    }, [is_open]);

    const loadShareLink = async () => {
        try {
            setLoading(true);
            const response = await getShareLink();
            if (response.status && response.data) {
                setShareData(response.data);
                const saved_message = response.data.custom_message || '';
                setCustomMessage(saved_message);
                
                // Check if saved message matches one of the predefined messages
                const matched_message = messages.find((msg) => msg.text === saved_message);
                if (matched_message) {
                    setSelectedMessage(matched_message);
                    setCustomMessage('');
                } else {
                    setSelectedMessage(null);
                }
            }
        } catch (error) {
            console.error('Error loading share link:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleGenerateLink = async () => {
        try {
            setUpdating(true);
            const message = custom_message.trim() || selected_message?.text || null;
            const response = await generateShareLink(message);
            if (response.status && response.data) {
                setShareData(response.data);
            }
        } catch (error) {
            console.error('Error generating share link:', error);
        } finally {
            setUpdating(false);
        }
    };

    const handleUpdateMessage = async () => {
        if (!custom_message.trim()) {
            return;
        }

        try {
            setUpdating(true);
            const response = await updateShareMessage(custom_message.trim());
            if (response.status && response.data) {
                setShareData(response.data);
            }
        } catch (error) {
            console.error('Error updating message:', error);
        } finally {
            setUpdating(false);
        }
    };

    const handleToggleShare = async (is_active) => {
        try {
            setUpdating(true);
            const response = await toggleShare(is_active);
            if (response.status && response.data) {
                setShareData(response.data);
            }
        } catch (error) {
            console.error('Error toggling share:', error);
        } finally {
            setUpdating(false);
        }
    };

    const handleCopyLink = async () => {
        if (!share_data?.share_url) {
            return;
        }

        const full_link = share_data.share_url;
        // Use the saved custom_message from backend, which includes selected messages
        const message = share_data.custom_message || messages[Math.floor(Math.random() * messages.length)].text;
        const text_to_copy = message ? `${message}\n\n${full_link}` : full_link;

        try {
            await navigator.clipboard.writeText(text_to_copy);
            setLinkCopied(true);
            setTimeout(() => setLinkCopied(false), 2000);
        } catch (error) {
            console.error('Error copying link:', error);
        }
    };

    const handleSelectMessage = async (message) => {
        setSelectedMessage(message);
        setCustomMessage('');
        
        // Automatically save the selected message to the backend
        try {
            setUpdating(true);
            const response = await updateShareMessage(message.text);
            if (response.status && response.data) {
                setShareData(response.data);
            }
        } catch (error) {
            console.error('Error updating message:', error);
        } finally {
            setUpdating(false);
        }
    };

    if (!is_open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" onClick={on_close}>
            <div
                className="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                        {t('wishlist.share.title')}
                    </h2>
                    <button
                        onClick={on_close}
                        className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        <X className="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    </button>
                </div>

                {/* Content */}
                <div className="flex-1 overflow-y-auto p-6 space-y-6">
                    {loading ? (
                        <div className="flex items-center justify-center py-12">
                            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        </div>
                    ) : (
                        <>
                            {/* Share Link Section */}
                            {share_data && (
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {t('wishlist.share.share_link')}
                                        </label>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                value={share_data.share_url || ''}
                                                readOnly
                                                className="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white"
                                            />
                                            <button
                                                onClick={handleCopyLink}
                                                className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2"
                                            >
                                                {link_copied ? (
                                                    <>
                                                        <Check className="w-4 h-4" />
                                                        {t('wishlist.share.link_copied')}
                                                    </>
                                                ) : (
                                                    <>
                                                        <Copy className="w-4 h-4" />
                                                        {t('wishlist.share.copy_link')}
                                                    </>
                                                )}
                                            </button>
                                        </div>
                                    </div>

                                    {/* Views Count */}
                                    <div className="text-sm text-gray-600 dark:text-gray-400">
                                        {t('wishlist.share.views_count')}: {share_data.views_count || 0}
                                    </div>

                                    {/* Toggle Share */}
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {share_data.is_active
                                                ? t('wishlist.share.sharing_enabled')
                                                : t('wishlist.share.sharing_disabled')}
                                        </span>
                                        <button
                                            onClick={() => handleToggleShare(!share_data.is_active)}
                                            disabled={updating}
                                            className={`px-4 py-2 rounded-lg transition-colors ${
                                                share_data.is_active
                                                    ? 'bg-red-600 hover:bg-red-700 text-white'
                                                    : 'bg-green-600 hover:bg-green-700 text-white'
                                            } disabled:opacity-50 disabled:cursor-not-allowed`}
                                        >
                                            {share_data.is_active
                                                ? t('wishlist.share.disable_sharing')
                                                : t('wishlist.share.enable_sharing')}
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* Message Selection */}
                            <div className="space-y-4">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {t('wishlist.share.select_message')}
                                </label>
                                <div className="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto">
                                    {messages.map((message) => (
                                        <button
                                            key={message.id}
                                            onClick={() => handleSelectMessage(message)}
                                            disabled={updating}
                                            className={`p-3 text-sm text-left rounded-lg border transition-colors ${
                                                selected_message?.id === message.id
                                                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-500'
                                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                                            } disabled:opacity-50 disabled:cursor-not-allowed`}
                                        >
                                            {message.text}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* Custom Message */}
                            <div className="space-y-2">
                                <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {t('wishlist.share.custom_message')}
                                </label>
                                <textarea
                                    value={custom_message}
                                    onChange={(e) => {
                                        setCustomMessage(e.target.value);
                                        setSelectedMessage(null);
                                    }}
                                    placeholder={t('wishlist.share.custom_message_placeholder')}
                                    rows={3}
                                    className="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <button
                                    onClick={handleUpdateMessage}
                                    disabled={!custom_message.trim() || updating}
                                    className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {updating ? t('wishlist.share.updating') : t('wishlist.share.generate_link')}
                                </button>
                                {selected_message && (
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        {t('wishlist.share.select_message')}: {selected_message.text}
                                    </p>
                                )}
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}

