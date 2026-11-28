@extends('admin.layouts.app')

@section('title_key', 'ageGroups.title')

@section('content')
<div class="card">
    <div class="toolbar">
        <button type="button" id="addBtn" class="btn" data-i18n="ageGroups.add"></button>
    </div>

    <div class="alert" id="pageError" hidden></div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th data-i18n="ageGroups.col.name"></th>
                    <th data-i18n="ageGroups.col.range"></th>
                    <th data-i18n="ageGroups.col.classrooms"></th>
                    <th data-i18n="ageGroups.col.status"></th>
                    <th data-i18n="nurseries.col.actions"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <p class="muted" id="emptyMsg" data-i18n="ageGroups.empty" hidden></p>
</div>

<div class="modal" id="modal" hidden>
    <form class="modal__box" id="form">
        <div class="modal__head">
            <h2 id="modalTitle"></h2>
            <button type="button" id="closeModal" class="btn btn--ghost">✕</button>
        </div>

        <div class="form-group">
            <label for="nameInput" data-i18n="ageGroups.name"></label>
            <input type="text" id="nameInput" class="input" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="minInput" data-i18n="ageGroups.min"></label>
            <input type="number" id="minInput" class="input" min="0" max="240" step="1" required>
        </div>

        <div class="form-group">
            <label for="maxInput" data-i18n="ageGroups.max"></label>
            <input type="number" id="maxInput" class="input" min="0" max="240" step="1" required>
        </div>

        <div class="form-group form-check">
            <label>
                <input type="checkbox" id="activeInput" checked>
                <span data-i18n="ageGroups.active"></span>
            </label>
        </div>

        <div class="alert" id="modalError" hidden></div>

        <div class="modal__actions">
            <button type="submit" id="saveBtn" class="btn" data-i18n="common.save"></button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const tbody = document.getElementById('tbody');
const emptyMsg = document.getElementById('emptyMsg');
const pageError = document.getElementById('pageError');
const modal = document.getElementById('modal');
const form = document.getElementById('form');
const modalTitle = document.getElementById('modalTitle');
const nameInput = document.getElementById('nameInput');
const minInput = document.getElementById('minInput');
const maxInput = document.getElementById('maxInput');
const activeInput = document.getElementById('activeInput');
const saveBtn = document.getElementById('saveBtn');
const modalError = document.getElementById('modalError');

let rows = [];
let editingId = null;
let pageErrorKey = null;
let modalErrorKey = null;

function showPageError(key) {
    pageErrorKey = key;
    pageError.textContent = t(key);
    pageError.hidden = false;
}

function hidePageError() {
    pageErrorKey = null;
    pageError.hidden = true;
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

function actionButton(textKey, className, handler) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = className;
    btn.textContent = t(textKey);
    btn.addEventListener('click', handler);
    return btn;
}

function renderRows() {
    tbody.replaceChildren();
    emptyMsg.hidden = rows.length > 0;

    rows.forEach((g) => {
        const tr = document.createElement('tr');

        [g.name, g.min_months + '-' + g.max_months, g.classrooms_count].forEach((text) => {
            const td = document.createElement('td');
            td.textContent = text;
            tr.appendChild(td);
        });

        const badge = document.createElement('span');
        badge.className = 'badge badge--' + (g.is_active ? 'active' : 'inactive');
        badge.textContent = t(g.is_active ? 'status.active' : 'status.inactive');
        const tdStatus = document.createElement('td');
        tdStatus.appendChild(badge);
        tr.appendChild(tdStatus);

        const actions = document.createElement('div');
        actions.className = 'actions';
        actions.append(
            actionButton('common.edit', 'btn btn--ghost', () => openModal(g)),
            actionButton('common.delete', 'btn btn--danger', () => remove(g))
        );
        const tdActions = document.createElement('td');
        tdActions.appendChild(actions);
        tr.appendChild(tdActions);

        tbody.appendChild(tr);
    });
}

async function load() {
    const {
        ok,
        data
    } = await apiFetch('/admin/age-groups');

    if (!ok) {
        showPageError('common.error');
        return;
    }

    rows = data;
    renderRows();
}

function setModalTitle() {
    modalTitle.textContent = t(editingId ? 'ageGroups.edit' : 'ageGroups.add');
}

function openModal(group = null) {
    editingId = group ? group.id : null;
    nameInput.value = group ? group.name : '';
    minInput.value = group ? group.min_months : '';
    maxInput.value = group ? group.max_months : '';
    activeInput.checked = group ? group.is_active : true;
    hideModalError();
    setModalTitle();
    modal.hidden = false;
    nameInput.focus();
}

function closeModal() {
    modal.hidden = true;
    editingId = null;
}

async function remove(group) {
    if (!confirm(t('ageGroups.confirmDelete'))) return;

    hidePageError();

    const {
        ok,
        status
    } = await apiFetch('/admin/age-groups/' + group.id, {
        method: 'DELETE',
    });

    if (!ok) {
        showPageError(status === 409 ? 'ageGroups.inUse' : 'common.error');
        return;
    }

    load();
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideModalError();

    const payload = {
        name: nameInput.value.trim(),
        min_months: Number(minInput.value),
        max_months: Number(maxInput.value),
        is_active: activeInput.checked,
    };

    if (payload.max_months <= payload.min_months) {
        showModalError('ageGroups.rangeInvalid');
        return;
    }

    saveBtn.disabled = true;

    const {
        ok,
        status
    } = await apiFetch(
        editingId ? '/admin/age-groups/' + editingId : '/admin/age-groups', {
            method: editingId ? 'PUT' : 'POST',
            body: JSON.stringify(payload),
        }
    );

    saveBtn.disabled = false;

    if (!ok) {
        showModalError(status === 422 ? 'ageGroups.invalid' : 'common.error');
        return;
    }

    closeModal();
    load();
});

document.getElementById('addBtn').addEventListener('click', () => openModal());
document.getElementById('closeModal').addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
});

document.addEventListener('langchange', () => {
    renderRows();
    if (!modal.hidden) setModalTitle();
    if (pageErrorKey) pageError.textContent = t(pageErrorKey);
    if (modalErrorKey) modalError.textContent = t(modalErrorKey);
});

load();
</script>
@endpush