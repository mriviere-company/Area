function manageUsernameChart() {
    //doughnut
    let username = $('#usernamesChart').data("usernamesChart");
    let usernameNbr = $('#usernamesNbrChart').data("usernamesNbrChart");
    let ctxD = document.getElementById("usernameChart").getContext('2d');
    let myLineChart = new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: username,
            datasets: [{
                data: usernameNbr,
                backgroundColor: ["#41abb5", "#688e94", "#c48a02", "#c9001b", "#78136c", "#125c12", "#a35622", "#d9bb34", "#70b82e", "#d93866", "#b8b2b2", "#131414"],
                hoverBackgroundColor: ["#d1cbcb", "#91c0c7", "#e3a005", "#e6001f", "#961787", "#1a7d1a", "#a34e15", "#917b17", "#599c1c", "#9e193f", "#238f99", "#2d3333"]
            }]
        },
        options: {
            responsive: true
        }
    });
}

function manageRefererChart() {
    //doughnut
    let referer = $('#referersChart').data("referersChart");
    let refererNbr = $('#referersNbrChart').data("referersNbrChart");
    let ctxD = document.getElementById("refererChart").getContext('2d');
    let myLineChart = new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: referer,
            datasets: [{
                data: refererNbr,
                backgroundColor: ["#41abb5", "#688e94", "#c48a02", "#c9001b", "#78136c", "#125c12", "#a35622", "#d9bb34", "#70b82e", "#d93866", "#b8b2b2", "#131414"],
                hoverBackgroundColor: ["#d1cbcb", "#91c0c7", "#e3a005", "#e6001f", "#961787", "#1a7d1a", "#a34e15", "#917b17", "#599c1c", "#9e193f", "#238f99", "#2d3333"]
            }]
        },
        options: {
            responsive: true
        }
    });
}

function manageBrowserChart() {
    //doughnut
    let browser = $('#browsersChart').data("browsersChart");
    let browserNbr = $('#browsersNbrChart').data("browsersNbrChart");
    let ctxD = document.getElementById("browserChart").getContext('2d');
    let myLineChart = new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: browser,
            datasets: [{
                data: browserNbr,
                backgroundColor: ["#41abb5", "#688e94", "#c48a02", "#c9001b", "#78136c", "#125c12", "#a35622", "#d9bb34", "#70b82e", "#d93866", "#b8b2b2", "#131414"],
                hoverBackgroundColor: ["#d1cbcb", "#91c0c7", "#e3a005", "#e6001f", "#961787", "#1a7d1a", "#a34e15", "#917b17", "#599c1c", "#9e193f", "#238f99", "#2d3333"]
            }]
        },
        options: {
            responsive: true
        }
    });
}

function managePageChart() {
    //doughnut
    let page = $('#pagesChart').data("pagesChart");
    let pageNbr = $('#pagesNbrChart').data("pagesNbrChart");
    let ctxD = document.getElementById("pageChart").getContext('2d');
    let myLineChart = new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: page,
            datasets: [{
                data: pageNbr,
                backgroundColor: ["#41abb5", "#688e94", "#c48a02", "#c9001b", "#78136c", "#125c12", "#a35622", "#d9bb34", "#70b82e", "#d93866", "#b8b2b2", "#131414"],
                hoverBackgroundColor: ["#d1cbcb", "#91c0c7", "#e3a005", "#e6001f", "#961787", "#1a7d1a", "#a34e15", "#917b17", "#599c1c", "#9e193f", "#238f99", "#2d3333"]
            }]
        },
        options: {
            responsive: true
        }
    });
}

function manageComputerChart() {
    //doughnut
    let computer = $('#computersChart').data("computersChart");
    let computerNbr = $('#computersNbrChart').data("computersNbrChart");
    let ctxD = document.getElementById("computerChart").getContext('2d');
    let myLineChart = new Chart(ctxD, {
        type: 'doughnut',
        data: {
            labels: computer,
            datasets: [{
                data: computerNbr,
                backgroundColor: ["#41abb5", "#688e94", "#c48a02", "#c9001b", "#78136c", "#125c12", "#a35622", "#d9bb34", "#70b82e", "#d93866", "#b8b2b2", "#131414"],
                hoverBackgroundColor: ["#d1cbcb", "#91c0c7", "#e3a005", "#e6001f", "#961787", "#1a7d1a", "#a34e15", "#917b17", "#599c1c", "#9e193f", "#238f99", "#2d3333"]
            }]
        },
        options: {
            responsive: true
        }
    });
}

$(document).ready(function() {
    manageUsernameChart();
    manageRefererChart();
    manageBrowserChart();
    managePageChart();
    manageComputerChart();
});