function managePdgChart() {
    //line
    let pdgPoints = $('#pdgPoints').data("pdgPoints");
    let playerPoints = $('#playerPoints').data("playerPoints");
    let pdgOtherPoints = $('#pdgOtherPoints').data("pdgOtherPoints");
    let otherAllPoints = $('#pointOtherPoints').data("pointOtherPoints");
    let pdgDate = $('#pdgDate').data("pdgDate");
    let ctxL = document.getElementById("pdgChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: pdgDate,
            datasets: [{
                label: "Mes Points",
                data: playerPoints,
                backgroundColor: [
                    'rgba(28, 185, 28, .2)',
                ],
                borderColor: [
                    'rgba(32, 132, 232, 1)',
                ],
                borderWidth: 2
            },
                {
                    label: "Moyenne des PDG (autres joueurs)",
                    data: otherAllPoints,
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
    managePdgChart();
});