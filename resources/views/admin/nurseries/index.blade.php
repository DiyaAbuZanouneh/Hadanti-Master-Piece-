@extends('admin.layouts.app')

@section('title_key', 'nurseries.title')

@section('content')
<div class="card">
    <div class="toolbar">
        <label for="statusFilter" data-i18n="nurseries.filter"></label>
        <select id="statusFilter" class="input input--auto">
            <option value="pending" data-i18n="status.pending"></option>
            <option value="approved" data-i18n="status.approved"></option>
            <option value="rejected" data-i18n="status.rejected"></option>
            <option value="suspended" data-i18n="status.suspended"></option>
            <option value="" data-i18n="status.all"></option>
        </select>
    </div>

    <div class="alert" id="pageError" hidden></div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="nurseries.col.name"></th>
                    <th data-i18n="nurseries.col.owner"></th>
                    <th data-i18n="nurseries.col.region"></th>
                    <th data-i18n="nurseries.col.phone"></th>
                    <th data-i18n="nurseries.col.status"></th>
                    <th data-i18n="nurseries.col.date"></th>
                    <th data-i18n="nurseries.col.actions"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <p class="muted" id="emptyMsg" data-i18n="nurseries.empty" hidden></p>

    <div class="pagination">
        <button type="button" id="prevBtn" class="btn btn--ghost" data-i18n="common.prev"></button>
        <span id="pageInfo"></span>
        <button type="button" id="nextBtn" class="btn btn--ghost" data-i18n="common.next"></button>
    </div>
</div>

<div class="modal" id="modal" hidden>
    <div class="modal__box">
        <div class="modal__head">
            <h2 data-i18n="nurseries.details"></h2>
            <button type="button" id="closeModal" class="btn btn--ghost">✕</button>
        </div>

        <dl class="details" id="details"></dl>

        <div id="reviewArea" hidden>
            <label for="reason" data-i18n="nurseries.reason"></label>
            <textarea id="reason" class="input" rows="3"></textarea>

            <div class="modal__actions">
                <button type="button" id="approveBtn" class="btn" data-i18n="nurseries.approve"></button>
                <button type="button" id="rejectBtn" class="btn btn--danger" data-i18n="nurseries.reject"></button>
            </div>
        </div>

        <div class="alert" id="modalError" hidden></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tbody = document.getElementById('tbody');
const emptyMsg = document.getElementById('emptyMsg');
const statusFilter = document.getElementById('statusFilter');
const pageError = document.getElementById('pageError');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const pageInfo = document.getElementById('pageInfo');
const modal = document.getElementById('modal');
const details = document.getElementById('details');
const reviewArea = document.getElementById('reviewArea');
const reasonInput = document.getElementById('reason');
const approveBtn = document.getElementById('approveBtn');
const rejectBtn = document.getElementById('rejectBtn');
const modalError = document.getElementById('modalError');

let rows = [];
let currentPage = 1;
let lastPage = 1;
let selected = null;
let modalErrorKey = null;

function statusBadge(status) {
    const span = document.createElement('span');
    span.className = 'badge badge--' + status;
    span.textContent = t('status.' + status);
    return span;
}

function formatDate(value) {
    return value ? new Date(value).toLocaleDateString(getLang()) : '-';
}

function renderRows() {
    tbody.replaceChildren();
    emptyMsg.hidden = rows.length > 0;

    rows.forEach((n) => {
        const tr = document.createElement('tr');

        [n.name, n.owner?.name ?? '-', n.region, n.phone ?? '-'].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');
        tdStatus.appendChild(statusBadge(n.status));
        tr.appendChild(tdStatus);

        const tdDate = document.createElement('td');
        tdDate.textContent = formatDate(n.created_at);
        tr.appendChild(tdDate);

        const tdActions = document.createElement('td');
        const viewBtn = document.createElement('button');
        viewBtn.type = 'button';
        viewBtn.className = 'btn btn--ghost';
        viewBtn.textContent = t('common.view');
        viewBtn.addEventListener('click', () => openModal(n.id));
        tdActions.appendChild(viewBtn);
        tr.appendChild(tdActions);

        tbody.appendChild(tr);
    });
}

function renderPager() {
    pageInfo.textContent = currentPage + ' / ' + lastPage;
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= lastPage;
}

async function load(page = 1) {
    pageError.hidden = true;

    const query = new URLSearchParams({
        page
    });
    if (statusFilter.value) {
        query.set('status', statusFilter.value);
    }

    const {
        ok,
        data
    } = await apiFetch('/admin/nurseries?' + query);

    if (!ok) {
        pageError.textContent = t('common.error');
        pageError.hidden = false;
        return;
    }

    rows = data.data;
    currentPage = data.current_page;
    lastPage = data.last_page;
    renderRows();
    renderPager();
}

function addDetail(labelKey, value) {
    const dt = document.createElement('dt');
    dt.textContent = t(labelKey);
    const dd = document.createElement('dd');
    if (value instanceof Node) {
        dd.appendChild(value);
    } else {
        dd.textContent = value ?? '-';
    }
    details.append(dt, dd);
}

function renderDetails() {
    details.replaceChildren();
    const n = selected;

    addDetail('nurseries.col.name', n.name);
    addDetail('nurseries.col.status', statusBadge(n.status));
    addDetail('nurseries.col.owner', n.owner?.name);
    addDetail('nurseries.email', n.owner?.email);
    addDetail('nurseries.col.phone', n.phone);
    addDetail('nurseries.col.region', n.region);
    addDetail('nurseries.address', n.address);
    addDetail('nurseries.hours', n.start_time.slice(0, 5) + ' - ' + n.end_time.slice(0, 5));
    addDetail('nurseries.dailyPrice', n.daily_price);
    addDetail('nurseries.monthlyPrice', n.monthly_price);
    addDetail('nurseries.licenseNumber', n.license_number);
    addDetail('nurseries.licenseExpiry', n.license_expiry ? formatDate(n.license_expiry) : null);
    addDetail('nurseries.crNumber', n.cr_number);

    if (n.status === 'rejected') {
        addDetail('nurseries.rejectReason', n.reject_reason);
    }

    reviewArea.hidden = n.status !== 'pending';
}

async function openModal(id) {
    const {
        ok,
        data
    } = await apiFetch('/admin/nurseries/' + id);

    if (!ok) {
        pageError.textContent = t('common.error');
        pageError.hidden = false;
        return;
    }

    selected = data;
    reasonInput.value = '';
    hideModalError();
    renderDetails();
    modal.hidden = false;
}

function closeModal() {
    modal.hidden = true;
    selected = null;
}

function showModalError(key) {
    modalErrorKey = key;
    modalError.textContent = t(key);
    modalError.hidden = false;
}

function hideModalError() {
    modalErrorKey = null;
    modalError.hidden = true;
}

async function review(action) {
    hideModalError();

    let body;
    if (action === 'reject') {
        const reason = reasonInput.value.trim();
        if (!reason) {
            showModalError('nurseries.reasonRequired');
            return;
        }
        body = JSON.stringify({
            reason
        });
    }

    approveBtn.disabled = true;
    rejectBtn.disabled = true;

    const {
        ok,
        status
    } = await apiFetch(`/admin/nurseries/${selected.id}/${action}`, {
        method: 'POST',
        body,
    });

    approveBtn.disabled = false;
    rejectBtn.disabled = false;

    if (!ok) {
        showModalError(status === 409 ? 'nurseries.alreadyReviewed' : 'common.error');
        return;
    }

    closeModal();
    load(currentPage);
}

statusFilter.addEventListener('change', () => load(1));
prevBtn.addEventListener('click', () => load(currentPage - 1));
nextBtn.addEventListener('click', () => load(currentPage + 1));
approveBtn.addEventListener('click', () => review('approve'));
rejectBtn.addEventListener('click', () => review('reject'));
document.getElementById('closeModal').addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

document.addEventListener('langchange', () => {
    renderRows();
    if (selected) renderDetails();
    if (modalErrorKey) modalError.textContent = t(modalErrorKey);
});

load(1);
</script>
@endpush