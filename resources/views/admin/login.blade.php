<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="login.title">تسجيل دخول الأدمن</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body class="auth">
    <div class="card auth__box">
        <h1 class="auth__title" data-i18n="login.title"></h1>

        <div class="alert" id="errorBox" hidden></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email" data-i18n="login.email"></label>
                <input type="email" id="email" class="input" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password" data-i18n="login.password"></label>
                <input type="password" id="password" class="input" required autocomplete="current-password">
            </div>

            <button type="submit" id="submitBtn" class="btn btn--block" data-i18n="login.submit"></button>
        </form>

        <div class="auth__lang">
            <button type="button" id="langBtn" class="btn btn--ghost" data-i18n="lang.switch">English</button>
        </div>
    </div>

    <script src="{{ asset('js/admin/i18n.js') }}"></script>
    <script src="{{ asset('js/admin/api.js') }}"></script>
    <script>
    if (getToken()) {
        location.href = '/admin';
    }

    const form = document.getElementById('loginForm');
    const errorBox = document.getElementById('errorBox');
    const submitBtn = document.getElementById('submitBtn');
    let errorKey = null;

    function showError(key) {
        errorKey = key;
        errorBox.textContent = t(key);
        errorBox.hidden = false;
    }

    function hideError() {
        errorKey = null;
        errorBox.hidden = true;
    }

    document.addEventListener('langchange', () => {
        if (errorKey) {
            errorBox.textContent = t(errorKey);
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideError();
        submitBtn.disabled = true;

        try {
            const {
                ok,
                status,
                data
            } = await apiFetch('/login', {
                method: 'POST',
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                }),
            });

            if (!ok) {
                if (status === 401) showError('login.error.invalid');
                else if (status === 403) showError('login.error.inactive');
                else if (status === 422) showError('login.error.validation');
                else showError('login.error.generic');
                return;
            }

            setToken(data.token);

            if (data.user.role !== 'admin') {
                await apiFetch('/logout', {
                    method: 'POST'
                });
                clearToken();
                showError('login.error.notAdmin');
                return;
            }

            location.href = '/admin';
        } catch (err) {
            showError('login.error.generic');
        } finally {
            submitBtn.disabled = false;
        }
    });
    </script>
</body>

</html>