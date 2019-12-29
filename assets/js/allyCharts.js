function manageAllyChart() {
    //line
    let ctxL = document.getElementById("allyChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: ["lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche"],
            datasets: [{
                label: "Mon alliance (points)",
                data: [0, 2, 6, 20, 24, 26, 30, 40, 40, 46, 50, 50, 52, 54],
                backgroundColor: [
                    'rgba(28, 185, 28, .2)',
                ],
                borderColor: [
                    'rgba(32, 132, 232, 1)',
                ],
                borderWidth: 2
            },
                {
                    label: "Alliances (moyenne points)",
                    data: [36, 36, 36, 38, 38, 36, 38, 40, 40, 40, 40, 42, 42, 41],
                    backgroundColor: [
                        'rgba(235, 40, 40, .5)',
                    ],
                    borderColor: [
                        'rgba(255, 255, 255, .6)',
                    ],
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true
        }
    });
}

$(document).ready(function() {
    manageAllyChart();
});