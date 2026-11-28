@extends('admin.layouts.app')

@section('title_key', 'tickets.title')

@section('content')
<div class="card">
    <div class="toolbar">
        <select id="statusFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="open" data-i18n="status.open"></option>
            <option value="in_progress" data-i18n="status.in_progress"></option>
            <option value="closed" data-i18n="status.closed"></option>
        </select>

        <select id="typeFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="complaint" data-i18n="type.complaint"></option>
            <option value="inquiry" data-i18n="type.inquiry"></option>
            <option value="suggestion" data-i18n="type.suggestion"></option>
        </select>
    </div>

    <div class="alert" id="pageError" hidden></div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="tickets.col.id"></th>
                    <th data-i18n="tickets.col.user"></th>
                    <th data-i18n="tickets.col.type"></th>
                    <th data-i18n="tickets.col.category"></th>
                    <th data-i18n="tickets.col.nursery"></th>
                    <th data-i18n="tickets.col.status"></th>
                    <th data-i18n="tickets.col.date"></th>
                    <th data-i18n="nurseries.col.actions"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <p class="muted" id="emptyMsg" data-i18n="tickets.empty" hidden></p>

    <div class="pagination">
        <button type="button" id="prevBtn" class="btn btn--ghost" data-i18n="common.prev"></button>
        <span id="pageInfo"></span>
        <button type="button" id="nextBtn" class="btn btn--ghost" data-i18n="common.next"></button>
    </div>
</div>

<div class="modal" id="modal" hidden>
    <div class="modal__box">
        <div class="modal__head">
            <h2 data-i18n="tickets.details"></h2>
            <button type="button" id="closeModal" class="btn btn--ghost">✕</button>
        </div>

        <dl class="details" id="details"></dl>

        <div class="form-group">
            <label for="statusSelect" data-i18n="tickets.statusLabel"></label>
            <select id="statusSelect" class="input">
                <option value="open" data-i18n="status.open"></option>
                <option value="in_progress" data-i18n="status.in_progress"></option>
                <option value="closed" data-i18n="status.closed"></option>
            </select>
        </div>

        <div class="form-group">
            <label for="noteInput" data-i18n="tickets.adminNote"></label>
            <textarea id="noteInput" class="input" rows="3" maxlength="2000"></textarea>
        </div>

        <div class="alert" id="modalError" hidden></div>

        <div class="modal__actions">
            <button type="button" id="saveBtn" class="btn" data-i18n="tickets.save"></button>
        </div>
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
const statusSelect = document.getElementById('statusSelect');
const noteInput = document.getElementById('noteInput');
const saveBtn = document.getElementById('saveBtn');
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

    rows.forEach((tk) => {
        const tr = document.createElement('tr');

        [
            tk.id,
            tk.user?.name ?? '-',
            t('type.' + tk.type),
            tk.category ?? '-',
            tk.nursery?.name ?? '-',
        ].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');
        tdStatus.appendChild(statusBadge(tk.status));
        tr.appendChild(tdStatus);

        const tdDate = document.createElement('td');
        tdDate.textContent = formatDate(tk.created_at);
        tr.appendChild(tdDate);

        const tdActions = document.createElement('td');
        const viewBtn = document.createElement('button');
        viewBtn.type = 'button';
        viewBtn.className = 'btn btn--ghost';
        viewBtn.textContent = t('common.view');
        viewBtn.addEventListener('click', () => openModal(tk.id));
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
    } = await apiFetch('/admin/tickets?' + query);

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
    const tk = selected;

    addDetail('tickets.col.id', tk.id);
    addDetail('tickets.col.user', tk.user?.name);
    addDetail('tickets.email', tk.user?.email);
    addDetail('tickets.col.type', t('type.' + tk.type));
    addDetail('tickets.col.category', tk.category);
    addDetail('tickets.col.nursery', tk.nursery?.name);
    addDetail('tickets.booking', tk.booking_id);
    addDetail('tickets.description', tk.description);
    addDetail('tickets.col.status', statusBadge(tk.status));

    if (tk.status === 'closed') {
        addDetail('tickets.closedBy', tk.closer?.name);
        addDetail('tickets.closedAt', formatDate(tk.closed_at));
    }
}

async function openModal(id) {
    const {
        ok,
        data
    } = await apiFetch('/admin/tickets/' + id);

    if (!ok) {
        pageError.textContent = t('common.error');
        pageError.hidden = false;
        return;
    }

    selected = data;
    statusSelect.value = data.status;
    noteInput.value = data.admin_note ?? '';
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

async function save() {
    hideModalError();
    saveBtn.disabled = true;

    const {
        ok
    } = await apiFetch('/admin/tickets/' + selected.id, {
        method: 'PATCH',
        body: JSON.stringify({
            status: statusSelect.value,
            admin_note: noteInput.value,
        }),
    });

    saveBtn.disabled = false;

    if (!ok) {
        showModalError('common.error');
        return;
    }

    closeModal();
    load(currentPage);
}

statusFilter.addEventListener('change', () => load(1));
typeFilter.addEventListener('change', () => load(1));
prevBtn.addEventListener('click', () => load(currentPage - 1));
nextBtn.addEventListener('click', () => load(currentPage + 1));
saveBtn.addEventListener('click', save);
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