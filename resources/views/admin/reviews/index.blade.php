@extends('admin.layouts.app')

@section('title_key', 'reviews.title')

@section('content')
<div class="card">
    <div class="toolbar">
        <select id="starsFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="5">★ 5</option>
            <option value="4">★ 4</option>
            <option value="3">★ 3</option>
            <option value="2">★ 2</option>
            <option value="1">★ 1</option>
        </select>

        <select id="hiddenFilter" class="input input--auto">
            <option value="" data-i18n="status.all"></option>
            <option value="0" data-i18n="reviews.visible"></option>
            <option value="1" data-i18n="reviews.hidden"></option>
        </select>
    </div>

    <div class="alert" id="pageError" hidden></div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="reviews.col.id"></th>
                    <th data-i18n="reviews.col.parent"></th>
                    <th data-i18n="reviews.col.nursery"></th>
                    <th data-i18n="reviews.col.stars"></th>
                    <th data-i18n="reviews.col.comment"></th>
                    <th data-i18n="reviews.col.status"></th>
                    <th data-i18n="reviews.col.date"></th>
                    <th data-i18n="nurseries.col.actions"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <p class="muted" id="emptyMsg" data-i18n="reviews.empty" hidden></p>

    <div class="pagination">
        <button type="button" id="prevBtn" class="btn btn--ghost" data-i18n="common.prev"></button>
        <span id="pageInfo"></span>
        <button type="button" id="nextBtn" class="btn btn--ghost" data-i18n="common.next"></button>
    </div>
</div>

<div class="modal" id="modal" hidden>
    <div class="modal__box">
        <div class="modal__head">
            <h2 data-i18n="reviews.details"></h2>
            <button type="button" id="closeModal" class="btn btn--ghost">✕</button>
        </div>

        <dl class="details" id="details"></dl>

        <div class="alert" id="modalError" hidden></div>

        <div class="modal__actions">
            <button type="button" id="toggleBtn" class="btn"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const tbody = document.getElementById('tbody');
const emptyMsg = document.getElementById('emptyMsg');
const starsFilter = document.getElementById('starsFilter');
const hiddenFilter = document.getElementById('hiddenFilter');
const pageError = document.getElementById('pageError');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const pageInfo = document.getElementById('pageInfo');
const modal = document.getElementById('modal');
const details = document.getElementById('details');
const toggleBtn = document.getElementById('toggleBtn');
const modalError = document.getElementById('modalError');

let rows = [];
let currentPage = 1;
let lastPage = 1;
let selected = null;

function starsText(count) {
    return '★'.repeat(count) + '☆'.repeat(5 - count);
}

function formatDate(value) {
    return value ? new Date(value).toLocaleDateString(getLang()) : '-';
}

function visibilityBadge(review) {
    const span = document.createElement('span');
    span.className = 'badge ' + (review.is_hidden ? 'badge--rejected' : 'badge--active');
    span.textContent = t(review.is_hidden ? 'reviews.hidden' : 'reviews.visible');
    return span;
}

function shorten(text) {
    if (!text) return '-';
    return text.length > 60 ? text.slice(0, 60) + '…' : text;
}

function renderRows() {
    tbody.replaceChildren();
    emptyMsg.hidden = rows.length > 0;

    rows.forEach((r) => {
        const tr = document.createElement('tr');

        [
            r.id,
            r.parent?.name ?? '-',
            r.nursery?.name ?? '-',
            starsText(r.stars),
            shorten(r.comment),
        ].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');
        tdStatus.appendChild(visibilityBadge(r));
        tr.appendChild(tdStatus);

        const tdDate = document.createElement('td');
        tdDate.textContent = formatDate(r.created_at);
        tr.appendChild(tdDate);

        const viewBtn = document.createElement('button');
        viewBtn.type = 'button';
        viewBtn.className = 'btn btn--ghost';
        viewBtn.textContent = t('common.view');
        viewBtn.addEventListener('click', () => openModal(r));
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
    if (starsFilter.value) query.set('stars', starsFilter.value);
    if (hiddenFilter.value) query.set('hidden', hiddenFilter.value);

    const {
        ok,
        data
    } = await apiFetch('/admin/reviews?' + query);

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
    const r = selected;

    addDetail('reviews.col.id', r.id);
    addDetail('reviews.col.parent', r.parent?.name);
    addDetail('reviews.col.nursery', r.nursery?.name);
    addDetail('reviews.col.stars', starsText(r.stars));
    addDetail('reviews.comment', r.comment);
    addDetail('reviews.reply', r.reply);
    addDetail('reviews.col.status', visibilityBadge(r));
    addDetail('reviews.col.date', formatDate(r.created_at));

    toggleBtn.textContent = t(r.is_hidden ? 'reviews.show' : 'reviews.hide');
    toggleBtn.className = 'btn ' + (r.is_hidden ? '' : 'btn--danger');
}

function openModal(review) {
    selected = review;
    modalError.hidden = true;
    renderDetails();
    modal.hidden = false;
}

function closeModal() {
    modal.hidden = true;
    selected = null;
}

async function toggleVisibility() {
    const hide = !selected.is_hidden;

    if (!confirm(t(hide ? 'reviews.confirmHide' : 'reviews.confirmShow'))) return;

    modalError.hidden = true;
    toggleBtn.disabled = true;

    const {
        ok
    } = await apiFetch(`/admin/reviews/${selected.id}/visibility`, {
        method: 'PATCH',
        body: JSON.stringify({
            is_hidden: hide
        }),
    });

    toggleBtn.disabled = false;

    if (!ok) {
        modalError.textContent = t('common.error');
        modalError.hidden = false;
        return;
    }

    closeModal();
    load(currentPage);
}

starsFilter.addEventListener('change', () => load(1));
hiddenFilter.addEventListener('change', () => load(1));
prevBtn.addEventListener('click', () => load(currentPage - 1));
nextBtn.addEventListener('click', () => load(currentPage + 1));
toggleBtn.addEventListener('click', toggleVisibility);
document.getElementById('closeModal').addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

document.addEventListener('langchange', () => {
    renderRows();
    if (selected) renderDetails();
    if (!modalError.hidden) modalError.textContent = t('common.error');
});

load(1);
</script>
@endpush