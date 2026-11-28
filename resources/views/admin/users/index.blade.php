@extends('admin.layouts.app')

@section('title_key', 'users.title')

@section('content')
<div class="card">
    <form class="toolbar" id="filterForm">
        <input type="search" id="searchInput" class="input input--auto" maxlength="100">

        <select id="roleFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="admin" data-i18n="role.admin"></option>
            <option value="owner" data-i18n="role.owner"></option>
            <option value="teacher" data-i18n="role.teacher"></option>
            <option value="parent" data-i18n="role.parent"></option>
        </select>

        <select id="statusFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="active" data-i18n="status.active"></option>
            <option value="inactive" data-i18n="status.inactive"></option>
            <option value="suspended" data-i18n="status.suspended"></option>
        </select>

        <button type="submit" class="btn" data-i18n="common.search"></button>
    </form>

    <div class="alert" id="pageError" hidden></div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="users.col.name"></th>
                    <th data-i18n="users.col.email"></th>
                    <th data-i18n="users.col.phone"></th>
                    <th data-i18n="users.col.role"></th>
                    <th data-i18n="users.col.status"></th>
                    <th data-i18n="users.col.date"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <p class="muted" id="emptyMsg" data-i18n="users.empty" hidden></p>

    <div class="pagination">
        <button type="button" id="prevBtn" class="btn btn--ghost" data-i18n="common.prev"></button>
        <span id="pageInfo"></span>
        <button type="button" id="nextBtn" class="btn btn--ghost" data-i18n="common.next"></button>
    </div>
</div>
@endsection

@push('scripts')
<script>
const STATUSES = ['active', 'inactive', 'suspended'];

const tbody = document.getElementById('tbody');
const emptyMsg = document.getElementById('emptyMsg');
const filterForm = document.getElementById('filterForm');
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');
const statusFilter = document.getElementById('statusFilter');
const pageError = document.getElementById('pageError');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const pageInfo = document.getElementById('pageInfo');

let rows = [];
let currentPage = 1;
let lastPage = 1;
let pageErrorKey = null;

function showError(key) {
    pageErrorKey = key;
    pageError.textContent = t(key);
    pageError.hidden = false;
}

function hideError() {
    pageErrorKey = null;
    pageError.hidden = true;
}

function formatDate(value) {
    return value ? new Date(value).toLocaleDateString(getLang()) : '-';
}

function applySearchPlaceholder() {
    searchInput.placeholder = t('users.search');
}

function statusSelect(user) {
    const select = document.createElement('select');
    select.className = 'input input--auto';

    STATUSES.forEach((s) => {
        const option = document.createElement('option');
        option.value = s;
        option.textContent = t('status.' + s);
        select.appendChild(option);
    });

    select.value = user.status;

    select.addEventListener('change', async () => {
        const newStatus = select.value;

        if (!confirm(t('users.confirm'))) {
            select.value = user.status;
            return;
        }

        hideError();
        select.disabled = true;

        const {
            ok,
            status
        } = await apiFetch(`/admin/users/${user.id}/status`, {
            method: 'PATCH',
            body: JSON.stringify({
                status: newStatus
            }),
        });

        select.disabled = false;

        if (!ok) {
            select.value = user.status;
            showError(status === 403 ? 'users.cannotSelf' : 'common.error');
            return;
        }

        user.status = newStatus;
        load(currentPage);
    });

    return select;
}

function renderRows() {
    tbody.replaceChildren();
    emptyMsg.hidden = rows.length > 0;

    rows.forEach((u) => {
        const tr = document.createElement('tr');

        [u.name, u.email, u.phone ?? '-', t('role.' + u.role)].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');
        tdStatus.appendChild(statusSelect(u));
        tr.appendChild(tdStatus);

        const tdDate = document.createElement('td');
        tdDate.textContent = formatDate(u.created_at);
        tr.appendChild(tdDate);

        tbody.appendChild(tr);
    });
}

function renderPager() {
    pageInfo.textContent = currentPage + ' / ' + lastPage;
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= lastPage;
}

async function load(page = 1) {
    const query = new URLSearchParams({
        page
    });
    if (searchInput.value.trim()) query.set('search', searchInput.value.trim());
    if (roleFilter.value) query.set('role', roleFilter.value);
    if (statusFilter.value) query.set('status', statusFilter.value);

    const {
        ok,
        data
    } = await apiFetch('/admin/users?' + query);

    if (!ok) {
        showError('common.error');
        return;
    }

    hideError();
    rows = data.data;
    currentPage = data.current_page;
    lastPage = data.last_page;
    renderRows();
    renderPager();
}

filterForm.addEventListener('submit', (e) => {
    e.preventDefault();
    load(1);
});
roleFilter.addEventListener('change', () => load(1));
statusFilter.addEventListener('change', () => load(1));
prevBtn.addEventListener('click', () => load(currentPage - 1));
nextBtn.addEventListener('click', () => load(currentPage + 1));

document.addEventListener('langchange', () => {
    applySearchPlaceholder();
    renderRows();
    if (pageErrorKey) pageError.textContent = t(pageErrorKey);
});

applySearchPlaceholder();
load(1);
</script>
@endpush