const translations = {
    ar: {
        'nav.home': 'الرئيسية',
        'topbar.logout': 'تسجيل الخروج',
        'lang.switch': 'English',
        'dashboard.title': 'الرئيسية',
        'dashboard.welcome': 'مرحباً بك في لوحة التحكم',
        'login.title': 'تسجيل دخول الأدمن',
        'login.email': 'البريد الإلكتروني',
        'login.password': 'كلمة المرور',
        'login.submit': 'دخول',
        'login.error.invalid': 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
        'login.error.inactive': 'هذا الحساب غير مفعّل',
        'login.error.notAdmin': 'هذا الحساب ليس حساب أدمن',
        'login.error.validation': 'تحقق من البريد الإلكتروني وكلمة المرور',
        'login.error.generic': 'حدث خطأ، حاول مرة أخرى',
    },
    en: {
        'nav.home': 'Home',
        'topbar.logout': 'Log out',
        'lang.switch': 'العربية',
        'dashboard.title': 'Home',
        'dashboard.welcome': 'Welcome to the dashboard',
        'login.title': 'Admin Login',
        'login.email': 'Email',
        'login.password': 'Password',
        'login.submit': 'Log in',
        'login.error.invalid': 'The email or password is incorrect',
        'login.error.inactive': 'This account is not active',
        'login.error.notAdmin': 'This account is not an admin account',
        'login.error.validation': 'Check your email and password',
        'login.error.generic': 'Something went wrong, please try again',
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