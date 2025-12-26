import { useLanguage } from '../hooks/useLanguage';

export default function ArrowIcon({ className = "w-5 h-5" }) {
    const { is_rtl } = useLanguage();
    
    return (
        <svg 
            className={`${className} ${is_rtl ? 'rotate-180' : ''}`}
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
        </svg>
    );
}


