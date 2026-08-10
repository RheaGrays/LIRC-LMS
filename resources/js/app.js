import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

import QRCode from 'qrcode';
window.QRCode = QRCode;

// Import kiosk app to ensure it registers its components before Alpine.start()
import './kiosk/app';
import webcamApp from './register/webcam';

Alpine.data('webcamApp', webcamApp);

Alpine.start();
