@extends('admin.layouts.app')

@section('title_key', 'dashboard.title')

@section('content')
<div class="alert" id="pageError" hidden></div>

<div class="stats-grid">
    <a class="stat" href="/admin/users">
        <div class="stat__value" id="statUsers">-</div>
        <div class="stat__label" data-i18n="dashboard.cards.users"></div>
    </a>
    <a class="stat" href="/admin/nurseries">
        <div class="stat__value" id="statNurseries">-</div>
        <div class="stat__label" data-i18n="dashboard.cards.nurseries"></div>
    </a>
    <a class="stat" href="/admin/nurseries">
        <div class="stat__value" id="statPending">-</div>
        <div class="stat__label" data-i18n="dashboard.cards.pendingNurseries"></div>
    </a>
    <a class="stat" href="/admin/tickets">
        <div class="stat__value" id="statTickets">-</div>
        <div class="stat__label" data-i18n="dashboard.cards.openTickets"></div>
    </a>

    <a class="stat" href="/admin/bookings">
        <div class="stat__value" id="statBookings">-</div>
        <div class="stat__label" data-i18n="dashboard.cards.bookings"></div>
    </a>
    <a class="stat" href="/admin/payments">
        <div class="stat__value" id="statRevenue">-</div>
        <div class="stat__label" data-i18n="dashboard.cards.revenue"></div>
    </a>
    <a class="stat" href="/admin/bookings">
        <div class="stat__value" id="statCommission">-</div>
        <div class="stat__label" data-i18n="dashboard.cards.commission"></div>
    </a>

</div>

<div class="charts-grid">
    <div class="card">
        <h2 class="chart__title" data-i18n="dashboard.charts.usersByRole"></h2>
        <div id="chartUsersByRole"></div>
    </div>
    <div class="card">
        <h2 class="chart__title" data-i18n="dashboard.charts.nurseriesByStatus"></h2>
        <div id="chartNurseries"></div>
    </div>
    <div class="card">
        <h2 class="chart__title" data-i18n="dashboard.charts.ticketsByStatus"></h2>
        <div id="chartTickets"></div>
    </div>
    <div class="card">
        <h2 class="chart__title" data-i18n="dashboard.charts.usersPerMonth"></h2>
        <div id="chartUsersMonthly"></div>
    </div>

    <div class="card">
        <h2 class="chart__title" data-i18n="dashboard.charts.bookingsByStatus"></h2>
        <div id="chartBookingsStatus"></div>
    </div>
    <div class="card">
        <h2 class="chart__title" data-i18n="dashboard.charts.bookingsByType"></h2>
        <div id="chartBookingsType"></div>
    </div>
    <div class="card">
        <h2 class="chart__title" data-i18n="dashboard.charts.revenuePerMonth"></h2>
        <div id="chartRevenue"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const SVG_NS = 'http://www.w3.org/2000/svg';
const PALETTE_SIZE = 4;

const pageError = document.getElementById('pageError');
let stats = null;

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

function noData(container) {
    container.replaceChildren(el('p', 'muted', t('dashboard.noData')));
}

function renderDonut(container, data, labelFn) {
    const entries = Object.entries(data);
    const total = entries.reduce((sum, [, value]) => sum + value, 0);

    if (total === 0) {
        noData(container);
        return;
    }

    const svg = document.createElementNS(SVG_NS, 'svg');
    svg.setAttribute('viewBox', '0 0 36 36');
    svg.setAttribute('class', 'donut__svg');

    let offset = 0;
    entries.forEach(([, value], i) => {
        if (!value) return;

        const pct = (value / total) * 100;
        const circle = document.createElementNS(SVG_NS, 'circle');
        circle.setAttribute('cx', '18');
        circle.setAttribute('cy', '18');
        circle.setAttribute('r', '15.9155');
        circle.setAttribute('fill', 'none');
        circle.setAttribute('stroke-width', '5');
        circle.setAttribute('stroke-dasharray', pct + ' ' + (100 - pct));
        circle.setAttribute('stroke-dashoffset', String(-offset));
        circle.style.stroke = 'var(--c' + (i % PALETTE_SIZE) + ')';
        svg.appendChild(circle);

        offset += pct;
    });

    const wrap = el('div', 'donut__wrap');
    wrap.append(svg, el('div', 'donut__total', total));

    const legend = el('ul', 'legend');
    entries.forEach(([key, value], i) => {
        const li = el('li');
        const dot = el('span', 'legend__dot');
        dot.style.background = 'var(--c' + (i % PALETTE_SIZE) + ')';
        li.append(dot, el('span', '', labelFn(key) + ': ' + value));
        legend.appendChild(li);
    });

    const box = el('div', 'donut');
    box.append(wrap, legend);
    container.replaceChildren(box);
}

function renderBars(container, data, labelFn) {
    const entries = Object.entries(data);
    const max = Math.max(...entries.map(([, value]) => value), 0);

    if (max === 0) {
        noData(container);
        return;
    }

    const box = el('div', 'bars');
    entries.forEach(([key, value], i) => {
        const track = el('div', 'bar__track');
        const fill = el('div', 'bar__fill');
        fill.style.width = (value / max) * 100 + '%';
        fill.style.background = 'var(--c' + (i % PALETTE_SIZE) + ')';
        track.appendChild(fill);

        const row = el('div', 'bar__row');
        row.append(el('span', '', labelFn(key)), track, el('span', 'bar__value', value));
        box.appendChild(row);
    });

    container.replaceChildren(box);
}

function formatMoney(value) {
    return Number(value).toLocaleString(getLang(), {
        maximumFractionDigits: 3
    });
}

function renderColumns(container, items, key = 'count', format = (v) => v) {
    const max = Math.max(...items.map((item) => item[key]), 0);

    if (max === 0) {
        noData(container);
        return;
    }

    const box = el('div', 'columns');
    items.forEach((item) => {
        const [year, month] = item.month.split('-');
        const label = new Date(Number(year), Number(month) - 1, 1)
            .toLocaleDateString(getLang(), {
                month: 'short'
            });

        const bar = el('div', 'column__bar');
        bar.style.height = (item[key] / max) * 100 + '%';

        const area = el('div', 'column__area');
        area.appendChild(bar);

        const column = el('div', 'column');
        column.append(area, el('span', '', label), el('strong', '', format(item[key])));
        box.appendChild(column);
    });

    container.replaceChildren(box);
}

function render() {
    if (!stats) return;

    document.getElementById('statUsers').textContent = stats.totals.users;
    document.getElementById('statNurseries').textContent = stats.totals.nurseries;
    document.getElementById('statPending').textContent = stats.totals.pending_nurseries;
    document.getElementById('statTickets').textContent = stats.totals.open_tickets;
    document.getElementById('statBookings').textContent = stats.totals.bookings;
    document.getElementById('statRevenue').textContent = formatMoney(stats.totals.revenue);
    document.getElementById('statCommission').textContent = formatMoney(stats.totals.commission);

    renderDonut(document.getElementById('chartUsersByRole'), stats.users_by_role, (k) => t('role.' + k));
    renderBars(document.getElementById('chartNurseries'), stats.nurseries_by_status, (k) => t('status.' + k));
    renderBars(document.getElementById('chartTickets'), stats.tickets_by_status, (k) => t('status.' + k));
    renderColumns(document.getElementById('chartUsersMonthly'), stats.users_per_month);
    renderBars(document.getElementById('chartBookingsStatus'), stats.bookings_by_status, (k) => t('status.' + k));
    renderDonut(document.getElementById('chartBookingsType'), stats.bookings_by_type, (k) => t('bookings.type.' + k));
    renderColumns(document.getElementById('chartRevenue'), stats.revenue_per_month, 'total', formatMoney);
}

async function loadStats() {
    const {
        ok,
        data
    } = await apiFetch('/admin/stats');

    if (!ok) {
        pageError.textContent = t('common.error');
        pageError.hidden = false;
        return;
    }

    stats = data;
    render();
}

document.addEventListener('langchange', render);

loadStats();
</script>
@endpush