import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Mail, Lock, ArrowRight, Sparkles } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import Header from '../components/Header';

export default function Login() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const { login } = useAuth();
    const [form_data, setFormData] = useState({
        email: '',
        password: '',
    });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
        // Clear error when user starts typing
        if (errors[name]) {
            setErrors((prev) => ({
                ...prev,
                [name]: null,
            }));
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            await login(form_data.email, form_data.password);
            navigate('/');
        } catch (error) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            } else {
                setErrors({
                    email: error.response?.data?.message || t('auth.login.invalid_credentials'),
                });
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
            <div className="container mx-auto px-4 py-8">
                <Header />
                
                <div className="max-w-md mx-auto mt-8 md:mt-16">
                    <div className="bg-white/80 dark:bg-gray-800/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 md:p-10">
                        {/* Header with icon */}
                        <div className="text-center mb-8">
                            <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-4 shadow-lg">
                                <Sparkles className="w-8 h-8 text-white" />
                            </div>
                            <h2 className="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-purple-400">
                                {t('auth.login.title')}
                            </h2>
                            <p className="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {t('auth.login.welcome_message')}
                            </p>
                        </div>
                        
                        <form onSubmit={handleSubmit} className="space-y-5">
                            {/* Email Field */}
                            <div>
                                <label htmlFor="email" className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    {t('auth.login.email')}
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <Mail className="w-5 h-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value={form_data.email}
                                        onChange={handleChange}
                                        required
                                        placeholder="you@example.com"
                                        className="w-full ps-10 pe-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/50 dark:text-white transition-all duration-200"
                                    />
                                </div>
                                {errors.email && (
                                    <p className="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <span>⚠️</span>
                                        {Array.isArray(errors.email) ? errors.email[0] : errors.email}
                                    </p>
                                )}
                            </div>

                            {/* Password Field */}
                            <div>
                                <label htmlFor="password" className="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    {t('auth.login.password')}
                                </label>
                                <div className="relative">
                                    <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <Lock className="w-5 h-5 text-gray-400" />
                                    </div>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        value={form_data.password}
                                        onChange={handleChange}
                                        required
                                        placeholder="••••••••"
                                        className="w-full ps-10 pe-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/50 dark:text-white transition-all duration-200"
                                    />
                                </div>
                                {errors.password && (
                                    <p className="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                                        <span>⚠️</span>
                                        {Array.isArray(errors.password) ? errors.password[0] : errors.password}
                                    </p>
                                )}
                            </div>

                            {/* Submit Button */}
                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
                            >
                                {loading ? (
                                    <>
                                        <span className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                        {t('auth.login.submitting')}
                                    </>
                                ) : (
                                    <>
                                        {t('auth.login.submit')}
                                        <ArrowRight className="w-5 h-5" />
                                    </>
                                )}
                            </button>
                        </form>

                        {/* Register Link */}
                        <div className="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <p className="text-center text-sm text-gray-600 dark:text-gray-400">
                                {t('auth.login.no_account')}{' '}
                                <Link 
                                    to="/register" 
                                    className="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline transition-colors"
                                >
                                    {t('auth.login.register_link')}
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

