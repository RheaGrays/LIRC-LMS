import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Global error boundary — catch uncaught exceptions and trigger fallback if Alpine fails
window.addEventListener('error', (event) => {
    console.error('[LEMS] Uncaught error:', event.error || event.message);
    if (!window.Alpine || !document.querySelector('[x-data]')) {
        const fallback = document.getElementById('js-crash-fallback');
        if (fallback) fallback.style.display = 'flex';
    }
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('[LEMS] Unhandled promise rejection:', event.reason);
});

import QRCode from 'qrcode';
window.QRCode = QRCode;

import flatpickr from 'flatpickr';
window.flatpickr = flatpickr;

import TomSelect from 'tom-select';
window.TomSelect = TomSelect;

import Chart from 'chart.js/auto';
window.Chart = Chart;

// Import kiosk app to ensure it registers its components before Alpine.start()
import './kiosk/app';
import webcamApp from './register/webcam';

Alpine.data('webcamApp', webcamApp);

try {
    Alpine.start();
} catch (e) {
    console.error('[LEMS] Failed to start Alpine.js:', e);
    const fallback = document.getElementById('js-crash-fallback');
    if (fallback) fallback.style.display = 'flex';
}
