import {
    Chart,
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
);

const BRAND = {
    crimson: '#AB1E23',
    gold: '#E6C280',
    navy: '#080D21',
    rose: '#E5989B',
    ink: '#0F141E',
    alice: '#E6EBF4',
    linen: '#ECEAE1',
};

const formatMoney = (value, symbol = '₹') => {
    const amount = Number(value) || 0;

    return `${symbol}${amount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
};

const baseChartOptions = (currencySymbol) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: BRAND.ink,
                font: { family: 'Instrument Sans, sans-serif', size: 11 },
                boxWidth: 12,
            },
        },
        tooltip: {
            callbacks: {
                label: (context) => {
                    const label = context.dataset.label ? `${context.dataset.label}: ` : '';

                    return label + formatMoney(context.parsed.y ?? context.parsed, currencySymbol);
                },
            },
        },
    },
});

const readChartPayload = () => {
    const root = document.getElementById('finance-overview-root');

    if (! root) {
        return null;
    }

    const dataNode = document.getElementById('finance-overview-chart-data');

    if (! dataNode?.textContent?.trim()) {
        return null;
    }

    try {
        return {
            root,
            charts: JSON.parse(dataNode.textContent),
        };
    } catch (error) {
        console.error('Finance overview: could not parse chart data.', error);

        return null;
    }
};

const initFinanceOverviewCharts = () => {
    const payload = readChartPayload();

    if (! payload) {
        return;
    }

    const { root, charts } = payload;
    const currencySymbol = charts.currency_symbol ?? '₹';
    const trend = charts.trend ?? { labels: [], collections: [], expenses: [], net: [] };

    const trendCanvas = document.getElementById('finance-chart-trend');

    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'bar',
            data: {
                labels: trend.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: root.dataset.labelCollections ?? 'Collections',
                        data: trend.collections,
                        backgroundColor: `${BRAND.gold}CC`,
                        borderColor: BRAND.gold,
                        borderWidth: 1,
                        borderRadius: 6,
                        order: 2,
                    },
                    {
                        type: 'bar',
                        label: root.dataset.labelExpenses ?? 'Expenses',
                        data: trend.expenses,
                        backgroundColor: `${BRAND.crimson}99`,
                        borderColor: BRAND.crimson,
                        borderWidth: 1,
                        borderRadius: 6,
                        order: 3,
                    },
                    {
                        type: 'line',
                        label: root.dataset.labelNet ?? 'Net',
                        data: trend.net,
                        borderColor: BRAND.navy,
                        backgroundColor: `${BRAND.navy}15`,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: BRAND.navy,
                        order: 1,
                    },
                ],
            },
            options: {
                ...baseChartOptions(currencySymbol),
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: BRAND.ink, font: { size: 11 } },
                    },
                    y: {
                        grid: { color: `${BRAND.alice}` },
                        ticks: {
                            color: BRAND.ink,
                            callback: (value) => formatMoney(value, currencySymbol),
                        },
                    },
                },
            },
        });
    }

    const collectionsCanvas = document.getElementById('finance-chart-collections');

    if (collectionsCanvas) {
        const data = charts.collections_by_type ?? { labels: [], values: [], colors: [] };

        new Chart(collectionsCanvas, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: data.colors?.length ? data.colors : [BRAND.gold, BRAND.crimson, BRAND.navy, BRAND.rose],
                    borderColor: BRAND.linen,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: BRAND.ink,
                            font: { family: 'Instrument Sans, sans-serif', size: 11 },
                            boxWidth: 10,
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.label}: ${formatMoney(context.parsed, currencySymbol)}`,
                        },
                    },
                },
            },
        });
    }

    const expensesCanvas = document.getElementById('finance-chart-expenses');

    if (expensesCanvas) {
        const data = charts.expenses_by_tag ?? { labels: [], values: [], colors: [] };

        new Chart(expensesCanvas, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: root.dataset.labelExpenseBreakdown ?? 'Expenses',
                    data: data.values,
                    backgroundColor: data.colors?.length ? data.colors : BRAND.crimson,
                    borderColor: BRAND.crimson,
                    borderWidth: 1,
                    borderRadius: 6,
                }],
            },
            options: {
                ...baseChartOptions(currencySymbol),
                indexAxis: 'y',
                plugins: {
                    ...baseChartOptions(currencySymbol).plugins,
                    legend: { display: false },
                },
                scales: {
                    x: {
                        grid: { color: BRAND.alice },
                        ticks: {
                            color: BRAND.ink,
                            callback: (value) => formatMoney(value, currencySymbol),
                        },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: BRAND.ink, font: { size: 11 } },
                    },
                },
            },
        });
    }
};

const bootFinanceOverviewCharts = () => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFinanceOverviewCharts);
    } else {
        initFinanceOverviewCharts();
    }
};

bootFinanceOverviewCharts();
