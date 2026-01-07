import './bootstrap';
import '../css/app.css';

// Import Font Awesome CSS
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import Flowbite
import 'flowbite';

// Import Chart.js
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Import SweetAlert2
import Swal from 'sweetalert2';
import 'sweetalert2/src/sweetalert2.scss';
window.Swal = Swal;

// Import Inter Font
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';

// Import Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Ensure font sizes are applied after page load
document.addEventListener('DOMContentLoaded', () => {
    // Force font size application for any dynamically loaded content
    const style = document.createElement('style');
    style.textContent = `
        /* Additional font size enforcement */
        .text-xs, .text-sm, .text-base, .text-lg, .text-xl, .text-2xl, .text-3xl, .text-4xl {
            font-size: inherit !important;
        }
        
        /* Override Tailwind default font sizes */
        .text-xs { font-size: 14px !important; }
        .text-sm { font-size: 14px !important; }
        .text-base { font-size: 14px !important; }
        .text-lg { font-size: 20px !important; }
        .text-xl { font-size: 20px !important; }
        .text-2xl { font-size: 20px !important; }
        .text-3xl { font-size: 20px !important; }
        .text-4xl { font-size: 20px !important; }
    `;
    document.head.appendChild(style);
});
