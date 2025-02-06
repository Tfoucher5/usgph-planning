// Fonction de débogage globale
window.debugCharts = function () {
  console.log('Débogage manuel des graphiques');
  console.log('Chart.js disponible:', !!window.Chart);
  console.log('Données employee:', window.employee);
};

// Méthode principale
function initCharts() {
  console.log('Initialisation des graphiques');

  if (!window.Chart) {
    console.error('Chart.js non chargé');
    return;
  }

  if (!window.employee || window.employee.length === 0) {
    console.error('Aucune donnée employé');
    return;
  }

    const canvas = document.getElementById(`chart-${employee.id}`);

    if (!canvas) {
      console.error(`Pas de canvas pour l'employé ${employee.id}`);
      return;
    }

    try {
      const workWeeks = Object.values(employee.workWeeks);

      // Correction des années pour 2024
      const labels = workWeeks.map(week => `${week.annee} - S${week.semaine}`);

      // Calculer les heures travaillées hors heures supplémentaires
      const regularHours = workWeeks.map(week => week.total - week.heures_supp);
      const overtimeHours = workWeeks.map(week => week.heures_supp);

      console.log(`Création graphique pour employé ${employee.id}`);
      console.log('Labels:', labels);
      console.log('Heures Régulières:', regularHours);
      console.log('Heures Supplémentaires:', overtimeHours);

      new window.Chart(canvas, {
        type: 'bar',
        data: {
          labels: labels.reverse(),
          datasets: [
            {
              label: 'Heures Régulières',
              data: regularHours.reverse(),
              backgroundColor: 'rgba(54, 162, 235, 0.6)'
            },
            {
              label: 'Heures Supplémentaires',
              data: overtimeHours.reverse(),
              backgroundColor: 'rgba(255, 99, 132, 0.6)'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              stacked: true,
              min: 0,
              max: 45,
              ticks: {
                font: {
                  size: 12
                },
                stepSize: 5
              },
              grid: {
                color: '#ccc',
                lineWidth: 1,
              },
              tickMarkLength: 5,
              ticks: {
                callback: function(value) {
                  return value;
                }
              },
            },
            x: {
              stacked: true
            }
          }
        }
      });

      console.log(`Graphique créé avec succès pour employé ${employee.id}`);
    } catch (error) {
      console.error(`Erreur création graphique employé ${employee.id}:`, error);
    }
  }

document.addEventListener('DOMContentLoaded', initCharts);
document.addEventListener('forceChartExecution', initCharts);

setTimeout(initCharts, 1000);
