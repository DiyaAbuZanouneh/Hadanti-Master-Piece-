@extends('admin.layouts.app')

@section('title_key', 'payments.title')

@section('content')
<div class="card">
    <div class="toolbar">
        <select id="statusFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="pending" data-i18n="status.pending"></option>
            <option value="paid" data-i18n="status.paid"></option>
            <option value="failed" data-i18n="status.failed"></option>
            <option value="refunded" data-i18n="status.refunded"></option>
        </select>

        <select id="typeFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="payment" data-i18n="payments.type.payment"></option>
            <option value="refund" data-i18n="payments.type.refund"></option>
        </select>
    </div>

    <div class="alert" id="pageError" hidden></div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="payments.col.id"></th>
                    <th data-i18n="payments.col.booking"></th>
                    <th data-i18n="payments.col.parent"></th>
                    <th data-i18n="payments.col.nursery"></th>
                    <th data-i18n="payments.col.amount"></th>
                    <th data-i18n="payments.col.type"></th>
                    <th data-i18n="payments.col.status"></th>
                    <th data-i18n="payments.col.ref"></th>
                    <th data-i18n="payments.col.date"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <p class="muted" id="emptyMsg" data-i18n="payments.empty" hidden></p>

    <div class="pagination">
        <button type="button" id="prevBtn" class="btn btn--ghost" data-i18n="common.prev"></button>
        <span id="pageInfo"></span>
        <button type="button" id="nextBtn" class="btn btn--ghost" data-i18n="common.next"></button>
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

let rows = [];
let currentPage = 1;
let lastPage = 1;

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

    rows.forEach((p) => {
        const tr = document.createElement('tr');

        [
            p.id,
            '#' + p.booking_id,
            p.booking?.parent?.name ?? '-',
            p.booking?.nursery?.name ?? '-',
            p.amount,
            t('payments.type.' + p.type),
        ].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');
        tdStatus.appendChild(badge(p.status));
        tr.appendChild(tdStatus);

        const tdRef = document.createElement('td');
        tdRef.textContent = p.gateway_ref ?? '-';
        tr.appendChild(tdRef);

        const tdDate = document.createElement('td');
        tdDate.textContent = formatDate(p.created_at);
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
    pageError.hidden = true;

    const query = new URLSearchParams({
        page
    });
    if (statusFilter.value) query.set('status', statusFilter.value);
    if (typeFilter.value) query.set('type', typeFilter.value);

    const {
        ok,
        data
    } = await apiFetch('/admin/payments?' + query);

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

statusFilter.addEventListener('change', () => load(1));
typeFilter.addEventListener('change', () => load(1));
prevBtn.addEventListener('click', () => load(currentPage - 1));
nextBtn.addEventListener('click', () => load(currentPage + 1));

document.addEventListener('langchange', renderRows);

load(1);
</script>
@endpush