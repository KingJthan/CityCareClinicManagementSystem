document.addEventListener('DOMContentLoaded', function () {
    const patientsCanvas = document.getElementById('patientsChart');
    const drugsCanvas = document.getElementById('drugsChart');

    const dashboardData = window.dashboardData || {
        patientsByGender: {
            labels: ['Male', 'Female'],
            values: [0, 0]
        },
        drugsByCategory: {
            labels: [],
            values: []
        }
    };

    const hasValues = function (values) {
        return Array.isArray(values) && values.some(function (value) {
            return Number(value) > 0;
        });
    };

    const emptyStatePlugin = {
        id: 'emptyState',
        afterDraw: function (chart) {
            const values = chart.data.datasets[0].data;

            if (hasValues(values)) {
                return;
            }

            const ctx = chart.ctx;
            const chartArea = chart.chartArea;
            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.fillStyle = '#667789';
            ctx.font = '700 14px Manrope, Arial, sans-serif';
            ctx.fillText('No chart data yet', centerX, centerY);
            ctx.restore();
        }
    };

    const chartColors = {
        teal: '#0f9f9a',
        blue: '#0f6cbf',
        navy: '#082f49',
        gold: '#d49a2a',
        mist: '#edf7f6',
        muted: '#667789',
        line: '#dfeaf2'
    };

    if (patientsCanvas && typeof Chart !== 'undefined') {
        new Chart(patientsCanvas, {
            type: 'bar',
            data: {
                labels: dashboardData.patientsByGender.labels,
                datasets: [{
                    label: 'Patients',
                    data: dashboardData.patientsByGender.values,
                    borderWidth: 0,
                    borderRadius: 16,
                    borderSkipped: false,
                    backgroundColor: [chartColors.teal, chartColors.blue, chartColors.gold, chartColors.navy],
                    hoverBackgroundColor: [chartColors.blue, chartColors.navy, '#b87918', chartColors.teal]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: chartColors.navy,
                        padding: 12,
                        cornerRadius: 12,
                        titleFont: { weight: '700' },
                        bodyFont: { weight: '700' }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: chartColors.muted,
                            font: { weight: '700' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: chartColors.line },
                        ticks: {
                            precision: 0,
                            color: chartColors.muted,
                            font: { weight: '700' }
                        }
                    }
                }
            },
            plugins: [emptyStatePlugin]
        });
    }

    if (drugsCanvas && typeof Chart !== 'undefined') {
        new Chart(drugsCanvas, {
            type: 'pie',
            data: {
                labels: dashboardData.drugsByCategory.labels,
                datasets: [{
                    data: dashboardData.drugsByCategory.values,
                    backgroundColor: [
                        chartColors.teal,
                        chartColors.blue,
                        chartColors.gold,
                        chartColors.navy,
                        '#38bdf8',
                        '#14b8a6',
                        '#f59e0b',
                        '#475569'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            color: chartColors.muted,
                            font: { weight: '700' },
                            padding: 16,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: chartColors.navy,
                        padding: 12,
                        cornerRadius: 12,
                        titleFont: { weight: '700' },
                        bodyFont: { weight: '700' }
                    }
                }
            },
            plugins: [emptyStatePlugin]
        });
    }
});
