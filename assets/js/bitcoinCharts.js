function manageBitcoinChart() {
    //line
    let bitcoinPoints = $('#bitcoinPoints').data("bitcoinPoints");
    let bitcoinOtherPoints = $('#bitcoinOtherPoints').data("bitcoinOtherPoints");
    let bitcoinDate = $('#bitcoinDate').data("bitcoinDate");
    let ctxL = document.getElementById("bitcoinChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: bitcoinDate,
            datasets: [{
                label: "Mes Bitcoins",
                data: bitcoinPoints,
                backgroundColor: [
                    'rgba(28, 185, 28, .2)',
                ],
                borderColor: [
                    'rgba(32, 132, 232, 1)',
                ],
                borderWidth: 2
            },
                {
                    label: "Moyenne des Bitcoins (autres joueurs)",
                    data: bitcoinOtherPoints,
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
    manageBitcoinChart();
});