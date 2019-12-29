function manageZombieChart() {
    //line
    let zombiePoints = $('#zombiePoints').data("zombiePoints");
    let zombieDate = $('#zombieDate').data("zombieDate");
    let ctxL = document.getElementById("zombieChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: zombieDate,
            datasets: [{
                label: "Courbe d'évolution de la menace zombie",
                data: zombiePoints,
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
    manageZombieChart();
});