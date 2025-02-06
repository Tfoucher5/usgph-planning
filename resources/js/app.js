import './bootstrap';
// import './datatables';
import './menu';
import './toast';
import './tooltip';
import './helper';
import './validation';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import $ from 'jquery';
window.jQuery = $;
window.$ = $;

import Swal from 'sweetalert2';
window.Swal = Swal;

import Cookies from 'js-cookie';
window.Cookies = Cookies;

import {
  Calendar
} from '@fullcalendar/core';
window.Calendar = Calendar;
import dayGridPlugin from '@fullcalendar/daygrid';
window.dayGridPlugin = dayGridPlugin;
import timeGridPlugin from '@fullcalendar/timegrid';
window.timeGridPlugin = timeGridPlugin;
import listPlugin from '@fullcalendar/list';
window.listPlugin = listPlugin;
import interaction from '@fullcalendar/interaction';
window.interaction = interaction;
import multimonth from '@fullcalendar/multimonth';
window.multimonth = multimonth;
import scrollgrid from '@fullcalendar/scrollgrid';
window.scrollgrid = scrollgrid;
import interactionPlugin from '@fullcalendar/interaction';
window.interactionPlugin = interactionPlugin;
// import bootstrap5 from '@fullcalendar/bootstrap5';
// window.bootstrap5 = bootstrap5;

import {
  Grid,
  html
} from "gridjs";
window.Grid = Grid;
window.html = html;
import {
  frFR
} from "gridjs/l10n";
window.frFR = frFR;

// import jszip from 'jszip';
// import pdfmake from 'pdfmake';
// import DataTable from 'datatables.net-bs5';
// import 'datatables.net-buttons-bs5';
// import 'datatables.net-buttons/js/buttons.colVis.mjs';
// import 'datatables.net-buttons/js/buttons.html5.mjs';
// import 'datatables.net-buttons/js/buttons.print.mjs';
// import 'datatables.net-fixedcolumns-bs5';
// import 'datatables.net-fixedheader-bs5';
// import 'datatables.net-responsive-bs5';
// import 'datatables.net-searchbuilder-bs5';
// import 'datatables.net-searchpanes-bs5';
// import 'datatables.net-staterestore-bs5';
// window.DataTable = DataTable;
// window.JSZip = jszip;
// window.pdfMake = pdfmake;

import * as select2 from 'select2';
window.Select2 = select2;
import 'select2/dist/js/i18n/fr'; // il faut aussi préciser la langue dans déclaration du select

import moment from 'moment';
window.moment = moment;
moment.locale('fr');

import { TempusDominus, loadLocale, locale } from '@eonasdan/tempus-dominus';
window.TempusDominus = TempusDominus;
window.loadLocale = loadLocale;
window.locale = locale;
import { localization, name } from "@eonasdan/tempus-dominus/dist/locales/fr";
window.localization = localization;
window.name = name;

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

if (window.location.pathname === '/synthese') {
  console.log('Page synthese détectée, chargement de Chart.js...');
  import('chart.js/auto').then(Chart => {
    console.log('Chart.js chargé');
    window.Chart = Chart.default;

    console.log('window.Chart:', window.Chart);

    import('./hours-chart.js').then(() => {
      console.log('hours-chart.js chargé et prêt');

      const event = new Event('forceChartExecution');
      document.dispatchEvent(event);
    }).catch((error) => {
      console.error('Erreur lors du chargement de hours-chart.js:', error);
    });
  }).catch((error) => {
    console.error('Erreur lors du chargement de chart.js:', error);
  });
}

if (window.location.pathname.includes('/synthese/graphique')) {
  console.log('Page synthese graphique détectée, chargement de Chart.js...');

  // Chargement conditionnel de Chart.js
  import('chart.js/auto')
    .then((Chart) => {
      console.log('Chart.js chargé');
      window.Chart = Chart.default;
      console.log('window.Chart:', window.Chart);

      // Chargement conditionnel du fichier hours-chart-year.js
      import('./hours-chart-year.js')
        .then(() => {
          console.log('hours-chart-year.js chargé et prêt');

          // Déclenchement de l'événement pour forcer l'exécution des graphiques
          const event = new Event('forceChartExecution');
          document.dispatchEvent(event);
        })
        .catch((error) => {
          console.error('Erreur lors du chargement de hours-chart-year.js:', error);
        });
    })
    .catch((error) => {
      console.error('Erreur lors du chargement de Chart.js:', error);
    });
}


