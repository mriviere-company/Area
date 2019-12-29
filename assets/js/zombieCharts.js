function manageZombieChart() {
    //line
    let zombiePoints = $('#zombiePoints').data("zombiePoints");
    let zombieOtherPoints = $('#zombieOtherPoints').data("zombieOtherPoints");
    let zombieDate = $('#zombieDate').data("zombieDate");
    let ctxL = document.getElementById("zombieChart").getContext('2d');
    new Chart(ctxL, {
        type: 'line',
        data: {
            labels: zombieDate,
            datasets: [{
                label: "Ma menace Zombie",
                data: zombiePoints,
                backgroundColor: [
                    'rgba(235, 40, 40, .5)',
                ],
                borderColor: [
                    'rgba(32, 132, 232, 1)',
                ],
                borderWidth: 2
            },
                {
                    label: "Moyenne de la menace Zombie (autres joueurs)",
                    data: zombieOtherPoints,
                    backgroundColor: [
                        'rgba(28, 185, 28, .2)',
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
    manageZombieChart();
});