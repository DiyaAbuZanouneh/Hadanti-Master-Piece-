@extends('admin.layouts.app')

@section('title_key', 'bookings.title')

@section('content')
<div class="card">
    <div class="toolbar">
        <select id="statusFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="pending" data-i18n="status.pending"></option>
            <option value="accepted" data-i18n="status.accepted"></option>
            <option value="rejected" data-i18n="status.rejected"></option>
            <option value="paid" data-i18n="status.paid"></option>
            <option value="cancelled" data-i18n="status.cancelled"></option>
            <option value="completed" data-i18n="status.completed"></option>
        </select>

        <select id="typeFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="daily" data-i18n="bookings.type.daily"></option>
            <option value="monthly" data-i18n="bookings.type.monthly"></option>
        </select>
    </div>

    <div class="alert" id="pageError" hidden></div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="bookings.col.id"></th>
                    <th data-i18n="bookings.col.parent"></th>
                    <th data-i18n="bookings.col.child"></th>
                    <th data-i18n="bookings.col.nursery"></th>
                    <th data-i18n="bookings.col.type"></th>
                    <th data-i18n="bookings.col.period"></th>
                    <th data-i18n="bookings.col.price"></th>
                    <th data-i18n="bookings.col.status"></th>
                    <th data-i18n="nurseries.col.actions"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <p class="muted" id="emptyMsg" data-i18n="bookings.empty" hidden></p>

    <div class="pagination">
        <button type="button" id="prevBtn" class="btn btn--ghost" data-i18n="common.prev"></button>
        <span id="pageInfo"></span>
        <button type="button" id="nextBtn" class="btn btn--ghost" data-i18n="common.next"></button>
    </div>
</div>

<div class="modal" id="modal" hidden>
    <div class="modal__box">
        <div class="modal__head">
            <h2 data-i18n="bookings.details"></h2>
            <button type="button" id="closeModal" class="btn btn--ghost">✕</button>
        </div>

        <dl class="details" id="details"></dl>

        <h3 class="subtitle" data-i18n="bookings.payments"></h3>
        <div id="paymentsBox"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tbody = document.getElementById('tbody');
const emptyMsg = document.getElementById('emptyMsg');
const statusFilter = document.getElementById('statusFilter');
const typeFilter = document.getElementById('typeFilter');
const pageError = document.getElementById('pageError');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const pageInfo = document.getElementById('pageInfo');
const modal = document.getElementById('modal');
const details = document.getElementById('details');
const paymentsBox = document.getElementById('paymentsBox');

let rows = [];
let currentPage = 1;
let lastPage = 1;
let selected = null;

function badge(status) {
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

    rows.forEach((b) => {
        const tr = document.createElement('tr');

        [
            b.id,
            b.parent?.name ?? '-',
            b.child?.name ?? '-',
            b.nursery?.name ?? '-',
            t('bookings.type.' + b.type),
            formatDate(b.start_date) + ' → ' + formatDate(b.end_date),
            b.price,
        ].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');
        tdStatus.appendChild(badge(b.status));
        tr.appendChild(tdStatus);

        const viewBtn = document.createElement('button');
        viewBtn.type = 'button';
        viewBtn.className = 'btn btn--ghost';
        viewBtn.textContent = t('common.view');
        viewBtn.addEventListener('click', () => openModal(b.id));
        const tdActions = document.createElement('td');
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
    if (statusFilter.value) query.set('status', statusFilter.value);
    if (typeFilter.value) query.set('type', typeFilter.value);

    const {
        ok,
        data
    } = await apiFetch('/admin/bookings?' + query);

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

function renderPayments() {
    const list = selected.payments ?? [];

    if (list.length === 0) {
        const p = document.createElement('p');
        p.className = 'muted';
        p.textContent = t('bookings.noPayments');
        paymentsBox.replaceChildren(p);
        return;
    }

    const table = document.createElement('table');
    table.className = 'table';

    const head = document.createElement('tr');
    ['payments.col.amount', 'payments.col.type', 'payments.col.status', 'payments.col.date'].forEach((key) => {
        const th = document.createElement('th');
        th.textContent = t(key);
        head.appendChild(th);
    });
    const thead = document.createElement('thead');
    thead.appendChild(head);
    table.appendChild(thead);

    const body = document.createElement('tbody');
    list.forEach((p) => {
        const tr = document.createElement('tr');

        [p.amount, t('payments.type.' + p.type)].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');
        tdStatus.appendChild(badge(p.status));
        tr.appendChild(tdStatus);

        const tdDate = document.createElement('td');
        tdDate.textContent = formatDate(p.created_at);
        tr.appendChild(tdDate);

        body.appendChild(tr);
    });
    table.appendChild(body);

    const wrap = document.createElement('div');
    wrap.className = 'table-wrap';
    wrap.appendChild(table);
    paymentsBox.replaceChildren(wrap);
}

function renderDetails() {
    details.replaceChildren();
    const b = selected;

    const commissionAmount = (Number(b.price) * Number(b.commission_rate) / 100).toFixed(3);
    const days = b.days_of_week ?
        b.days_of_week.split(',').map((d) => t('days.' + d)).join('، ') :
        null;

    addDetail('bookings.col.id', b.id);
    addDetail('bookings.col.status', badge(b.status));
    addDetail('bookings.col.parent', b.parent?.name);
    addDetail('bookings.email', b.parent?.email);
    addDetail('bookings.col.child', b.child?.name);
    addDetail('bookings.col.nursery', b.nursery?.name);
    addDetail('bookings.classroom', b.classroom?.name);
    addDetail('bookings.col.type', t('bookings.type.' + b.type));
    addDetail('bookings.col.period', formatDate(b.start_date) + ' → ' + formatDate(b.end_date));
    addDetail('bookings.days', days);
    addDetail('bookings.col.price', b.price);
    addDetail('bookings.commissionRate', b.commission_rate);
    addDetail('bookings.commissionAmount', commissionAmount);
    addDetail('bookings.acceptedAt', formatDate(b.accepted_at));
    addDetail('bookings.paidAt', formatDate(b.paid_at));

    if (b.status === 'rejected') {
        addDetail('bookings.rejectReason', b.reject_reason);
    }

    renderPayments();
}

async function openModal(id) {
    const {
        ok,
        data
    } = await apiFetch('/admin/bookings/' + id);

    if (!ok) {
        pageError.textContent = t('common.error');
        pageError.hidden = false;
        return;
    }

    selected = data;
    renderDetails();
    modal.hidden = false;
}

function closeModal() {
    modal.hidden = true;
    selected = null;
}

statusFilter.addEventListener('change', () => load(1));
typeFilter.addEventListener('change', () => load(1));
prevBtn.addEventListener('click', () => load(currentPage - 1));
nextBtn.addEventListener('click', () => load(currentPage + 1));
document.getElementById('closeModal').addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

document.addEventListener('langchange', () => {
    renderRows();
    if (selected) renderDetails();
});

load(1);
</script>
@endpush