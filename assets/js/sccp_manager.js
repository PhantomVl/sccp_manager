$(document).ready(function () {

    $('.sortable').sortable({
        update: function (event, ui) {
            //           console.log(ui.item.find('input').val(), ui.item.index())
            ui.item.find('input').val(ui.item.index());
        },
    });
    $('#ajaxcancel').on('click', function (e) {
//        console.log('Calncel');
        if ($(this).data('hash') != null) {
            location.hash = $(this).data('hash');
        }
        if ($(this).data('href') != null) {
            location.href = $(this).data('href');
        }
        if ($(this).data('path') != null) {
            location.path = $(this).data('path');
        }
        if ($(this).data('search') != null) {
            location.search = $(this).data('search');
        }
        if ($(this).data('reload') != null) {
            location.reload();
        }
    });

    /*
     $('#ajaxbackup').on('click', function (e) {
     var vdata = '';
     var snd_command = 'backupsettings';
     $('.fpbx-submit').each(function () {
     vdata = vdata + $(this).serialize() + '&';
     });

     $.ajax({
     type: 'POST',
     url: 'ajax.php?module=sccp_manager&command=' + snd_command,
     data: vdata,
     success: function (data) {
     if (data.status === true) {
     if (data.message) {
     alert(data.message);
     } else {
     alert('Data Save');
     }
     } else {
     if (Array.isArray(data.message)) {
     data.message.forEach(function (entry) {
     fpbxToast(entry, 'error', 'error');
     });
     }
     }
     }
     });

     });
     */
    $('#ajaxsubmit2').on('click', function (e) {
        var vdata = '';
        var snd_command = 'savesettings';
        console.log($('.fpbx-submit').data('id'));
        $('.fpbx-submit').each(function () {
            vdata = vdata + $(this).serialize() + '&';
        });
        if ($('.fpbx-submit').data('id') == "hw_edit") {
            snd_command = 'save_hardware';
        }
        if ($('.fpbx-submit').data('id') == "hw_sedit") {
            snd_command = 'save_sip_hardware';
        }
        if ($('.fpbx-submit').data('id') == "ruser_edit") {
            snd_command = 'save_ruser';
        }
        if ($('.fpbx-submit').data('id') == "dial_template") {
            snd_command = 'save_dialplan_template';
        }

        $.ajax({
            type: 'POST',
            url: 'ajax.php?module=sccp_manager&command=' + snd_command,
            data: vdata,
            success: function (data) {
                if (data.status === true) {
                    if (data.message) {
                        bs_alert(data.message,data.status);
                    } else {
                        bs_alert('Data Save',data.status);
                    }
                } else {
                    bs_alert(data.message,data.status);
                }
            }
        });
    });

    $('#ajaxsubmit').on('click', function (e) {
        var vdata = '';
        var snd_command = 'savesettings';
        $('.fpbx-submit').each(function () {
            vdata = vdata + $(this).serialize() + '&';
        });
        if ($('.fpbx-submit').data('id') == "hw_edit") {
            snd_command = 'save_hardware';
        }
        if ($('.fpbx-submit').data('id') == "hw_sedit") {
            snd_command = 'save_sip_hardware';
        }
        if ($('.fpbx-submit').data('id') == "ruser_edit") {
            snd_command = 'save_ruser';
        }
        if ($('.fpbx-submit').data('id') == "dial_template") {
            snd_command = 'save_dialplan_template';
        }
        $.ajax({
            type: 'POST',
            url: 'ajax.php?module=sccp_manager&command=' + snd_command,
            data: vdata,
            success: function (data) {
                if (data.status === true) {
                    if (data.message) {
                        var old_style = bs_alert(data.message, data.status, data.reload);
                    }
                    if (data.table_reload === true) {
                        $('table').bootstrapTable('refresh');
                    }
                    if (data.hash != null) {
                        location.hash = data.hash;
                    }
                    if (data.href != null) {
                        location.href = data.href;
                    }
                    if (data.path != null) {
                        location.path = data.path;
                    }
                    if (data.search != null) {
                        location.search = data.search;
                    }
//                    if (data.reload === true ) {
                    if (data.reload === true && old_style ===true ) {
                        location.reload();
                    }
                } else {
                    bs_alert(data.message,data.status);
                }
            }
        });
    });

    $(".input-js-add").click(function () {
        add_dynamic_input($(this), $(this).data('for'), "", "");
    });

    $(".table").on('click', '.table-js-add', function (e) {
        add_dynamic_table($(this), $(this).data('for'), "", "");
    });

    $(".table").on('click', '.table-js-del', function (e) {
        del_dynamic_table($(this), $(this).data('for'));
    });


    $(".table").on('click', '.btn-item-delete', function (e) {
        var dev_cmd = '';
        var dev_id = $(this).data('id');
        var dev_for = $(this).data('for');
        var ext_data = '';
        if (dev_for == 'softkeys') {
            dev_cmd = 'deleteSoftKey';
            ext_data = "softkey=" + dev_id;
        }
        if (dev_for == 'model') {
            dev_cmd = 'model_delete';
            ext_data = "model=" + dev_id;
        }
        if (dev_for == 'dialplan') {
            dev_cmd = 'delete_dialplan';
            ext_data = "dialplan=" + dev_id;
        }
        if (dev_for == 'hardware') {
            dev_cmd = 'delete_hardware';
            ext_data = "idn[0]=" + dev_id;
        }
//    console.log("delete : " + data);
        if (dev_cmd != '') {
            if (confirm(_('Are you sure you wish to delete "' + dev_id.toString().toUpperCase() + '" inormation ?'))) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php?module=sccp_manager&command=' + dev_cmd,
                    command: dev_cmd,
                    data: ext_data,
                    success: function (data) {
//                console.log(data);
                        if (data.status === true) {
                            if (data.message) {
                                var old_style = bs_alert(data.message, data.status, data.reload);
                            }
                            if (data.table_reload === true) {
                                $('table').bootstrapTable('refresh');
                            }
                            if (data.reload === true && old_style === true ) {
                                location.reload();
                            }
                        } else {
                            bs_alert(data.message,data.status);
                        }
                    }

                });
            }
        }

    });



// ----------------------- Server.model.Button.Select----------------

    $('.dropdown-menu a.dropitem').on("click", function (e) {

        $(this).parents('div.btn-group').find('.dropdown_capture').text($(this).text());
//        console.log($(this).data('id'));
        ref_url = "ajax.php?module=sccp_manager&command=getDeviceModel&type=" + $(this).data('id');
        $('#table-models').bootstrapTable('refresh', {url: ref_url});
    });
// ---------------------------------------

    $('.btnMultiselect').click(function (e) {
        var kid = $(this).data('id');
        if ($(this).data('key') === 'Right') {
            $('select').moveToListAndDelete('#source_' + kid, '#destination_' + kid);
        }
        if ($(this).data('key') === 'AllRight') {
            $('select').moveAllToListAndDelete('#source_' + kid, '#destination_' + kid);
        }
        if ($(this).data('key') === 'Left') {
            $('select').moveToListAndDelete('#destination_' + kid, '#source_' + kid);
        }
        if ($(this).data('key') === 'AllLeft') {
            $('select').moveAllToListAndDelete('#destination_' + kid, '#source_' + kid);
        }
        e.preventDefault();
    });

// Form.buttons - Form.adddevice
    $('.futuretype').change(function (e) {
        var kid = $(this).data('id');
        var kval = $(this).val();
        $('.lineid_' + kid).each(function () {
            var lval = $(this).data('type');
            if (lval == 'featurep') {
                if ( kval == 'parkinglot') {
                    $(this).removeClass('hidden');
                } else {
                    $(this).addClass('hidden');
                }
            }
        });

    });

    $('.buttontype').change(function (e) {
        var kid = $(this).data('id');
        var kval = $(this).val();
        $('.lineid_' + kid).each(function () {
            var lval = $(this).data('type');
            var class_id = [kval];
            switch (kval) {
                case 'silent':
                case 'monitor':
                case 'line':
                    class_id = ['line'];
                    break;
                case 'adv.line':
                    class_id = ['line','adv_line'];
                    break;
                case 'service':
                case 'feature':
                    if (lval == 'featurep') {
                        if ($('.futuretype').val() == 'parkinglot') {
                            class_id = [kval,lval];
                        }
                    }
                    break;
                case 'speeddial':
                    class_id = ['speeddial','hintline'];
                    break;
                case 'empty':
                    class_id = [];
                    break;
            }
            var pos = class_id.indexOf(lval);
            if (pos >= 0) {
                $(this).removeClass('hidden');
            } else {
                console.log(lval);
                $(this).addClass('hidden');
            }
        });
    });
// Form.adddevice
    $('.hw_select').change(function (e) {
//        console.log('HwSelect');
        var type_id = $('#sccp_hw_type').find(':selected').data('id');
        var btn_dev = $('#sccp_hw_type').find(':selected').data('val');
//        var btn_add=$('#sccp_hw_addon').find(':selected').data('val');

        if (type_id === 1) {
            if ($('#sccp_hw_addon').val() !== 'NONE') {
                $('#sccp_hw_addon').val('NONE').change();
            }
            $('#sccp_hw_addon').attr("disabled", "disabled");
        } else {
            $('#sccp_hw_addon').removeAttr('disabled');
        }

        var btn_add = $('#sccp_hw_addon').find(':selected').data('val');
        $('.line_button').each(function () {
            if ($(this).data('id') < btn_dev + btn_add) {
                $(this).removeClass('hidden');
                $(this).removeAttr('hidden')
            } else {
                $(this).addClass('hidden');
            }
        });

    });

    $('.sccp_button_hide').each(function () {
 // On page create !!
        var dev_id = $(this).data('vhide');
        var dev_class = $(this).data('clhide');
        var dev_val = $(this).val();
        if ($(this).data('btn') == 'checkbox') {
            dev_val = $('input', this).is(":checked");
        }
        var dev_state = $(this).attr('checked');
        if (dev_state == 'checked') {
            $(dev_class).each(function () {
                if (dev_val != dev_id) {
                    $(this).removeClass('hidden');
                    $(this).removeAttr('hidden')
                } else {
                    $(this).addClass('hidden');
                }
            });
        }

        dev_id = $(this).data('vshow');
        if (dev_state == 'checked') {
            dev_class = $(this).data('clshow');
            $(dev_class).each(function () {
                if (dev_val == dev_id) {
                    $(this).removeClass('hidden');
                    $(this).removeAttr('hidden')
                } else {
                    $(this).addClass('hidden');
                }
            });
        }
    });
    $('.sccp_button_hide').on('click', function (e) {
        var dev_id = $(this).data('vhide');
        var dev_class = $(this).data('clhide');
        var dev_val = $(this).val();
        if ($(this).data('btn') == 'checkbox') {
            dev_val = $('input', this).is(":checked");
        }
        $(dev_class).each(function () {
            if (dev_val != dev_id) {
                $(this).removeClass('hidden');
                $(this).removeAttr('hidden')
            } else {
                $(this).addClass('hidden');
            }
        });

        dev_id = $(this).data('vshow');
        if (dev_id !== '') {
            dev_class = $(this).data('clshow');
            $(dev_class).each(function () {
                if (dev_val == dev_id) {
                    $(this).removeClass('hidden');
                    $(this).removeAttr('hidden')
                } else {
                    $(this).addClass('hidden');
                }
            });
        }

    });

    $('.sccp_button_disabled').each(function () {
 // On page create !!
        var dev_id = $(this).data('vhide');
        var dev_class = $(this).data('clhide');
        var dev_val = $(this).val();
        if ($(this).data('btn') == 'checkbox') {
            dev_val = $('input', this).is(":checked");
        }
        $(dev_class).find('input, select, textarea, button').each(function () {
            if (dev_val == dev_id) {
                $(this).removeClass('disabled');
                $(this).removeAttr('disabled')
            } else {
                $(this).addClass('disabled', true);
                $(this).attr('disabled', 'disabled');
            }
        });
    });

    $('.sccp_button_disabled').on('click', function (e) {
        var dev_id = $(this).data('vhide');
        var dev_class = $(this).data('clhide');
        var dev_val = $(this).val();
        if ($(this).data('btn') == 'checkbox') {
            dev_val = $('input', this).is(":checked");
        }
        $(dev_class).find('input, select, textarea, button').each(function () {
            if (dev_val != dev_id) {
                $(this).removeClass('disabled');
                $(this).removeAttr('disabled')
            } else {
                $(this).addClass('disabled', true);
                $(this).attr('disabled', 'disabled');
            }
        });
    });


    $('.button-checkbox').on('click', '', function (e) {
        settings = {
            true: {
                icon: 'glyphicon glyphicon-unchecked'
            },
            false: {
                icon: 'glyphicon glyphicon-check'
            }
        };
        var button_1 = $('button', this);
        var isChecked = $('input', this).is(':checked');

        if (button_1.find('.state-icon').length == 0) {
            button_1.prepend('<i class="state-icon ' + settings[isChecked].icon + '"></i> ');
        } else {
            button_1.find('.state-icon')
                    .removeClass()
                    .addClass('state-icon ' + settings[isChecked].icon);
        }
        if (isChecked) {
            $('input', this).removeAttr('checked');
            button_1.removeClass('active');
        } else {
            $('input', this).attr('checked');
            $('input', this).prop('checked', 'true');
            button_1.addClass('active');
        }
    });



// ----------------------- TEST Validate ----------------
    $('.need-validate').on('change', function (e) {
        var dev_class = $(this).attr('class');
        var dev_id = $(this).val();
        if (dev_class.includes('validate-netmask')) {
            confirm(dev_id);
        }
//        confirm(dev_id);
    });

    $('.sccp_test').on('click', function (e) {
        var dev_id = [];
//        $('table').bootstrapTable('getSelections').forEach(function (entry) {
//            dev_id.push(entry['name']);
//        });
        dv = dev_id;
        confirm(dv);
    });
// ----------------------- TEST ----------------
    $('.test').on('click', function (e) {
        var dev_fld = ['onhook', 'connected', 'onhold', 'ringin', 'offhook', 'conntrans', 'digitsfoll', 'connconf', 'ringout', 'offhookfeat',
            'onhint', 'onstealable', 'holdconf', 'uriaction'];
//            var x=document.getElementById("source_onhook");
//            var x=$("#source_onhook")[0];
//            console.log(x.length);
        var datas = '';
        var tmp_val = '';
        var dev_opt = '';
        for (var i = 0; i < dev_fld.length; i++) {
            dev_opt = $('#destination_' + dev_fld[i])[0];
            tmp_val = '';
            for (var n = 0; n < dev_opt.length; n++) {
                if (n > 0) {
                    tmp_val += ',';
                }
                tmp_val += dev_opt.options[n].value;
            }
            datas += dev_fld[i] + '=' + tmp_val + '&';
        }
        ;
//        console.log(datas);
    });


    $('.sccp_update').on('click', function (e) {
//        console.log($(this).data('id'));

// ----------------------- Server.keyset form ----------------
//
        if ($(this).data('id') === 'keyset_add') {
            var dev_cmd = 'updateSoftKey';
            if ($(this).data('mode') === 'new') {
                dev_cmd = 'updateSoftKey';
            }
            var dev_fld = ['onhook', 'connected', 'onhold', 'ringin', 'offhook', 'conntrans', 'digitsfoll', 'connconf', 'ringout', 'offhookfeat',
                'onhint', 'onstealable', 'holdconf'];
            var datas = 'id=' + $('#new_keySetname').val() + '&';
            var tmp_val = '';
            var dev_opt = '';

            for (var i = 0; i < dev_fld.length; i++) {
                tmp_val = '';
                dev_opt = $('#destination_' + dev_fld[i])[0];
                for (var n = 0; n < dev_opt.length; n++) {
                    if (n > 0) {
                        tmp_val += ',';
                    }
                    tmp_val += dev_opt.options[n].value;
                }
                datas += dev_fld[i] + '=' + tmp_val + '&';
            }
            ;
        }

// ----------------------- Server.model form ----------------

        if ($(this).data('id') === 'model_add') {
            var dev_cmd = 'model_add';
//            var dev_fld = ["model","vendor","dns","buttons","loadimage","loadinformationid","validate","enabled"];
            var dev_fld = ["model", "vendor", "dns", "buttons", "loadimage", "loadinformationid", "nametemplate"];
            datas = 'enabled=0' + '&';

            for (var i = 0; i < dev_fld.length; i++) {
                datas = datas + dev_fld[i] + '=' + $('#new_' + dev_fld[i]).val() + '&';
            }
            ;

//            $("#add_new_model").modal('hide');
        }
        if ($(this).data('id') === 'model_apply') {
            var dev_cmd = 'model_update';
            var dev_fld = ["model", "loadimage", "nametemplate"];
            datas = '';
            for (var i = 0; i < dev_fld.length; i++) {
                datas = datas + dev_fld[i] + '=' + $('#editd_' + dev_fld[i]).val() + '&';
            }
            ;
//            $("#edit_model").modal('hide');
        }
        if (($(this).data('id') === 'model_enabled') || ($(this).data('id') === 'model_disabled')) {
            var dev_cmd = $(this).data('id');
            var tab_name = '#' + $(this).data('table');
            var datas = '';
            var i = 0;
            $(tab_name).bootstrapTable('getSelections').forEach(function (entry) {
                datas = datas + 'model[' + i + ']=' + entry['model'] + '&';
                i++;
            });
        }
// ----------------------- form ----------------
        if ($(this).data('id') === 'create-cnf') {
            var dev_cmd = 'create_hw_tftp';
            var datas = '';
            var i = 0;
            $('#table-sccp').bootstrapTable('getSelections').forEach(function (entry) {
                datas = datas + 'idn[' + i + ']=' + entry['name'] + '&';
                i++;
            });
            if (document.getElementById('table-sip') != null ) {
                $('#table-sip').bootstrapTable('getSelections').forEach(function (entry) {
                    datas = datas + 'idn[' + i + ']=' + entry['name'] + '&';
                    i++;
                });
            }
            console.log(datas);
        }
        if ($(this).data('id') === 'delete_hardware') {
            var dev_cmd = $(this).data('id');
            var datas = '';
            var i = 0;
            $('#table-sccp').bootstrapTable('getSelections').forEach(function (entry) {
                datas = datas + 'idn[' + i + ']=' + entry['name'] + '&';
                i++;
            });
            if (document.getElementById('table-sip') != null ) {
                $('#table-sip').bootstrapTable('getSelections').forEach(function (entry) {
                    datas = datas + 'idn[' + i + ']=' + entry['name'] + '&';
                    i++;
                });
            }
            if (!confirm(_('Are you sure you wish to delete selected device ?'))) {
                dev_cmd = '';
            }
        }
        if ($(this).data('id') === 'reset_dev' || $(this).data('id') === 'reset_token' || $(this).data('id') === 'update_button_label') {
            var dev_cmd = $(this).data('id');
            var datas = '';
            var i = 0;
            var conf_msg = '??????';
            if ($(this).data('id') === 'reset_dev') {
                conf_msg = 'Reset All device ?';
            }
            if ($(this).data('id') === 'reset_token') {
                conf_msg = 'Reset Token on All device ?';
            }
            if ($(this).data('id') === 'update_button_label') {
                conf_msg = 'Update Butons Labels on All device ?';
            }
            $('#table-sccp').bootstrapTable('getSelections').forEach(function (entry) {
                datas = datas + 'name[' + i + ']=' + entry['name'] + '&';
                i++;
            });
            if (document.getElementById('table-sip') != null ) {
                $('#table-sip').bootstrapTable('getSelections').forEach(function (entry) {
                    datas = datas + 'name[' + i + ']=' + entry['name'] + '&';
                    i++;
                });
            }

            if (datas === '') {
                if (confirm(conf_msg)) {
                    datas = 'name[0]=all';
                } else {
                    dev_cmd = '';
                }
            }
        }
        if (dev_cmd !== '') {
            $.ajax({
                type: 'POST',
                url: 'ajax.php?module=sccp_manager&command=' + dev_cmd,
                data: datas,
                success: function (data) {
//                    console.log(data);
                    if (data.status === true) {
                        if (data.message) {
                            var old_style = bs_alert(data.message, data.status,data.reload);
                        }
                        if (data.table_reload === true) {
                            $('table').bootstrapTable('refresh');
                        }
                        if (data.reload === true && old_style === true) {
                            location.reload();
                        }
                    } else {
                        if (Array.isArray(data.message)) {
                            data.message.forEach(function (entry) {
                                bs_alert(entry,data.status);
                                //fpbxToast(entry, 'error', 'error');
                            });
                        } else {
                            if (data.message) {
                                bs_alert(data.message,data.status);
                            } else {
                                if (data) {
                                    bs_alert(data,data.status);
                                }
                            }
                        }
                    }
                }

            });
        }

    });
    $('#cr_sccp_phone_xml').on('click', function (e) {
//        console.log("asasdasdasdasd");
//        console.log($('#update-sccp-phone').find(':selected').data('val'));

    });

});



//$("table").on('click-cell.bs.table', function (field, value, row, $element) {
//    var id_fld=$element['model']; Работает !
//    console.log('Table test: '+ id_fld);
//    $('#bt'+id_fld).removeAttr('hidden');
//});


//    Bootstrap table Enabled / Disabled buttons ( class="btn-tab-select")
$("table").on('check-all.bs.table', function (rows) {
    var id_fld = $(this).data('id');
    $(".btn-tab-select").each(function () {
        $(this).removeAttr('disabled');
    });
//    console.log('Table  unselect all' + id_fld);
});
$("table").on('check.bs.table', function (e, row) {
    var id_fld = $(this).data('id');
    $(".btn-tab-select").each(function () {
        $(this).removeAttr('disabled');
    });
//    console.log('Table  select ' + id_fld);
});
$("table").on('uncheck.bs.table', function (e, row) {
    var id_fld = $(this).data('id');
    var id_parent = '#' + $(this).parent().find('table').attr('id');
//    console.log(id_parent);
    var id_count = $(id_parent).bootstrapTable('getAllSelections').length;
    if (id_count < 1) {
        $(".btn-tab-select").each(function () {
            $(this).attr('disabled', true);
        });
    }
//    console.log('Table  unselect ' + id_count);
});
$("table").on('uncheck-all.bs.table', function (rows) {
    var id_fld = $(this).data('id');
    var id_parent = '#' + $(this).parent().find('table').attr('id');
//    console.log(id_parent);
//    var id_count = $(id_parent).bootstrapTable('getAllSelections').length;
    var id_count = $("table").bootstrapTable('getAllSelections').length;
    if (id_count < 1) {
        $(".btn-tab-select").each(function () {
            $(this).attr('disabled', true);
        });
    }
//    console.log('Table  unselect all' + id_fld);
});

//
// On table  Click !!!!!!
$("table").on("post-body.bs.table", function () {
//    console.log('Table ');
// delete extension
    $(this).find(".clickable.delete").click(function () {
        var id = $(this).data("id");

        if (confirm(_("Are you sure you wish to delete this extension?"))) {
            $.post("ajax.php", {command: "delete", module: "core", extensions: [id], type: "extensions"}, function (data) {
                if (data.status) {
                    delete(extmap[id]);
                    $(".ext-list-sccp").bootstrapTable('remove', {
                        field: "name",
                        values: [id.toString()]
                    });
                    toggle_reload_button("show");
                } else {
                    bs_alert(data.message, data.status);
                }
            });
        }
    });
});

function load_oncliсk(e, data)
{

//    console.log('load_oncliсk');
    var add_softkey = false;
    var add_btn = false;

    if (typeof e.href === 'undefined') {
        add_softkey = false;
        if (data == '*new*') {
            add_softkey = true;
            add_btn = true;
        }
    } else {
        if (e.href.indexOf('#edit_softkeys')) {
            add_softkey = true;
        }
    }

    if (add_softkey) {
        var dev_fld = ['onhook', 'connected', 'onhold', 'ringin', 'offhook', 'conntrans', 'digitsfoll', 'connconf', 'ringout', 'offhookfeat',
            'onhint', 'onstealable', 'holdconf'];
        if (add_btn) {
            document.getElementById("new_keySetname").disabled = false;
            data = 'SoftKeyset';
        } else {
            var datas = $('#softkey-all').bootstrapTable('getRowByUniqueId', data);
        }
        document.getElementById("new_keySetname").value = data;
        document.getElementById("new_keySetname").disabled = !add_btn;
        var opts = '';
        var opts_idx = -1;
        for (var i = 0; i < dev_fld.length; i++) {
            opts = $('#destination_' + dev_fld[i] + ' option');
            if (opts.length > 0) { // Remove all
                $(opts).remove();
                $('#source_' + dev_fld[i]).append($(opts).clone());
            }
            if (!add_btn) {
                sv_data = datas[dev_fld[i]].split("<br>");
                opts = $('#source_' + dev_fld[i] + ' option');
                for (var n = 0; n < sv_data.length; n++) {
                    opts_idx = -1;
                    for (var j = 0; j < opts.length; j++) {
                        if (opts[j].value === sv_data[n]) {
                            opts_idx = j;
                        }
                    }
                    if (opts_idx >= 0) {
                        $('#destination_' + dev_fld[i]).append($(opts[opts_idx]).clone());
                        $(opts[opts_idx]).remove();
                    }
                }
            }
        }
    }
}


function add_dynamic_input(pe, pclass, vdefault)
{
    // We'd like a new one, please.
    pcls = pe.data('for');
    pname = pe.data('id');
    pmax = pe.data('max');
    jdata = JSON.parse(hex2bin(pe.data('json')));

    var last = $("." + pcls + ":last"),
            ourid = last.data('nextid'),
            nextid = ourid + 1;
    var html = "<div class = '" + pcls + " form-group form-inline' data-nextid=" + nextid + ">";
    for (var key in jdata) {
        html_opt = '';
        html_calss = jdata[key]['class'];
        for (var skey in jdata[key]['options']) {
            html_opt += ' ' + skey + '="' + jdata[key]['options'][skey] + '"';
        }
        html += "<input type='text' name='" + pname + "[" + ourid + "][" + key + "]' class='" + html_calss + "' " + html_opt + " value='" + vdefault + "'> " + jdata[key]['nameseparator'] + " ";
    }
    html += "</div>\n";
    if (pmax >= nextid) {
        last.after(html);
    }
}

function del_dynamic_table(pe, pclass, vdefault)
{
    pcls = pe.data('for');
    pname = pe.data('id');

//        pe.preventDefault();
    var rowCount = $('#dp-table-' + pcls + '>tbody >tr').length;
    curRow = $('#' + pcls + '-row-' + pname);
    var curRow = pe.closest('tr');
    if (rowCount > 1) {
        curRow.fadeOut("slow", function () {
            $(this).remove();
        });
    } else {
        curRow.find('input:text').each(function () {
            $(this).val('')
        });
    }
}

function add_dynamic_table(pe, pclass, vdefault)
{
    // We'd like a new one, please.
    pcls = pe.data('for');
    pname = pe.data('id');
    jdata = JSON.parse(hex2bin(pe.data('json')));

    var last = $("." + pcls + ":last"),
            ourid = last.data('nextid'),
            nextid = ourid + 1;
    var html = "<tr class = '" + pcls + "' data-nextid=" + nextid + ">";
    for (var key in jdata) {
        html_opt = '';
        res_ni = pcls + '_' + nextid + '_' + key;
        res_n = pcls + '[' + nextid + '][' + key + ']';
        for (var skey in jdata[key]['options']) {
            html_opt += ' ' + skey + '="' + jdata[key]['options'][skey] + '"';
        }
        var html_rs = '<div class="input-group"> <span class="input-group-addon" id="basep_' + res_ni + '" >' + jdata[key]['display_prefix'] + '</span>';
        var html_re = '<span class="input-group-addon" id="bases_' + res_ni + '">' + jdata[key]['display_sufix'] + '</span></div>';

        html += '<td class="">';
        switch (jdata[key]['type']) {
            case "title":
                break;
            case "label":
                html += '<label ' + html_opt + ' >' + jdata[key]['data'] + '</label>';
                break;
            case "input":
                html += html_rs + '<input type="text" name="' + res_n + '" value="' + '"' + html_opt + '>' + html_re;
                break;
            case "number":
                html += html_rs + '<input type="number" name="' + res_n + '" value="' + '"' + html_opt + '>' + html_re;
                break;
            case "date":
                html += html_rs + '<input type="date" name="' + res_n + '" value="' + '"' + html_opt + '>' + html_re;
                break;
            case "select":
                html += html_rs + '<select name="' + res_n + '" id="' + res_n + '"' + html_opt + ">";
                sel_data = jdata[key]['data'].split(';');
                for (var dkey in sel_data) {
                    html += '<option>' + sel_data[dkey] + '</option>';
                }
                html += '</select>' + html_re;
                break;
        }
        html += '</td>';
    }
    html += '<td><input type="button" id="' + pcls + nextid + '-btn" data-id="' + nextid + '" data-for="' + pcls + '"data-json="' + pe.data('json') + '" class="table-js-add" value="+" />';
    html += '<input type="button" id="' + pcls + nextid + '-btndel" data-id="' + nextid + '" data-for="' + pcls + '" class="table-js-del" value="-" />';

//        html += '<a href="#"  id="routerowdel0"><i class="fa fa-trash"></i></a>';

    html += "</td></tr>\n";

    last.after(html);
}

var theForm = document.editIax;
/* Insert a iax_setting/iax_value pair of text boxes */
(function ($) {
    //Moves selected item(s) from sourceList to destinationList
    $.fn.moveToList = function (sourceList, destinationList) {
        var opts = $(sourceList + ' option:selected');
        if (opts.length == 0) {
            alert("Nothing to move");
        }

        $(destinationList).append($(opts).clone());
    };

    //Moves all items from sourceList to destinationList
    $.fn.moveAllToList = function (sourceList, destinationList) {
        var opts = $(sourceList + ' option');
        if (opts.length == 0) {
            alert("Nothing to move");
        }

        $(destinationList).append($(opts).clone());
    };

    //Moves selected item(s) from sourceList to destinationList and deleting the
    // selected item(s) from the source list
    $.fn.moveToListAndDelete = function (sourceList, destinationList) {
        var opts = $(sourceList + ' option:selected');
        if (opts.length == 0) {
            alert("Nothing to move");
        }

        $(opts).remove();
        $(destinationList).append($(opts).clone());
    };

    //Moves all items from sourceList to destinationList and deleting
    // all items from the source list
    $.fn.moveAllToListAndDelete = function (sourceList, destinationList) {
        var opts = $(sourceList + ' option');
        if (opts.length == 0) {
            alert("Nothing to move");
        }

        $(opts).remove();
        $(destinationList).append($(opts).clone());
    };

    //Removes selected item(s) from list
    $.fn.removeSelected = function (list) {
        var opts = $(list + ' option:selected');
        if (opts.length == 0) {
            alert("Nothing to remove");
        }

        $(opts).remove();
    };

    //Moves selected item(s) up or down in a list
    $.fn.moveUpDown = function (list, btnUp, btnDown) {
        var opts = $(list + ' option:selected');
        if (opts.length == 0) {
            alert("Nothing to move");
        }

        if (btnUp) {
            opts.first().prev().before(opts);
        } else if (btnDown) {
            opts.last().next().after(opts);
        }
    };
})(jQuery);

/*
 String.prototype.hex2bin = function()
 {
 var i = 0, len = this.length, result = "";

 //Converting the hex string into an escaped string, so if the hex string is "a2b320", it will become "%a2%b3%20"
 for(; i < len; i+=2)
 result += '%' + this.substr(i, 2);

 return unescape(result);
 }
 */
function bs_page_reload()
{
    window.location.reload(false);
}
function bs_alert(data, status, reload)
{

    if (document.getElementById('hwalert') === null) {
        if (Array.isArray(data)) {
            data.forEach(function (entry) {
                alert(entry);
            });
        } else {
            alert(data);
        }
        return true; // Old style
    } else {
        var modal = $("#hwalert");
        if (typeof status != "undefined") {
            if (status === true) {
                modal.find('.modal-title').text('Operation result');
            } else {
                 modal.find('.modal-title').text('Error operation ');
            }
        } else {
            modal.find('.modal-title').text('Operation result');
        }
//        var modal2 = modal.find('.modal-title');
//        console.log(modal2);
//        modal.find('.modal-body').text(data);
        var modal2 = modal.find('.modal-body');
        var msg_html = '';
        if (Array.isArray(data)) {
            data.forEach(function (entry) {
                msg_html = msg_html + '<p>'+ entry + '</p>';
            });
        } else {
            msg_html = data;
        }
        modal2[0].innerHTML = msg_html;
        if (typeof reload != "undefined") {
            if (reload === true) {
                $("#hwalert").on('hidden.bs.modal', bs_page_reload);
            }
        }
        modal.modal('show');
        return false;
    }

}
function hex2bin(hex)
{
    var bytes = [], str;

    for (var i = 0; i < hex.length - 1; i += 2) {
        bytes.push(parseInt(hex.substr(i, 2), 16));
    }

    return String.fromCharCode.apply(String, bytes);
}

function sleep(milliseconds)
{
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds) {
            break;
        }
    }
}
