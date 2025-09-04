jQuery(document).ready(function($) {
    'use strict';

    if (typeof eim_stats_data !== 'undefined') {
        eim_stats_data.forEach(function(occasion, index) {
            var ctx = document.getElementById('eim-chart-' + index);
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['حاضر', 'معتذر', 'بانتظار الرد'],
                        datasets: [{
                            label: 'Guest Responses',
                            data: [
                                occasion.attending_guests,
                                occasion.not_attending,
                                occasion.pending
                            ],
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(201, 203, 207, 0.7)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(201, 203, 207, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'استجابات المدعوين: ' + occasion.occasion_name
                            }
                        }
                    }
                });
            }
        });
    }
});
