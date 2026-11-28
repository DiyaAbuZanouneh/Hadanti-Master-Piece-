const translations = {
    ar: {
        'nav.home': 'الرئيسية',
        'topbar.logout': 'تسجيل الخروج',
        'lang.switch': 'English',
        'dashboard.title': 'الرئيسية',
        'dashboard.welcome': 'مرحباً بك في لوحة التحكم',
    },
    en: {
        'nav.home': 'Home',
        'topbar.logout': 'Log out',
        'lang.switch': 'العربية',
        'dashboard.title': 'Home',
        'dashboard.welcome': 'Welcome to the dashboard',
    },
};

const SUPPORTED_LANGS = ['ar', 'en'];

function getLang() {
    const saved = localStorage.getItem('lang');
    return SUPPORTED_LANGS.includes(saved) ? saved : 'ar';
}

function t(key) {
    return translations[getLang()][key] ?? key;
}

function applyLang() {
    const lang = getLang();

    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';

    document.querySelectorAll('[data-i18n]').forEach((el) => {
        el.textContent = t(el.dataset.i18n);
    });
}

function setLang(lang) {
    localStorage.setItem('lang', lang);
    applyLang();
    document.dispatchEvent(new Event('langchange'));
}

applyLang();

const langBtn = document.getElementById('langBtn');
if (langBtn) {
    langBtn.addEventListener('click', () => {
        setLang(getLang() === 'ar' ? 'en' : 'ar');
    });
}