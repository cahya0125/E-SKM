@extends('layouts.admin')

@section('title', 'Grafik · e-SKM BPBD Kota Bandung')

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-bold text-[#172b4d]">Tren IKM Per Tahun</h2>
            <p class="mt-1 text-xs text-slate-400">Perkembangan nilai IKM dari tahun ke tahun</p>
            <div class="relative mt-4 h-56"><canvas id="trendChart" aria-label="Grafik tren IKM per tahun"></canvas></div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-bold text-[#172b4d]">Nilai Unsur Pelayanan</h2>
            <p class="mt-1 text-xs text-slate-400">Rata-rata nilai per unsur (skala 100)</p>
            <div class="relative mt-4 h-56"><canvas id="elementChart" aria-label="Grafik nilai unsur pelayanan"></canvas></div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-bold text-[#172b4d]">Distribusi Jenis Kelamin</h2>
            <p class="mt-1 text-xs text-slate-400">Komposisi responden</p>
            <div class="relative mt-3 h-56"><canvas id="genderChart" aria-label="Grafik distribusi jenis kelamin"></canvas></div>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-bold text-[#172b4d]">Distribusi Pekerjaan</h2>
            <p class="mt-1 text-xs text-slate-400">Profil pekerjaan responden</p>
            <div class="relative mt-4 h-56"><canvas id="jobChart" aria-label="Grafik distribusi pekerjaan"></canvas></div>
        </article>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
const grafikData = @json($data ?? []);
grafikData.trend = grafikData.trend ?? { labels: [], values: [] };
grafikData.elements = grafikData.elements ?? { labels: [], values: [] };
grafikData.gender = grafikData.gender ?? { labels: [], values: [] };
grafikData.jobs = grafikData.jobs ?? { labels: [], values: [] };
const chartFont = { family: 'Instrument Sans, sans-serif', size: 10 };
const gridColor = '#e8edf3';
const navy = '#1d2f54';
const red = '#c43b2d';
const blue = '#2864e8';

const valueLabels = {
    id: 'valueLabels',
    afterDatasetsDraw(chart) {
        const context = chart.ctx;
        context.save();
        context.fillStyle = navy;
        context.font = '600 10px Instrument Sans, sans-serif';
        context.textAlign = 'center';
        context.textBaseline = 'bottom';

        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const meta = chart.getDatasetMeta(datasetIndex);
            meta.data.forEach((element, index) => {
                const value = dataset.data[index];
                if (value === undefined || value === null) return;

                if (chart.config.type === 'doughnut') {
                    const point = element.getCenterPoint();
                    context.fillStyle = '#fff';
                    context.textBaseline = 'middle';
                    context.fillText(value, point.x, point.y);
                    return;
                }

                const properties = element.getProps(['x', 'y', 'base'], true);
                context.fillStyle = navy;
                if (chart.options.indexAxis === 'y') {
                    context.textAlign = 'left';
                    context.textBaseline = 'middle';
                    context.fillText(value, properties.x + 6, properties.y);
                } else if (chart.config.type === 'line') {
                    context.textAlign = 'center';
                    context.textBaseline = 'bottom';
                    context.fillText(value, properties.x, properties.y - 8);
                } else {
                    context.textAlign = 'center';
                    context.textBaseline = 'bottom';
                    context.fillText(value, properties.x, Math.min(properties.y, properties.base) - 6);
                }
            });
        });
        context.restore();
    },
};

Chart.register(valueLabels);

const sharedScales = {
    x: { grid: { color: gridColor, borderDash: [2, 3] }, ticks: { color: '#8da0bb', font: chartFont } },
    y: { grid: { color: gridColor, borderDash: [2, 3] }, ticks: { color: '#8da0bb', font: chartFont }, beginAtZero: false },
};

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels: grafikData.trend.labels, datasets: [{ data: grafikData.trend.values, borderColor: red, backgroundColor: red, borderWidth: 2.5, pointRadius: 5, pointHoverRadius: 6, tension: 0.3 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: sharedScales.x, y: { ...sharedScales.y, min: 60, max: 100, ticks: { ...sharedScales.y.ticks, stepSize: 10 } } } },
});

new Chart(document.getElementById('elementChart'), {
    type: 'bar',
    data: { labels: grafikData.elements.labels.map((_, index) => `U${index + 1}`), datasets: [{ data: grafikData.elements.values, backgroundColor: navy, borderRadius: 3, barPercentage: 0.72, categoryPercentage: 0.8 }] },
    options: { maintainAspectRatio: false, layout: { padding: { top: 14 } }, plugins: { legend: { display: false }, tooltip: { callbacks: { title: (items) => grafikData.elements.labels[items[0].dataIndex] } } }, scales: { x: sharedScales.x, y: { ...sharedScales.y, min: 60, max: Math.max(100, Math.ceil(Math.max(...grafikData.elements.values, 100) / 10) * 10 + 10), ticks: { ...sharedScales.y.ticks, stepSize: 10 } } } },
});

new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: { labels: grafikData.gender.labels, datasets: [{ data: grafikData.gender.values, backgroundColor: [navy, red], borderColor: '#fff', borderWidth: 5, spacing: 2 }] },
    options: { maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'bottom', labels: { color: navy, font: chartFont, boxWidth: 10, boxHeight: 10, padding: 16 } } } },
});

new Chart(document.getElementById('jobChart'), {
    type: 'bar',
    data: { labels: grafikData.jobs.labels, datasets: [{ data: grafikData.jobs.values, backgroundColor: red, borderRadius: 4, barPercentage: 0.68, categoryPercentage: 0.8 }] },
    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ...sharedScales.x, beginAtZero: true, suggestedMax: 360, ticks: { ...sharedScales.x.ticks, stepSize: 90 } }, y: { ...sharedScales.y, grid: { display: false } } } },
});
});
</script>
@endsection