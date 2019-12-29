function manageShipChart() {
    //doughnut
    let ships = $('#shipPoints').data("shipPoints");
    let ctxD = document.getElementById("doughnutChart").getContext('2d');
    let myLineChart = new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: ["Coque", "Bouclier", "Missile", "Laser", "Plasma", "Précision"],
            datasets: [{
                data: ships,
                backgroundColor: ["#b8b2b2", "#688e94", "#c48a02", "#c9001b", "#78136c", "#125c12"],
                hoverBackgroundColor: ["#d1cbcb", "#91c0c7", "#e3a005", "#e6001f", "#961787", "#1a7d1a"]
            }]
        },
        options: {
            responsive: true
        }
    });
}

$(document).ready(function() {
    manageShipChart();
});