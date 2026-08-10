import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

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

Alpine.start();
