import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);
window.Chart = Chart;

window.Alpine = Alpine;

window.alertCenter = (initialAlerts = []) => ({
	alerts: initialAlerts.map((alert, index) => ({ ...alert, id: Date.now() + index, visible: true })),
	timers: {},
	confirmation: null,
	confirmationResolver: null,
	styles: {
		success: { title: 'Berhasil', icon: 'fa-circle-check', iconBackground: 'bg-emerald-100 text-emerald-600', border: 'border-emerald-200' },
		error: { title: 'Terjadi Kesalahan', icon: 'fa-circle-exclamation', iconBackground: 'bg-red-100 text-red-600', border: 'border-red-200' },
		warning: { title: 'Perhatian', icon: 'fa-triangle-exclamation', iconBackground: 'bg-amber-100 text-amber-600', border: 'border-amber-200' },
		info: { title: 'Informasi', icon: 'fa-circle-info', iconBackground: 'bg-blue-100 text-blue-600', border: 'border-blue-200' },
	},
	init() {
		this.alerts.forEach((alert) => this.schedule(alert));
	},
	show(detail = {}) {
		const alert = {
			id: Date.now() + Math.random(),
			type: this.styles[detail.type] ? detail.type : 'info',
			title: detail.title || '',
			message: detail.message || '',
			visible: true,
		};

		this.alerts.push(alert);
		this.schedule(alert);
	},
	schedule(alert) {
		this.timers[alert.id] = setTimeout(() => this.dismiss(alert.id), alert.duration ?? 4500);
	},
	dismiss(id) {
		const alert = this.alerts.find((item) => item.id === id);
		if (!alert) return;

		alert.visible = false;
		clearTimeout(this.timers[id]);
		setTimeout(() => {
			this.alerts = this.alerts.filter((item) => item.id !== id);
		}, 250);
	},
	confirm(detail = {}) {
		this.confirmation = { title: detail.title || 'Konfirmasi', message: detail.message || 'Apakah Anda yakin?', confirmText: detail.confirmText || 'Ya, lanjutkan', cancelText: detail.cancelText || 'Batal', resolver: detail.resolver };
	},
	resolveConfirmation(confirmed) {
		this.confirmation?.resolver?.(confirmed);
		this.confirmation = null;
	},
});

window.showAlert = (message, type = 'info', title = '') => {
	window.dispatchEvent(new CustomEvent('app-alert', { detail: { message, type, title } }));
};

window.confirmAction = (message, options = {}) => new Promise((resolve) => {
	window.dispatchEvent(new CustomEvent('app-confirm', { detail: { ...options, message, resolver: resolve } }));
});

Alpine.start();