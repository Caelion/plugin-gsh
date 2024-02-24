
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

actionOptions = []

$('.deviceAttr[data-l1key=options][data-l2key=challenge]').off('change').on('change',function(){
  $(this).closest('tr').find('.challenge').hide();
  if(+$(this).value() != ''){
    $(this).closest('tr').find('.challenge.'+$(this).value()).show();
  }
});

$('.nav-tabs li a').off('click').on('click',function(){
  setTimeout(function(){
    jeedomUtils.taAutosize();
  }, 50);
})

$('#div_modes').off('click','.panel-heading').on('click','.panel-heading',function(){
  setTimeout(function(){
    jeedomUtils.taAutosize();
  }, 50);
})

$('.bt_configureEqLogic').off('click').on('click',function(){
  $('#md_modal').dialog({title: "{{Configuration de l'équipement}}"})
  .load('index.php?v=d&modal=eqLogic.configure&eqLogic_id=' + $(this).attr('data-id')).dialog('open');
});


$('.bt_advanceConfigureEqLogic').off('click').on('click',function(){
  $('#md_modal').dialog({title: "{{Configuration avancée}}"})
  .load('index.php?v=d&plugin=gsh&modal=advanceConfig&eqLogic_id=' + $(this).attr('data-id')).dialog('open');
});

$('#div_configuration').off('click','.bt_needGenericType').on('click','.bt_needGenericType',function(){
  $('#md_modal').dialog({title: "{{Information type générique}}"})
  .load('index.php?v=d&plugin=gsh&modal=showNeedGenericType&eqLogic_id=' + $(this).closest('tr').attr('data-link_id')).dialog('open');
});

$('#bt_saveConfiguration').off('click').on('click',function(){
  var devices = $('#div_configuration .device[data-link_type=eqLogic]').getValues('.deviceAttr');
  $('#div_scenes .scene').each(function () {
    var scene = $(this).getValues('.sceneAttr')[0];
    scene.options.inAction = $(this).find('.inAction').getValues('.expressionAttr');
    scene.options.outAction = $(this).find('.outAction').getValues('.expressionAttr');
    devices.push(scene);
  });
  
  $.ajax({
    type: "POST",
    url: "plugins/gsh/core/ajax/gsh.ajax.php",
    data: {
      action: "saveDevices",
      devices : JSON.stringify(devices),
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      sendDevices();
    },
  });
});

function sendDevices(){
  $.ajax({
    type: "POST",
    url: "plugins/gsh/core/ajax/gsh.ajax.php",
    data: {
      action: "sendDevices",
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      $('#div_alert').showAlert({message: '{{Synchronisation réussie. Pour voir le status des équipements à jour, merci de rafraîchir la page (F5)}}', level: 'success'});
    },
  });
}

function loadData(){
  $("#div_scenes").empty();
  $.ajax({
    type: "POST",
    url: "plugins/gsh/core/ajax/gsh.ajax.php",
    data: {
      action: "allDevices"
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      for(var i in data.result){
        if(data.result[i]['link_type'] == 'scene'){
          addScene(data.result[i]);
          continue;
        }
        var el = $('.device[data-link_id='+data.result[i]['link_id']+'][data-link_type='+data.result[i]['link_type']+']');
        if(!el){
          continue;
        }
        el.setValues(data.result[i], '.deviceAttr');
        if(data.result[i].options && data.result[i].options.configState){
          if(data.result[i].options.configState == 'OK'){
            el.find('.deviceAttr[data-l2key=configState]').removeClass('label-danger').addClass('label-success bt_needGenericType cursor');
          }else{
            el.find('.deviceAttr[data-l2key=configState]').removeClass('label-success').addClass('label-danger bt_needGenericType cursor');
          }
        }
      }
      $('#eqlogictab table').trigger('update')
      
      jeedom.cmd.displayActionsOption({
        params : actionOptions,
        async : false,
        error: function (error) {
          $('#div_alert').showAlert({message: error.message, level: 'danger'});
        },
        success : function(data){
          for(var i in data){
            $('#'+data[i].id).append(data[i].html.html);
          }
          jeedomUtils.taAutosize();
        }
      });
    },
  });
}

loadData();

$('#bt_displayDevice').off('click').on('click',function(){
  $('#md_modal').dialog({title: "{{Configuration péripheriques}}"});
  $('#md_modal').load('index.php?v=d&plugin=gsh&modal=showDevicesConf').dialog('open');
});


$('#bt_addScene').off('click').on('click', function () {
  bootbox.prompt("{{Nom de la scène ?}}", function (result) {
    if (result !== null && result != '') {
      addScene({options : {name: result}});
    }
  });
});

$('body').off('click', '.rename').on('click', '.rename', function () {
  var el = $(this);
  bootbox.prompt("{{Nouveau nom ?}}", function (result) {
    if (result !== null && result != '') {
      el.text(result);
      el.closest('.panel.panel-default').find('span.name').text(result);
    }
  });
});

$("body").off('click','.listCmdAction').on('click','.listCmdAction',  function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      jeedomUtils.taAutosize();
    });
  });
});

$("body").off('click','.listAction').on('click','.listAction',  function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      jeedomUtils.taAutosize();
    });
  });
});

$("body").off( 'click','.bt_removeAction').on('click','.bt_removeAction', function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

$("#div_scenes").off('click','.bt_addInAction').on('click','.bt_addInAction', function () {
  addAction({}, 'inAction', '{{Action d\'entrée}}', $(this).closest('.scene'));
});

$("#div_scenes").off( 'click','.bt_addOutAction').on( 'click','.bt_addOutAction', function () {
  addAction({}, 'outAction', '{{Action de sortie}}', $(this).closest('.scene'));
});

$('body').off( 'focusout','.cmdAction.expressionAttr[data-l1key=cmd]').on( 'focusout','.cmdAction.expressionAttr[data-l1key=cmd]', function (event) {
  var type = $(this).attr('data-type')
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
    jeedomUtils.taAutosize();
  })
});

$("#div_scenes").off( 'click','.bt_removeScene').on( 'click','.bt_removeScene', function () {
  $(this).closest('.scene').remove();
});


function addAction(_action, _type, _name, _el) {
  if (!isset(_action)) {
    _action = {};
  }
  if (!isset(_action.options)) {
    _action.options = {};
  }
  var input = '';
  var button = 'btn-default';
  if (_type == 'outAction') {
    input = 'has-warning';
    button = 'btn-warning';
  }
  if (_type == 'inAction') {
    input = 'has-success';
    button = 'btn-success';
  }
  var div = '<div class="' + _type + '">';
  div += '<div class="form-group ">';
  div += '<label class="col-sm-2 control-label">' + _name + '</label>';
  div += '<div class="col-sm-1  ' + input + '">';
  div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher pour désactiver l\'action}}" />';
  div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher pour que la commande s\'éxecute en parrallèle des autres actions}}" />';
  div += '</div>';
  div += '<div class="col-sm-4 ' + input + '">';
  div += '<div class="input-group">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-default bt_removeAction btn-sm roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
  div += '</span>';
  div += '<input class="expressionAttr form-control input-sm cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
  div += '<span class="input-group-btn">';
  div += '<a class="btn ' + button + ' btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
  div += '<a class="btn ' + button + ' btn-sm listCmdAction roundedRight" data-type="' + _type + '"><i class="fas fa-list-alt"></i></a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  var actionOption_id = jeedomUtils.uniqId();
  div += '<div class="col-sm-5 actionOptions" id="'+actionOption_id+'">';
  div += '</div>';
  div += '</div>';
  if (isset(_el)) {
    _el.find('.div_' + _type).append(div);
    _el.find('.' + _type + '').last().setValues(_action, '.expressionAttr');
  } else {
    $('#div_' + _type).append(div);
    $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
  }
  actionOptions.push({
    expression : init(_action.cmd, ''),
    options : _action.options,
    id : actionOption_id
  });
}

function addScene(_scene) {
  if (init(_scene.options.name) == '') {
    return;
  }
  var random = Math.floor((Math.random() * 1000000) + 1);
  var div = '<div class="scene panel panel-default">';
  div += '<div class="panel-heading">';
  div += '<h4 class="panel-title">';
  div += '<a data-toggle="collapse" data-parent="#div_scenes" href="#collapse' + random + '">';
  div += '<span class="name">' + _scene.options.name + '</span>';
  div += '</a>';
  div += '</h4>';
  div += '</div>';
  div += '<div id="collapse' + random + '" class="panel-collapse collapse in">';
  div += '<div class="panel-body">';
  div += '<div class="well">';
  div += '<form class="form-horizontal" role="form">';
  div += '<div class="form-group">';
  div += '<div class="col-sm-12">';
  div += '<div class="input-group pull-right" style="display:inline-flex">';
  div += '<span class="input-group-btn">';
  div += '<a class="btn btn-sm bt_addInAction btn-succes roundedLeft"><i class="fas fa-plus-circle"></i> {{Action d\'entrée}}</a>';
  div += '<a class="btn btn-warning btn-sm bt_addOutAction"><i class="fas fa-plus-circle"></i> {{Action de sortie}}</a>';
  div += '<a class="btn btn-sm bt_removeScene btn-danger roundedRight"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>';
  div += '</span>';
  div += '</div>';
  div += '</div>';
  div += '<div class="form-group">';
  div += '<label class="col-sm-2 control-label">{{Nom de la scène}}</label>';
  div += '<div class="col-sm-10">';
  div += '<input class="sceneAttr" data-l1key="id" style="display:none;" />';
  div += '<input class="sceneAttr" data-l1key="enable" style="display:none;" value="1" />';
  div += '<input class="sceneAttr" data-l1key="link_type" style="display:none;" value="scene" />';
  div += '<input class="sceneAttr" data-l1key="type" style="display:none;" value="action.devices.types.SCENE" />';
  div += '<span class="sceneAttr label label-info rename cursor" data-l1key="options" data-l2key="name" style="font-size : 1em;" ></span>';
  div += '</div>';
  div += '</div>';
  div += '<div class="form-group">';
  div += '<label class="col-sm-2 control-label">{{Pièce}}</label>';
  div += '<div class="col-sm-3">';
  div += '<select class="sceneAttr form-control" data-l1key="options" data-l2key="piece">';
  div += '<option value="">Aucun</option>';
  jeedom.object.all({
    async : false,
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (objects) {
      for (var i in objects) {
        div += '<option value="' + objects[i].name + '">' + objects[i].name+'</option>';
      }
    }
  });
  div += '</select>';
  div += '</div>';
  div += '<label class="col-sm-2 control-label">{{Pseudo}}</label>';
  div += '<div class="col-sm-5">';
  div += '<input class="sceneAttr form-control" data-l1key="options" data-l2key="pseudo"/>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  div += '<hr/>';
  div += '<div class="div_inAction"></div>';
  div += '<hr/>';
  div += '<div class="div_outAction"></div>';
  div += '</form>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  div += '</div>';
  
  $('#div_scenes').append(div);
  $('#div_scenes .scene').last().setValues(_scene, '.sceneAttr');
  if (is_array(_scene.options.inAction)) {
    for (var i in _scene.options.inAction) {
      addAction(_scene.options.inAction[i], 'inAction', '{{Action d\'entrée}}', $('#div_scenes .scene').last());
    }
  } else {
    if ($.trim(_scene.options.inAction) != '') {
      addAction(_scene.options.inAction[i], 'inAction', '{{Action d\'entrée}}', $('#div_scenes .scene').last());
    }
  }
  
  if (is_array(_scene.options.outAction)) {
    for (var i in _scene.options.outAction) {
      addAction(_scene.options.outAction[i], 'outAction', '{{Action de sortie}}', $('#div_scenes .scene').last());
    }
  } else {
    if ($.trim(_scene.options.outAction) != '') {
      addAction(_scene.options.outAction, 'outAction', '{{Action de sortie}}', $('#div_scenes .scene').last());
    }
  }
  $('.collapse').collapse();
  $("#div_scenes .scene:last .div_inAction").sortable({axis: "y", cursor: "move", items: ".inAction", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
  $("#div_scenes .scene:last .div_outAction").sortable({axis: "y", cursor: "move", items: ".outAction", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
}
