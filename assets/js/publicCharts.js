function managePdgChart() {
    //line
    let pdgOtherPoints = $('#pdgOtherPoints').data("pdgOtherPoints");
    let otherAllPoints = $('#pointOtherPoints').data("pointOtherPoints");
    let pdgDate = $('#pdgDate').data("pdgDate");
    let ctxL = document.getElementById("publicChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: pdgDate,
            datasets: [{
                label: "Moyenne des points",
                data: otherAllPoints,
                backgroundColor: [
                    'rgba(12, 42, 192, 0.5)',
                ],
                borderColor: [
                    'rgba(32, 132, 232, 1)',
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