import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Import kiosk app to ensure it registers its components before Alpine.start()
import './kiosk/app';
import webcamApp from './register/webcam';

Alpine.data('webcamApp', webcamApp);

Alpine.start();
