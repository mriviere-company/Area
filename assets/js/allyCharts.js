function manageAllianceChart() {
    //line
    let allyPoints = $('#allyPoints').data("allyPoints");
    let allyOtherPoints = $('#allyOtherPoints').data("allyOtherPoints");
    let allyDate = $('#allyDate').data("allyDate");
    let ctxL = document.getElementById("allyChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: allyDate,
            datasets: [{
                label: "Mon alliance (points)",
                data: allyPoints,
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
                    data: allyOtherPoints,
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
    manageAllianceChart();
});