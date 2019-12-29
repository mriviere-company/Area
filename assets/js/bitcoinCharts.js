function manageBitcoinChart() {
    //line
    let bitcoinPoints = $('#bitcoinPoints').data("bitcoinPoints");
    let bitcoinDate = $('#bitcoinDate').data("bitcoinDate");
    let ctxL = document.getElementById("bitcoinChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: bitcoinDate,
            datasets: [{
                label: "Courbe d'évolution de mes Bitcoins",
                data: bitcoinPoints,
                backgroundColor: [
                    'rgba(28, 185, 28, .2)',
                ],
                borderColor: [
                    'rgba(32, 132, 232, 1)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true
        }
    });
}

$(document).ready(function() {
    manageBitcoinChart();
});