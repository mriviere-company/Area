const $ = require('jquery');
        require('bootstrap-sass');
        require('bootstrap-confirmation2');
const now = new Date();

function manageImageForm() {
    $('.modify').off('click').on('click',function(e){
        $('#user_image_imageFile_file').click();
        $('#user_image_imageFile_file').on('change',function(){
            $(this).closest('form').submit();
        });
    });
}

function manageAllyImageForm() {
    $('.modify-allyImage').off('click').on('click',function(e){
        $('#ally_image_imageFile_file').click();
        $('#ally_image_imageFile_file').on('change',function(){
            $(this).closest('form').submit();
        });
    });
}

function manageRegistrationCheck() {
    $('#registration-check').off('click').on('click',function(e){
        $('#email').removeAttr('hidden');
        $(this).hide();
        $('#email').attr('required', 'required');
        $('form').attr('action', '/enregistrement');
    });
}

function managePlanetChoiceForm() {
    $('#planet_choice').off('change').on('change',function(e){
        e.preventDefault();

        let actionForm = $(this).attr('action');
            planetChoice = $(this)[0][0].value;

        actionForm = actionForm.replace(/\/\d+$/, '/' + planetChoice);
        $(this).attr('action', actionForm)
        $(this).closest('form').submit();
    });
}

function manageModalContact() {
    $('#contactModal').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let recipient = button.data('whatever');
        let title = $('#contactModalLabel').text();
        let modal = $(this);
        modal.find('.modal-title').text(title);
        modal.find('.modal-body input.form-control').val(recipient);
    });
}

function manageTime() {
    $('.timerArea').each( function(){
        let build = new Date($(this).text());
        let area = $(this);
        let date_now = Math.abs(build - now) / 1000;
        let jours = Math.floor(date_now / (60 * 60 * 24));
        let heures = Math.floor((date_now - (jours * 60 * 60 * 24)) / (60 * 60));
        let minutes = Math.floor((date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60))) / 60);
        let secondes = Math.floor(date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60 + minutes * 60)));
        jours = (jours < 10 && jours >= 0)  ? '0' + jours : jours;
        heures = (heures < 10 && heures >= 0)  ? '0' + heures : heures;
        minutes = (minutes < 10 && minutes >= 0)  ? '0' + minutes : minutes;
        secondes = (secondes < 10 && secondes >= 0)  ? '0' + secondes : secondes;
        setInterval(function() {
            if (build < now) {
                area.html("<a onclick='setTimeout(\"window.location.reload();\",2000)' style='cursor: pointer;'  href='../../construction/1/' target='_blank'>Terminer</a>");
                area.removeAttr('hidden');
                setTimeout(function () {
                }, 2000);
            } else {
                if (date_now > 0) {
                    if (jours > 0) {
                        area.text('(' + jours + 'j ' + heures + ':' + minutes + ':' + secondes + ')');
                        area.removeAttr('hidden');
                    } else if (heures > 0) {
                        area.text('(' + heures + ':' + minutes + ':' + secondes + ')');
                        area.removeAttr('hidden');
                    } else if (minutes > 0) {
                        area.text('(' + minutes + ':' + secondes + ')');
                        area.removeAttr('hidden');
                    } else if (secondes > 0) {
                        area.text('(' + secondes + ')');
                        area.removeAttr('hidden');
                    }
                    secondes = secondes - 1;
                    secondes = (secondes < 10 && secondes >= 0)  ? '0' + secondes : secondes;
                    if (secondes < 0) {
                        if (minutes == 0 && heures == 0 && jours == 0) {
                            area.html("<a onclick='setTimeout(\"window.location.reload();\",2000)' style='cursor: pointer;'  href='../../construction/1/' target='_blank'>Terminer</a>");
                        } else {
                            secondes = 59;
                            minutes = minutes - 1;
                            minutes = (minutes < 10 && minutes >= 0)  ? '0' + minutes : minutes;
                            if (minutes < 0 && heures > 0) {
                                minutes = 59;
                                heures = heures - 1;
                                heures = (heures < 10 && heures >= 0)  ? '0' + heures : heures;
                                if (heures < 0 && jours > 0) {
                                    heures = 23;
                                    jours = jours - 1;
                                    jours = (jours < 10 && jours >= 0)  ? '0' + jours : jours;
                                }
                            }
                        }
                    }
                }
            }
        }, 1000);
    });
}

function manageTotalCaserne() {
    $('.nbrProduct').off('change').on('change',function(e){
        let niobium = 0;
        let product = 0;
        let worker = 0;
        let bitcoin = 0;
        if($('#caserne_recruit_soldier').attr('max') - $('#caserne_recruit_soldier').val() < 0) {
            $('#caserne_recruit_soldier').val($('#caserne_recruit_soldier').attr('max'));
        }
        if($('#caserne_recruit_tank').attr('max') - $('#caserne_recruit_tank').val() < 0) {
            $('#caserne_recruit_tank').val($('#caserne_recruit_tank').attr('max'));
        }
        if($('#caserne_recruit_scientist').attr('max') - $('#caserne_recruit_scientist').val() < 0) {
            $('#caserne_recruit_scientist').val($('#caserne_recruit_scientist').attr('max'));
        }
        $('.nbrProduct').each( function(){
            if($(this).val() > 0) {
                if (product == 0) {
                    product = $(this).val();
                } else {
                    product = parseFloat(product) + parseFloat($(this).val());
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.niobiumProduct').text().replace('.', '');
                if (niobium == 0) {
                    niobium = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    niobium = niobium + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.workerProduct').text().replace('.', '');
                if (worker == 0) {
                    worker = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    worker = worker + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.bitcoinProduct').text().replace('.', '');
                if (bitcoin == 0) {
                    bitcoin = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    bitcoin = bitcoin + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        let re = new RegExp(',', 'g')
        if(niobium <= parseFloat($('#niobium').text().replace(re, ''))) {
            $('#niobiumProduct').text(niobium);
        } else {
            $('#niobiumProduct').html("<span class='text-rouge'>" + niobium + "</span>");
        }
        if(worker <= parseFloat($('#worker').text().replace(re, ''))) {
            $('#workerProduct').text(worker);
        } else {
            $('#workerProduct').html("<span class='text-rouge'>" + worker + "</span>");
        }
        if(bitcoin <= parseFloat($('#bitcoin').text().replace(re, ''))) {
            $('#bitcoinProduct').text(bitcoin);
        } else {
            $('#bitcoinProduct').html("<span class='text-rouge'>" + bitcoin + "</span>");
        }
    });
}

function manageFleetListChoiceForm() {
    $('.fleetList_add').off('change').on('change',function(e){
        e.preventDefault();

        let actionForm = $(this).attr('action');
        fleetChoice = $(this)[0][0].value;

        actionForm = actionForm.replace(/\/\d$/, '/' + fleetChoice);
        console.log(actionForm);
        $(this).attr('action', actionForm)
        $(this).closest('form').submit();
    });
}

function manageFlightTime(){
    let position = $('#positionFleet').text();
    let galaxy = $('#galaxyFleet').text();
    let speed = $('#speedFleet').text();
    let carburant = 1;

    $('#fleet_send_planete').off('change').on('change',function(e){
        let newPosition = $('#fleet_send_sector').val();
        let newGalaxy = $('#fleet_send_galaxy').val();
        newPosition = newPosition.toString();
        let planete = $('#planeteFleet').text();
        let newPlanete = $('#fleet_send_planete').val();
        let base;
        let price;
        let x1;
        let x2;
        let y1;
        let y2;
        newPlanete = newPlanete.toString();
        if (galaxy != newGalaxy) {
            base = 18;
            price = 25;
        } else {
            if (position == newPosition) {
                x1 = (planete - 1) % 5;
                x2 = (newPlanete - 1) % 5;
                y1 = (planete - 1) / 5;
                y2 = (newPlanete - 1) / 5;
            } else {
                x1 = ((position - 1) % 10) * 3;
                x2 = ((newPosition - 1) % 10) * 3;
                y1 = ((position - 1) / 10) * 3;
                y2 = ((newPosition - 1) / 10) * 3;
            }
            base = Math.sqrt(Math.pow((x2 - x1), 2) + Math.pow((y2 - y1), 2));
            price = base / 3;
        }
        carburant = Math.round(price * ($('#signatureFleet').text() / 200));
        let travel = new Date();
        travel.setSeconds(travel.getSeconds() + (base * speed * 100)); // 1000 MODE NORMAL
        let date_now = Math.abs((travel - now) / 1000);
        let jours = Math.floor(date_now / (60 * 60 * 24));
        let heures = Math.floor((date_now - (jours * 60 * 60 * 24)) / (60 * 60));
        let minutes = Math.floor((date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60))) / 60);
        let secondes = Math.floor(date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60 + minutes * 60)));
            if (jours > 0) {
            $('#flightTime').text(jours + ' jours ' + heures + ' heures ' + minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (heures > 0)
        {
            $('#flightTime').text(heures + ' heures ' + minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (minutes > 0)
        {
            $('#flightTime').text(minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (secondes > 0)
        {
            $('#flightTime').text(secondes + ' secondes');
            $('#flightCost').text(carburant);
        }
    });

    $('#fleet_send_sector').off('change').on('change',function(e){
        let newPosition = $('#fleet_send_sector').val();
        let newGalaxy = $('#fleet_send_galaxy').val();
        newPosition = newPosition.toString();
        let planete = $('#planeteFleet').text();
        let newPlanete = $('#fleet_send_planete').val();
        let base;
        let price;
        let x1;
        let x2;
        let y1;
        let y2;
        newPlanete = newPlanete.toString();
        if (galaxy != newGalaxy) {
            base = 18;
            price = 25;
        } else {
            if (position == newPosition) {
                x1 = (planete - 1) % 5;
                x2 = (newPlanete - 1) % 5;
                y1 = (planete - 1) / 5;
                y2 = (newPlanete - 1) / 5;
            } else {
                x1 = ((position - 1) % 10) * 3;
                x2 = ((newPosition - 1) % 10) * 3;
                y1 = ((position - 1) / 10) * 3;
                y2 = ((newPosition - 1) / 10) * 3;
            }
            base = Math.sqrt(Math.pow((x2 - x1), 2) + Math.pow((y2 - y1), 2));
            price = base / 3;
        }
        carburant = Math.round(price * ($('#signatureFleet').text() / 200));
        let travel = new Date();
        travel.setSeconds(travel.getSeconds() + (base * speed * 100)); // 1000 MODE NORMAL
        let date_now = Math.abs((travel - now) / 1000);
        let jours = Math.floor(date_now / (60 * 60 * 24));
        let heures = Math.floor((date_now - (jours * 60 * 60 * 24)) / (60 * 60));
        let minutes = Math.floor((date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60))) / 60);
        let secondes = Math.floor(date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60 + minutes * 60)));
        if (jours > 0) {
            $('#flightTime').text(jours + ' jours ' + heures + ' heures ' + minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (heures > 0)
        {
            $('#flightTime').text(heures + ' heures ' + minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (minutes > 0)
        {
            $('#flightTime').text(minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (secondes > 0)
        {
            $('#flightTime').text(secondes + ' secondes');
            $('#flightCost').text(carburant);
        }
    });

    $('#fleet_send_galaxy').off('change').on('change',function(e){
        let newPosition = $('#fleet_send_sector').val();
        let newGalaxy = $('#fleet_send_galaxy').val();
        newPosition = newPosition.toString();
        let planete = $('#planeteFleet').text();
        let newPlanete = $('#fleet_send_planete').val();
        newPlanete = newPlanete.toString();
        let base;
        let price;
        let x1;
        let x2;
        let y1;
        let y2;

        if (galaxy != newGalaxy) {
            base = 18;
            price = 25;
        } else {
            if (position == newPosition) {
                x1 = (planete - 1) % 5;
                x2 = (newPlanete - 1) % 5;
                y1 = (planete - 1) / 5;
                y2 = (newPlanete - 1) / 5;
            } else {
                x1 = ((position - 1) % 10) * 3;
                x2 = ((newPosition - 1) % 10) * 3;
                y1 = ((position - 1) / 10) * 3;
                y2 = ((newPosition - 1) / 10) * 3;
            }
            base = Math.sqrt(Math.pow((x2 - x1), 2) + Math.pow((y2 - y1), 2));
            price = base / 3;
        }
        carburant = Math.round(price * ($('#signatureFleet').text() / 200)) > 0 ? Math.round(price * ($('#signatureFleet').text() / 200)) : 1;
        let travel = new Date();
        travel.setSeconds(travel.getSeconds() + (base * speed * 100)); // 1000 MODE NORMAL
        let date_now = Math.abs((travel - now) / 1000);
        let jours = Math.floor(date_now / (60 * 60 * 24));
        let heures = Math.floor((date_now - (jours * 60 * 60 * 24)) / (60 * 60));
        let minutes = Math.floor((date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60))) / 60);
        let secondes = Math.floor(date_now - ((jours * 60 * 60 * 24 + heures * 60 * 60 + minutes * 60)));
        if (jours > 0) {
            $('#flightTime').text(jours + ' jours ' + heures + ' heures ' + minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (heures > 0)
        {
            $('#flightTime').text(heures + ' heures ' + minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (minutes > 0)
        {
            $('#flightTime').text(minutes + ' mins ' + secondes + ' secondes');
            $('#flightCost').text(carburant);
        } else if (secondes > 0)
        {
            $('#flightTime').text(secondes + ' secondes');
            $('#flightCost').text(carburant);
        }
    });
}

function manageCoordonate(){
    $('#fleet_send_planet').off('change').on('change',function(e){
        if($('#fleet_send_planet').val() != '') {
            $('#fleet_send_planete').attr('disabled', 'disabled');
            $('#fleet_send_sector').attr('disabled', 'disabled');
        } else {
            $('#fleet_send_planete').removeAttr('disabled', 'disabled');
            $('#fleet_send_sector').removeAttr('disabled', 'disabled');
        }
    });
}

function manageAttackFleetForm() {
    $('#fleet_attack_attack').off('change').on('change',function(e){
        e.preventDefault();

        let formContent = $(this).closest('form')[0][1].checked;
        content = formContent ? 1 : 0;
        $form = $(this).closest('form');
        $.ajax({
            url: $form.attr('action'),
            type: "POST",
            data: {name: 'attack', data: content},
            success: function(response){
                if(response.war == true) {
                    location = location;
                }
            }
        });
        //$(this).closest('form').submit();
    });
}

function manageMaxShip() {
    $('#spatial_edit_fleet_moreSonde').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreSonde').attr('max') - $('#spatial_edit_fleet_moreSonde').val() < 0) {
            $('#spatial_edit_fleet_moreSonde').val($('#spatial_edit_fleet_moreSonde').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreColonizer').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreColonizer').attr('max') - $('#spatial_edit_fleet_moreColonizer').val() < 0) {
            $('#spatial_edit_fleet_moreColonizer').val($('#spatial_edit_fleet_moreColonizer').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreCargoI').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreCargoI').attr('max') - $('#spatial_edit_fleet_moreCargoI').val() < 0) {
            $('#spatial_edit_fleet_moreCargoI').val($('#spatial_edit_fleet_moreCargoI').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreCargoV').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreCargoV').attr('max') - $('#spatial_edit_fleet_moreCargoV').val() < 0) {
            $('#spatial_edit_fleet_moreCargoV').val($('#spatial_edit_fleet_moreCargoV').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreCargoX').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreCargoX').attr('max') - $('#spatial_edit_fleet_moreCargoX').val() < 0) {
            $('#spatial_edit_fleet_moreCargoX').val($('#spatial_edit_fleet_moreCargoX').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreRecycleur').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreRecycleur').attr('max') - $('#spatial_edit_fleet_moreRecycleur').val() < 0) {
            $('#spatial_edit_fleet_moreRecycleur').val($('#spatial_edit_fleet_moreRecycleur').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreHunter').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreHunter').attr('max') - $('#spatial_edit_fleet_moreHunter').val() < 0) {
            $('#spatial_edit_fleet_moreHunter').val($('#spatial_edit_fleet_moreHunter').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreHunterHeavy').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreHunterHeavy').attr('max') - $('#spatial_edit_fleet_moreHunterHeavy').val() < 0) {
            $('#spatial_edit_fleet_moreHunterHeavy').val($('#spatial_edit_fleet_moreHunterHeavy').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreCorvet').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreCorvet').attr('max') - $('#spatial_edit_fleet_moreCorvet').val() < 0) {
            $('#spatial_edit_fleet_moreCorvet').val($('#spatial_edit_fleet_moreCorvet').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreCorvetLaser').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreCorvetLaser').attr('max') - $('#spatial_edit_fleet_moreCorvetLaser').val() < 0) {
            $('#spatial_edit_fleet_moreCorvetLaser').val($('#spatial_edit_fleet_moreCorvetLaser').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreFregate').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreFregate').attr('max') - $('#spatial_edit_fleet_moreFregate').val() < 0) {
            $('#spatial_edit_fleet_moreFregate').val($('#spatial_edit_fleet_moreFregate').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreFregatePlasma').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreFregatePlasma').attr('max') - $('#spatial_edit_fleet_moreFregatePlasma').val() < 0) {
            $('#spatial_edit_fleet_moreFregatePlasma').val($('#spatial_edit_fleet_moreFregatePlasma').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreCroiser').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreCroiser').attr('max') - $('#spatial_edit_fleet_moreCroiser').val() < 0) {
            $('#spatial_edit_fleet_moreCroiser').val($('#spatial_edit_fleet_moreCroiser').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreIronClad').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreIronClad').attr('max') - $('#spatial_edit_fleet_moreIronClad').val() < 0) {
            $('#spatial_edit_fleet_moreIronClad').val($('#spatial_edit_fleet_moreIronClad').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreDestroyer').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreDestroyer').attr('max') - $('#spatial_edit_fleet_moreDestroyer').val() < 0) {
            $('#spatial_edit_fleet_moreDestroyer').val($('#spatial_edit_fleet_moreDestroyer').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreBarge').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreBarge').attr('max') - $('#spatial_edit_fleet_moreBarge').val() < 0) {
            $('#spatial_edit_fleet_moreBarge').val($('#spatial_edit_fleet_moreBarge').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessSonde').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessSonde').attr('max') - $('#spatial_edit_fleet_lessSonde').val() < 0) {
            $('#spatial_edit_fleet_lessSonde').val($('#spatial_edit_fleet_lessSonde').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessColonizer').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessColonizer').attr('max') - $('#spatial_edit_fleet_lessColonizer').val() < 0) {
            $('#spatial_edit_fleet_lessColonizer').val($('#spatial_edit_fleet_lessColonizer').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessCargoI').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessCargoI').attr('max') - $('#spatial_edit_fleet_lessCargoI').val() < 0) {
            $('#spatial_edit_fleet_lessCargoI').val($('#spatial_edit_fleet_lessCargoI').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessCargoV').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessCargoV').attr('max') - $('#spatial_edit_fleet_lessCargoV').val() < 0) {
            $('#spatial_edit_fleet_lessCargoV').val($('#spatial_edit_fleet_lessCargoV').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessCargoX').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessCargoX').attr('max') - $('#spatial_edit_fleet_lessCargoX').val() < 0) {
            $('#spatial_edit_fleet_lessCargoX').val($('#spatial_edit_fleet_lessCargoX').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessRecycleur').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessRecycleur').attr('max') - $('#spatial_edit_fleet_lessRecycleur').val() < 0) {
            $('#spatial_edit_fleet_lessRecycleur').val($('#spatial_edit_fleet_lessRecycleur').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessHunter').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessHunter').attr('max') - $('#spatial_edit_fleet_lessHunter').val() < 0) {
            $('#spatial_edit_fleet_lessHunter').val($('#spatial_edit_fleet_lessHunter').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessHunterHeavy').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessHunterHeavy').attr('max') - $('#spatial_edit_fleet_lessHunterHeavy').val() < 0) {
            $('#spatial_edit_fleet_lessHunterHeavy').val($('#spatial_edit_fleet_lessHunterHeavy').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessCorvet').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessCorvet').attr('max') - $('#spatial_edit_fleet_lessCorvet').val() < 0) {
            $('#spatial_edit_fleet_lessCorvet').val($('#spatial_edit_fleet_lessCorvet').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessCorvetLaser').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessCorvetLaser').attr('max') - $('#spatial_edit_fleet_lessCorvetLaser').val() < 0) {
            $('#spatial_edit_fleet_lessCorvetLaser').val($('#spatial_edit_fleet_lessCorvetLaser').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessFregate').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessFregate').attr('max') - $('#spatial_edit_fleet_lessFregate').val() < 0) {
            $('#spatial_edit_fleet_lessFregate').val($('#spatial_edit_fleet_lessFregate').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessFregatePlasma').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessFregatePlasma').attr('max') - $('#spatial_edit_fleet_lessFregatePlasma').val() < 0) {
            $('#spatial_edit_fleet_lessFregatePlasma').val($('#spatial_edit_fleet_lessFregatePlasma').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessCroiser').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessCroiser').attr('max') - $('#spatial_edit_fleet_lessCroiser').val() < 0) {
            $('#spatial_edit_fleet_lessCroiser').val($('#spatial_edit_fleet_lessCroiser').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessIronClad').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessIronClad').attr('max') - $('#spatial_edit_fleet_lessIronClad').val() < 0) {
            $('#spatial_edit_fleet_lessIronClad').val($('#spatial_edit_fleet_lessIronClad').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessDestroyer').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessDestroyer').attr('max') - $('#spatial_edit_fleet_lessDestroyer').val() < 0) {
            $('#spatial_edit_fleet_lessDestroyer').val($('#spatial_edit_fleet_lessDestroyer').attr('max'));
        }
    });
    $('#spatial_edit_fleet_lessBarge').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessBarge').attr('max') - $('#spatial_edit_fleet_lessBarge').val() < 0) {
            $('#spatial_edit_fleet_lessBarge').val($('#spatial_edit_fleet_lessBarge').attr('max'));
        }
    });
    $('#spatial_edit_fleet_moreNiobium').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreNiobium').attr('max') - $('#spatial_edit_fleet_moreNiobium').val() < 0) {
            $('#spatial_edit_fleet_moreNiobium').val(Math.trunc($('#spatial_edit_fleet_moreNiobium').attr('max')));
        }
    });
    $('#spatial_edit_fleet_lessNiobium').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessNiobium').attr('max') - $('#spatial_edit_fleet_lessNiobium').val() < 0) {
            $('#spatial_edit_fleet_lessNiobium').val(Math.trunc($('#spatial_edit_fleet_lessNiobium').attr('max')));
        }
    });
    $('#spatial_edit_fleet_moreWater').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreWater').attr('max') - $('#spatial_edit_fleet_moreWater').val() < 0) {
            $('#spatial_edit_fleet_moreWater').val(Math.trunc($('#spatial_edit_fleet_moreWater').attr('max')));
        }
    });
    $('#spatial_edit_fleet_lessWater').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessWater').attr('max') - $('#spatial_edit_fleet_lessWater').val() < 0) {
            $('#spatial_edit_fleet_lessWater').val(Math.trunc($('#spatial_edit_fleet_lessWater').attr('max')));
        }
    });
    $('#spatial_edit_fleet_moreSoldier').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreSoldier').attr('max') - $('#spatial_edit_fleet_moreSoldier').val() < 0) {
            $('#spatial_edit_fleet_moreSoldier').val(Math.trunc($('#spatial_edit_fleet_moreSoldier').attr('max')));
        }
    });
    $('#spatial_edit_fleet_lessSoldier').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessSoldier').attr('max') - $('#spatial_edit_fleet_lessSoldier').val() < 0) {
            $('#spatial_edit_fleet_lessSoldier').val(Math.trunc($('#spatial_edit_fleet_lessSoldier').attr('max')));
        }
    });
    $('#spatial_edit_fleet_moreWorker').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreWorker').attr('max') - $('#spatial_edit_fleet_moreWorker').val() < 0) {
            $('#spatial_edit_fleet_moreWorker').val(Math.trunc($('#spatial_edit_fleet_moreWorker').attr('max')));
        }
    });
    $('#spatial_edit_fleet_lessWorker').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessWorker').attr('max') - $('#spatial_edit_fleet_lessWorker').val() < 0) {
            $('#spatial_edit_fleet_lessWorker').val(Math.trunc($('#spatial_edit_fleet_lessWorker').attr('max')));
        }
    });
    $('#spatial_edit_fleet_moreScientist').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_moreScientist').attr('max') - $('#spatial_edit_fleet_moreScientist').val() < 0) {
            $('#spatial_edit_fleet_moreScientist').val(Math.trunc($('#spatial_edit_fleet_moreScientist').attr('max')));
        }
    });
    $('#spatial_edit_fleet_lessScientist').off('change').on('change',function(e){
        if($('#spatial_edit_fleet_lessScientist').attr('max') - $('#spatial_edit_fleet_lessScientist').val() < 0) {
            $('#spatial_edit_fleet_lessScientist').val(Math.trunc($('#spatial_edit_fleet_lessScientist').attr('max')));
        }
    });
    $('#spatial_fleet_sonde').off('change').on('change',function(e){
        if($('#spatial_fleet_sonde').attr('max') - $('#spatial_fleet_sonde').val() < 0) {
            $('#spatial_fleet_sonde').val($('#spatial_fleet_sonde').attr('max'));
        }
    });
    $('#spatial_fleet_colonizer').off('change').on('change',function(e){
        if($('#spatial_fleet_colonizer').attr('max') - $('#spatial_fleet_colonizer').val() < 0) {
            $('#spatial_fleet_colonizer').val($('#spatial_fleet_colonizer').attr('max'));
        }
    });
    $('#spatial_fleet_cargoI').off('change').on('change',function(e){
        if($('#spatial_fleet_cargoI').attr('max') - $('#spatial_fleet_cargoI').val() < 0) {
            $('#spatial_fleet_cargoI').val($('#spatial_fleet_cargoI').attr('max'));
        }
    });
    $('#spatial_fleet_cargoV').off('change').on('change',function(e){
        if($('#spatial_fleet_cargoV').attr('max') - $('#spatial_fleet_cargoV').val() < 0) {
            $('#spatial_fleet_cargoV').val($('#spatial_fleet_cargoV').attr('max'));
        }
    });
    $('#spatial_fleet_cargoX').off('change').on('change',function(e){
        if($('#spatial_fleet_cargoX').attr('max') - $('#spatial_fleet_cargoX').val() < 0) {
            $('#spatial_fleet_cargoX').val($('#spatial_fleet_cargoX').attr('max'));
        }
    });
    $('#spatial_fleet_recycleur').off('change').on('change',function(e){
        if($('#spatial_fleet_recycleur').attr('max') - $('#spatial_fleet_recycleur').val() < 0) {
            $('#spatial_fleet_recycleur').val($('#spatial_fleet_recycleur').attr('max'));
        }
    });
    $('#spatial_fleet_hunter').off('change').on('change',function(e){
        if($('#spatial_fleet_hunter').attr('max') - $('#spatial_fleet_hunter').val() < 0) {
            $('#spatial_fleet_hunter').val($('#spatial_fleet_hunter').attr('max'));
        }
    });
    $('#spatial_fleet_hunterHeavy').off('change').on('change',function(e){
        if($('#spatial_fleet_hunterHeavy').attr('max') - $('#spatial_fleet_hunterHeavy').val() < 0) {
            $('#spatial_fleet_hunterHeavy').val($('#spatial_fleet_hunterHeavy').attr('max'));
        }
    });
    $('#spatial_fleet_corvet').off('change').on('change',function(e){
        if($('#spatial_fleet_corvet').attr('max') - $('#spatial_fleet_corvet').val() < 0) {
            $('#spatial_fleet_corvet').val($('#spatial_fleet_corvet').attr('max'));
        }
    });
    $('#spatial_fleet_corvetLaser').off('change').on('change',function(e){
        if($('#spatial_fleet_corvetLaser').attr('max') - $('#spatial_fleet_corvetLaser').val() < 0) {
            $('#spatial_fleet_corvetLaser').val($('#spatial_fleet_corvetLaser').attr('max'));
        }
    });
    $('#spatial_fleet_fregate').off('change').on('change',function(e){
        if($('#spatial_fleet_fregate').attr('max') - $('#spatial_fleet_fregate').val() < 0) {
            $('#spatial_fleet_fregate').val($('#spatial_fleet_fregate').attr('max'));
        }
    });
    $('#spatial_fleet_fregatePlasma').off('change').on('change',function(e){
        if($('#spatial_fleet_fregatePlasma').attr('max') - $('#spatial_fleet_fregatePlasma').val() < 0) {
            $('#spatial_fleet_fregatePlasma').val($('#spatial_fleet_fregatePlasma').attr('max'));
        }
    });
    $('#spatial_fleet_croiser').off('change').on('change',function(e){
        if($('#spatial_fleet_croiser').attr('max') - $('#spatial_fleet_croiser').val() < 0) {
            $('#spatial_fleet_croiser').val($('#spatial_fleet_croiser').attr('max'));
        }
    });
    $('#spatial_fleet_ironClad').off('change').on('change',function(e){
        if($('#spatial_fleet_ironClad').attr('max') - $('#spatial_fleet_ironClad').val() < 0) {
            $('#spatial_fleet_ironClad').val($('#spatial_fleet_ironClad').attr('max'));
        }
    });
    $('#spatial_fleet_destroyer').off('change').on('change',function(e){
        if($('#spatial_fleet_destroyer').attr('max') - $('#spatial_fleet_destroyer').val() < 0) {
            $('#spatial_fleet_destroyer').val($('#spatial_fleet_destroyer').attr('max'));
        }
    });
    $('#spatial_fleet_barge').off('change').on('change',function(e){
        if($('#spatial_fleet_barge').attr('max') - $('#spatial_fleet_barge').val() < 0) {
            $('#spatial_fleet_barge').val($('#spatial_fleet_barge').attr('max'));
        }
    });
}

function manageMaxClick() {
    $('.maxInput').off('click').on('click',function(e){
        let parent = $(this).parent().parent();
        parent.find('input:first').val(parent.find('input:first').attr('max'));
    });
    $('.maxInputLess').off('click').on('click',function(e){
        let parent = $(this).parent().parent();
        parent.find('input:eq(1)').val(parent.find('input:eq(1)').attr('max'));
    });
    $('.maxInputR').off('click').on('click',function(e){
        let parent = $(this).parent().parent();
        parent.find('input:first').val(parent.find('input:first').attr('max'));
    });
    $('.maxInputLessR').off('click').on('click',function(e){
        let parent = $(this).parent().parent();
        parent.find('input:eq(1)').val(parent.find('input:eq(1)').attr('max'));
    });
    $('.addAllShip').off('click').on('click',function(e){
        $('.maxInput').each( function(){
            let parent = $(this).parent().parent();
            parent.find('input:first').val(parent.find('input:first').attr('max'));
        });
    });
    $('.removeAllShip').off('click').on('click',function(e){
        $('.maxInputLess').each( function(){
            let parent = $(this).parent().parent();
            parent.find('input:eq(1)').val(parent.find('input:eq(1)').attr('max'));
        });
    });
    $('.addAllShipR').off('click').on('click',function(e){
        $('.maxInputR').each( function(){
            let parent = $(this).parent().parent();
            parent.find('input:first').val(parent.find('input:first').attr('max'));
        });
    });
    $('.removeAllShipR').off('click').on('click',function(e){
        $('.maxInputLessR').each( function(){
            let parent = $(this).parent().parent();
            parent.find('input:eq(1)').val(parent.find('input:eq(1)').attr('max'));
        });
    });
    $('.addAllRes').off('click').on('click',function(e){
        $('.maxInputLessR').each( function(){
            let parent = $(this).parent().parent();
            parent.find('input:first').val(parent.find('input:first').attr('max'));
        });
    });
}
/*
function manageRenameFleetForm() {
    $('#fleet_rename_sendForm').off('click').on('click',function(e){
        e.preventDefault();

        let content = $(this).closest('input')[0][0].value;
        $form = $(this).closest('form');
        $.ajax({
            url: $form.attr('action'),
            type: "POST",
            data: {name: 'name', data: content}
        });
    });
}*/

function managePlanetSellerChoiceForm() {
    $('.planetList_add').off('change').on('change',function(e){
        e.preventDefault();

        let actionForm = $(this).attr('action');
        fleetChoice = $(this)[0][0].value;

        actionForm = actionForm.replace(/\/\d$/, '/' + fleetChoice);
        console.log(actionForm);
        $(this).attr('action', actionForm)
        $(this).closest('form').submit();
    });
}

function manageSalon() {
    /*$('#salon_sendForm').click(function(e) {
        e.preventDefault();

        let content = $('#salon_content').val();
        $form = $('#salon_content').closest('form');
        if (content != "") {
            $.ajax({
               url: $form.attr('action'),
               type: "POST",
                data: 'newMessage=' + content,
            });
        }
    });*/
    if(document.location.href.match('/salon(/|$)')) {
        $('.chat-defil').scrollTop(8000);
        $('#salon_content').select();
    }
}


function manageFocusSalon() {
    $('.nameSalon').off('click').on('click',function(e){
        if ($('#salon_content').val().indexOf($(this).text() + " > ") == -1) {
            $('#salon_content').val($(this).text() + " > "  + $('#salon_content').val());
            $('#salon_content').focus();
        }
    });
}

function manageDisplaySalon(){
    if(document.location.href.match('/salon(/|$)')) {
        setTimeout( function(){
            $.ajax({
                url : document.location.href,
                type : "GET",
                success : function(data){
                    if(data.has_error === false && $('#salon_content').val() === "") {
                        location = location;
                    }
                }
            });
            manageDisplaySalon();
        }, 2500);
    }
}

function manageClickShip() {
    $('.hunterClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="hunter"]').removeClass('hidden');
    });
    $('.hunterHeavyClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="hunterHeavy"]').removeClass('hidden');
    });
    $('.hunterWarClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="hunterWar"]').removeClass('hidden');
    });
    $('.corvetClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="corvet"]').removeClass('hidden');
    });
    $('.corvetLaserClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="corvetLaser"]').removeClass('hidden');
    });
    $('.corvetWarClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="corvetWar"]').removeClass('hidden');
    });
    $('.fregateClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="fregate"]').removeClass('hidden');
    });
    $('.fregatePlasmaClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="fregatePlasma"]').removeClass('hidden');
    });
    $('.croiserClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="croiser"]').removeClass('hidden');
    });
    $('.ironCladClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="ironClad"]').removeClass('hidden');
    });
    $('.destroyerClick').off('click').on('click',function(e){
        $('.persoFleet tr').addClass('hidden');
        $('tr[name="destroyer"]').removeClass('hidden');
    });
}

function managePointShip() {
    $('tr[name="hunter"] input').off('change').on('change',function(e){

        let total = $('tr[name="hunter"] .totalExec').text();
        let points = 0;
        $('tr[name="hunter"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="hunter"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="hunter"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="hunter"] td:eq(1)').text()) {
                if ($('tr[name="hunter"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="hunter"] td:eq(7)').text()) {
                if ($('tr[name="hunter"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="hunter"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="hunterHeavy"] input').off('change').on('change',function(e){

        let total = $('tr[name="hunterHeavy"] .totalExec').text();
        let points = 0;
        $('tr[name="hunterHeavy"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="hunterHeavy"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="hunterHeavy"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="hunterHeavy"] td:eq(1)').text()) {
                if ($('tr[name="hunterHeavy"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="hunterHeavy"] td:eq(7)').text()) {
                if ($('tr[name="hunterHeavy"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="hunterHeavy"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="hunterWar"] input').off('change').on('change',function(e){

        let total = $('tr[name="hunterWar"] .totalExec').text();
        let points = 0;
        $('tr[name="hunterWar"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="hunterWar"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="hunterWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = Math.abs($(this).parent().prev().find('.addPoint').text()) - 1;
            } else {
                cumul = Math.abs($(this).parent().prev().find('.addPoint').text()) + 1;
            }
            if ($(this).parent().prev().text() == $('tr[name="hunterWar"] td:eq(1)').text()) {
                if ($('tr[name="hunterWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="hunterWar"] td:eq(7)').text()) {
                if ($('tr[name="hunterWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="hunterWar"] td:eq(9)').text()) {
                if ($('tr[name="hunterWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="hunterWar"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="corvet"] input').off('change').on('change',function(e){

        let total = $('tr[name="corvet"] .totalExec').text();
        let points = 0;
        $('tr[name="corvet"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="corvet"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="corvet"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="corvet"] td:eq(1)').text()) {
                if ($('tr[name="corvet"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="corvet"] td:eq(7)').text()) {
                if ($('tr[name="corvet"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="corvet"] td:eq(9)').text()) {
                if ($('tr[name="corvet"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="corvet"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="corvetLaser"] input').off('change').on('change',function(e){

        let total = $('tr[name="corvetLaser"] .totalExec').text();
        let points = 0;
        $('tr[name="corvetLaser"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="corvetLaser"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="corvetLaser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="corvetLaser"] td:eq(1)').text()) {
                if ($('tr[name="corvetLaser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="corvetLaser"] td:eq(7)').text()) {
                if ($('tr[name="corvetLaser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="corvetLaser"] td:eq(9)').text()) {
                if ($('tr[name="corvetLaser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="corvetLaser"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="corvetWar"] input').off('change').on('change',function(e){

        let total = $('tr[name="corvetWar"] .totalExec').text();
        let points = 0;
        $('tr[name="corvetWar"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="corvetWar"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="corvetWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="corvetWar"] td:eq(1)').text()) {
                if ($('tr[name="corvetWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="corvetWar"] td:eq(7)').text()) {
                if ($('tr[name="corvetWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="corvetWar"] td:eq(9)').text()) {
                if ($('tr[name="corvetWar"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="corvetWar"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="fregate"] input').off('change').on('change',function(e){

        let total = $('tr[name="fregate"] .totalExec').text();
        let points = 0;
        $('tr[name="fregate"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="fregate"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="fregate"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="fregate"] td:eq(1)').text()) {
                if ($('tr[name="fregate"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="fregate"] td:eq(7)').text()) {
                if ($('tr[name="fregate"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="fregate"] td:eq(9)').text()) {
                if ($('tr[name="fregate"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="fregate"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="fregatePlasma"] input').off('change').on('change',function(e){

        let total = $('tr[name="fregatePlasma"] .totalExec').text();
        let points = 0;
        $('tr[name="fregatePlasma"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="fregatePlasma"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="fregatePlasma"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="fregatePlasma"] td:eq(1)').text()) {
                if ($('tr[name="fregatePlasma"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="fregatePlasma"] td:eq(7)').text()) {
                if ($('tr[name="fregatePlasma"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="fregatePlasma"] td:eq(9)').text()) {
                if ($('tr[name="fregatePlasma"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="fregatePlasma"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="croiser"] input').off('change').on('change',function(e){

        let total = $('tr[name="croiser"] .totalExec').text();
        let points = 0;
        $('tr[name="croiser"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="croiser"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="croiser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="croiser"] td:eq(1)').text()) {
                if ($('tr[name="croiser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="croiser"] td:eq(7)').text()) {
                if ($('tr[name="croiser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="croiser"] td:eq(9)').text()) {
                if ($('tr[name="croiser"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="croiser"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="ironClad"] input').off('change').on('change',function(e){

        let total = $('tr[name="ironClad"] .totalExec').text();
        let points = 0;
        $('tr[name="ironClad"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="ironClad"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="ironClad"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="ironClad"] td:eq(1)').text()) {
                if ($('tr[name="ironClad"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="ironClad"] td:eq(7)').text()) {
                if ($('tr[name="ironClad"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="ironClad"] td:eq(9)').text()) {
                if ($('tr[name="ironClad"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="ironClad"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
    $('tr[name="destroyer"] input').off('change').on('change',function(e){

        let total = $('tr[name="destroyer"] .totalExec').text();
        let points = 0;
        $('tr[name="destroyer"] input[type="number"]').each(function() { points = points + Math.abs($(this).val()); });

        if (total - points < 0) {
            $('tr[name="destroyer"] .totalPoints').text(0);
            $(this).val($(this).val() - 1);
        } else {
            let val = $(this).val() != 0 ? $(this).val() : 1;
            if ($('tr[name="destroyer"] .totalPoints').text() < total - points || $(this).val() == 0) {
                cumul = -1 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
            } else {
                cumul = 1 * val + Math.abs($(this).parent().prev().find('.basis').text());
            }
            if ($(this).parent().prev().text() == $('tr[name="destroyer"] td:eq(1)').text()) {
                if ($('tr[name="destroyer"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -5 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 5 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="destroyer"] td:eq(7)').text()) {
                if ($('tr[name="destroyer"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -3 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 3 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            } else if ($(this).parent().prev().text() == $('tr[name="destroyer"] td:eq(9)').text()) {
                if ($('tr[name="destroyer"] .totalPoints').text() < total - points || $(this).val() == 0) {
                    cumul = -2 * val + Math.abs($(this).parent().prev().find('.addPoint').text());
                } else {
                    cumul = 2 * val + Math.abs($(this).parent().prev().find('.basis').text());
                }
            }
            $('tr[name="destroyer"] .totalPoints').text(total - points);
            $(this).parent().prev().find('.addPoint').html("<span class='text-vert'>" + cumul + "</span>");
        }
    });
}

function manageTotalShip() {
    $('.nbrProduct').off('change').on('change',function(e){
        let niobium = 0;
        let water = 0;
        let worker = 0;
        let soldier = 0;
        let bitcoin = 0;
        let pdg = 0;
        let product = 0;
        $('.nbrProduct').each( function(){
            if($(this).val() > 0) {
                if (product == 0) {
                    product = $(this).val();
                } else {
                    product = parseFloat(product) + parseFloat($(this).val());
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.niobiumProduct').text().replace('.', '');
                if (niobium == 0) {
                    niobium = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    niobium = niobium + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.waterProduct').text().replace('.', '');
                if (water == 0) {
                    water = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    water = water + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.workerProduct').text().replace('.', '');
                if (worker == 0) {
                    worker = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    worker = worker + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.soldierProduct').text().replace('.', '');
                if (soldier == 0) {
                    soldier = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    soldier = soldier + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.bitcoinProduct').text().replace('.', '');
                if (bitcoin == 0) {
                    bitcoin = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    bitcoin = bitcoin + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        $('tr').each( function(){
            if($(this).find('.nbrProduct').val() > 0) {
                let delPoint = $(this).find('.pdgProduct').text().replace('.', '');
                if (product == 0) {
                    pdg = parseFloat($(this).find('.nbrProduct').val() * delPoint);
                } else {
                    pdg = pdg + parseFloat($(this).find('.nbrProduct').val() * delPoint);
                }
            }
        });
        let re = new RegExp(',', 'g')
        if(niobium <= parseFloat($('#niobium').text().replace(re, ''))) {
            $('#niobiumProduct').text(niobium);
        } else {
            $('#niobiumProduct').html("<span class='text-rouge'>" + niobium + "</span>");
        }
        if(water <= parseFloat($('#water').text().replace(re, ''))) {
            $('#waterProduct').text(water);
        } else {
            $('#waterProduct').html("<span class='text-rouge'>" + water + "</span>");
        }
        if(worker <= parseFloat($('#worker').text().replace(re, ''))) {
            $('#workerProduct').text(worker);
        } else {
            $('#workerProduct').html("<span class='text-rouge'>" + worker + "</span>");
        }
        if(soldier <= parseFloat($('#soldier').text().replace(re, ''))) {
            $('#soldierProduct').text(soldier);
        } else {
            $('#soldierProduct').html("<span class='text-rouge'>" + soldier + "</span>");
        }
        if(bitcoin <= parseFloat($('#bitcoin').text().replace(re, ''))) {
            $('#bitcoinProduct').text(bitcoin);
        } else {
            $('#bitcoinProduct').html("<span class='text-rouge'>" + bitcoin + "</span>");
        }
        if(pdg <= parseFloat($('#pdg').text().replace(re, ''))) {
            $('#pdgProduct').text(pdg);
        } else {
            $('#pdgProduct').html("<span class='text-rouge'>" + pdg + "</span>");
        }
        $('#nbrProduct').text(product);
    });
}

function manageZbMission() {
    $('.zbMission .zbForm').off('change').on('change',function(e){
        let succeedZb = 0;
        let gainZb = 0;
        let soldierZb = 0;
        let tankZb = 0;
        let timeZb = 0;
        let zombie = Math.abs($('.zombieIndicator').text()) * 75;
        let zombieTotal = zombie;
        if($('#mission_soldier').attr('max') - $('#mission_soldier').val() < 0) {
            $('#mission_soldier').val($('#mission_soldier').attr('max'));
        }
        if($('#mission_tank').attr('max') - $('#mission_tank').val() < 0) {
            $('#mission_tank').val($('#mission_tank').attr('max'));
        }
        if($('.zbMission .nbrSoldier').val() > 0) {
            soldierZb = $('.zbMission .nbrSoldier').val() * 2;
        }
        if($('.zbMission .nbrTank').val() > 0) {
            tankZb = $('.zbMission .nbrTank').val() * 75;
        }
        zombie = zombie - soldierZb - tankZb;
        if (zombie <= 1) {
            zombie = 1;
        } else {
            zombie = 1 + ((100 * zombie) / zombieTotal) / 100;
        }
        if($('.zbMission .nbrTime').val() > 0) {
            timeZb = $('.zbMission .nbrTime').val();
            if (timeZb == 1) {
                gainZb = -2;
                succeedZb = Math.round(90 / zombie);
            } else if (timeZb == 2) {
                gainZb = -5;
                succeedZb = Math.round(70 / zombie);
            } else if (timeZb == 3) {
                gainZb = -8;
                succeedZb = Math.round(50 / zombie);
            } else if (timeZb == 4) {
                gainZb = -15;
                succeedZb = Math.round(30 / zombie);
            }
        }
        if(succeedZb >= 70) {
            $('.zbMission .zombiePercent').html("<span class='text-vert'>" + succeedZb + "%</span>");
        } else if (succeedZb >= 50) {
            $('.zbMission .zombiePercent').html("<span class='text-orange'>" + succeedZb + "%</span>");
        } else {
            $('.zbMission .zombiePercent').html("<span class='text-rouge'>" + succeedZb + "%</span>");
        }
        $('.zbMission .zombieGain').html("<span class='text-vert'>" + gainZb + "</span>");
    });
}

function manageUraMission() {
    $('.uraMission .zbForm').off('change').on('change',function(e){
        let succeedUra = 0;
        let gainUra = 0;
        let soldierUra = 0;
        let tankUra = 0;
        let timeUra = 0;
        let zombie = $('.zombieIndicator').text() * 75;
        if (zombie <= 0) {
            zombie = 1;
        }
        let zombieTotal = zombie;
        if($('#mission_ura_soldier').attr('max') - $('#mission_ura_soldier').val() < 0) {
            $('#mission_ura_soldier').val($('#mission_ura_soldier').attr('max'));
        }
        if($('#mission_ura_tank').attr('max') - $('#mission_ura_tank').val() < 0) {
            $('#mission_ura_tank').val($('#mission_ura_tank').attr('max'));
        }
        if($('.uraMission .nbrSoldier').val() > 0) {
            soldierUra = $('.uraMission .nbrSoldier').val();
        }
        if($('.uraMission .nbrTank').val() > 0) {
            tankUra = $('.uraMission .nbrTank').val() * 75;
        }
        zombie = zombie - soldierUra - tankUra;
        if (zombie <= 1) {
            zombie = 1;
        } else {
            zombie = 1 + ((100 * zombie) / zombieTotal) / 100;
        }
        if($('.uraMission .nbrTime').val() > 0) {
            timeUra = $('.uraMission .nbrTime').val();
            if (timeUra == 1) {
                gainUra = 2;
                succeedUra = Math.round(90 / zombie);
            } else if (timeUra == 2) {
                gainUra = 5;
                succeedUra = Math.round(70 / zombie);
            } else if (timeUra == 3) {
                gainUra = 8;
                succeedUra = Math.round(50 / zombie);
            } else if (timeUra == 4) {
                gainUra = 15;
                succeedUra = Math.round(30 / zombie);
            }
        }
        if(succeedUra >= 70) {
            $('.uraMission .uraniumPercent').html("<span class='text-vert'>" + succeedUra + "%</span>");
        } else if (succeedUra >= 50) {
            $('.uraMission .uraniumPercent').html("<span class='text-orange'>" + succeedUra + "%</span>");
        } else {
            $('.uraMission .uraniumPercent').html("<span class='text-rouge'>" + succeedUra + "%</span>");
        }
        $('.uraMission .uraniumGain').html("<span class='text-vert'>+" + gainUra + "</span>");
    });
}

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'hover'
    });
    $('[data-toggle="confirmation"]').confirmation();
    $("body").scrollspy({
        target: "#navbar-rules",
        offset: 70
    });

    $('.tipFleet').on("mouseover", function() {
        $(this).tooltip('show');
    })

    $('.tipProduct').on("mouseover", function() {
        $(this).tooltip('show');
    })

    $('.tipProduct').on("mouseout", function() {
        $(this).tooltip('hide');
    })

    $(document).click(function() {
        $('.tipFleet').tooltip('hide');
        $("body").find(".modal-backdrop").css("display", "none");
        $("body").find(".popover.confirmation").attr('hidden', 'hidden');
    });

    manageFleetListChoiceForm();
    manageTotalCaserne();
    manageImageForm();
    manageAllyImageForm();
    manageModalContact();
    manageTime();
    manageRegistrationCheck();
    managePlanetChoiceForm();
    manageFlightTime();
    manageCoordonate();
    manageAttackFleetForm();
    manageMaxShip();
    manageMaxClick();
    managePlanetSellerChoiceForm();
    //manageRenameFleetForm();
    manageDisplaySalon();
    manageSalon();
    manageFocusSalon();
    managePointShip();
    manageClickShip();
    manageTotalShip();
    manageZbMission();
    manageUraMission();
    console.log("Toute utilisation de scripts sur le jeu seront puni d'un ban permanent, merci.");
});