const API = '/api';
const TOKEN_KEY = 'admin_token';

function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

function setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
}

function clearToken() {
    localStorage.removeItem(TOKEN_KEY);
}

async function apiFetch(path, options = {}) {
    const headers = {
        'Accept': 'application/json',
        'Accept-Language': getLang(),
        ...(options.headers || {}),
    };

    if (options.body) {
        headers['Content-Type'] = 'application/json';
    }

    const token = getToken();
    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }

    const res = await fetch(API + path, { ...options, headers });
    const data = await res.json().catch(() => ({}));

    if (res.status === 401 && path !== '/login') {
        clearToken();
        location.href = '/admin/login';
    }

    return { ok: res.ok, status: res.status, data };
}

async function requireAdmin() {
    if (!getToken()) {
        location.href = '/admin/login';
        return;
    }

    const { ok, data } = await apiFetch('/me');

    if (!ok || data.role !== 'admin') {
        clearToken();
        location.href = '/admin/login';
        return;
    }

    document.getElementById('adminName').textContent = data.name;
}

async function logout() {
    await apiFetch('/logout', { method: 'POST' });
    clearToken();
    location.href = '/admin/login';
}