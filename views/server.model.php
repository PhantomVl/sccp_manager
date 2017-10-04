<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>

<div class="fpbx-container container-fluid">
    <div class="row">
        <div class="col-sm-12">

            <div class="display no-border">
                <div id="toolbar-model">
                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target=".add_new_model"><i class="fa fa-bolt"></i> <?php echo _("Add model"); ?></button>
                    <button data-id="model_disabled" class="btn btn-danger sccp_update btn-tab-select" data-type="sccp_model" disabled data-section="all">
                        <i class="glyphicon glyphicon-remove"></i> <span><?php echo _('Disabled') ?></span>
                    </button>
                    <button data-id="model_enabled" class="btn btn-danger sccp_update btn-tab-select" data-type="sccp_model" disabled data-section="all">
                        <i class="glyphicon glyphicon-active"></i> <span><?php echo _('Enabled') ?></span>
                    </button>
                    <div class="btn-group">
                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                            <BtnCaption class="dropdown_capture"><?php echo _('Show Enabled') ?></BtnCaption>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropitem" data-id="enabled" tabindex="-1" href="#"><span><?php echo _('Show Enabled') ?></span></a></li>
                            <li><a class="dropitem" data-id="extension" tabindex="-1" href="#"><span><?php echo _('Expansion Module')?></span></a></li>                            
                            <li><a class="dropitem" data-id="all" tabindex="-1" href="#"><span><?php echo _('Show All') ?></span></a></li>                            
                        </ul>
                    </div>
                </div>
                <table data-cookie="true" data-row-style="SetRowColor" data-cookie-id-table="sccp_model-all" data-url="ajax.php?module=sccp_manager&amp;command=getDeviceModel&amp;type=enabled" data-cache="false" data-show-refresh="true" data-toolbar="#toolbar-model" data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-toggle="table" data-pagination="true" data-search="true" class="table table-condensed" id="table-models" data-id="model" data-unique-id="model">
                   <thead>
                        <tr>
                            <th data-checkbox="true"></th>
                            <th data-sortable="false" data-formatter="StatusIconFormatter" data-field="enabled"><?php echo _('Eabled');?></th>
                            <th data-sortable="true" data-field="model"><?php echo _('Device Model');?></th>
                            <th data-sortable="true" data-field="vendor"><?php echo _('Vendor');?></th>
                            <th data-sortable="false" data-formatter="DisplayDnsFormatter" data-field="dns"><?php echo _('Expansion Module');?></th>
                            <th data-sortable="false" data-field="buttons"><?php echo _('Buttons');?></th>
                            <th data-sortable="false" data-formatter="SetColColorFirm" data-field="loadimage"><?php echo _('Loadimage');?></th>
                            <th data-sortable="false" data-field="loadinformationid"><?php echo _('Loadinformation ID');?></th>
                            <th data-sortable="false" data-formatter="SetColColorTempl" data-field="nametemplate"><?php echo _('Model template');?></th>
                            <th data-field="actions" data-formatter="DispayActionsModelFormatter"><?php echo _('Actions');?></th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>

<!-- Begin Form Input New  -->
<div class="modal fade add_new_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel"><?php echo _('Modal title');?></h4>
            </div>
            <div class="modal-body">
                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="new_model"><?php echo _('Device Model');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="new_model"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="new_model" name="new_model" value="79XX">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="new_model-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>

                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="new_vendor"><?php echo _('Vendor name');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="new_vendor"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="new_vendor" name="new_vendor" value="CISCO">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="new_vendor-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>

                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="new_dns"><?php echo _('Expansion Module');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="new_dns"></i>
                    </div><div class="col-md-9">
                	<select name="new_dns" id="new_dns">
                            <option value="1">Phone - no sidecars.</option>
                            <option value="2">Phone - one sidecar.</option>
                            <option value="3">Phone - two sidecars.</option>
                            <option value="0" selected='selected'>Sidecar</option>
                        </select>
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="new_dns-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>


                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="new_buttons"><?php echo _('Model Line Buttons');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="new_buttons"></i>
                    </div><div class="col-md-9">
                        <input type="number" min="1" min="96" class="form-control" id="new_buttons" name="new_buttons" value="1">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="new_buttons-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>
                
                
                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="new_loadimage"><?php echo _('Load Image');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="new_loadimage"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="new_loadimage" name="new_loadimage" value="">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="new_loadimage-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>

                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="new_loadinformationid"><?php echo _('Load Information ID');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="new_loadinformationid"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="new_loadinformationid" name="new_loadinformationid" value="">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="new_loadinformationid-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>
                
                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="new_nametemplate"><?php echo _('Model template XML');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="new_nametemplate"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="new_nametemplate" name="new_nametemplate" value="">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="new_nametemplate-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Close');?></button>
                <button type="button" class="btn btn-primary sccp_update" data-id="model_add" id="add_new_model" data-dismiss="modal"><?php echo _('Add New model whithout Enabled');?></button>
            </div>            
        </div>
    </div>
</div>



<div class="modal fade" id="edit_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="gridSystemModalLabel"><?php echo _('Modal title');?></h4>
            </div>
            <div class="modal-body">

                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="editd_model"><?php echo _('Device Model');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="editd_model"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="editd_model" name="editd_model" value="79XX" disabled>
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="editd_model-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>

                
                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="editd_loadimage"><?php echo _('Load Image');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="edit_devimage"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="editd_loadimage" name="editd_loadimage" value="">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="editd_loadimage-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>

                <div class="element-container"><div class="row"> <div class="form-group"><div class="col-md-3">
                        <label class="control-label" for="editd_nametemplate"><?php echo _('Model template XML');?></label>
                        <i class="fa fa-question-circle fpbx-help-icon" data-for="editd_nametemplate"></i>
                    </div><div class="col-md-9">
                        <input type="text" class="form-control" id="editd_nametemplate" name="editd_nametemplate" value="">
                    </div> </div></div>
                    <div class="row"><div class="col-md-12">
                        <span id="editd_nametemplate-help" class="help-block fpbx-help-block">Help.</span>
                </div></div></div>

                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Close');?></button>
                <button type="button" class="btn btn-primary sccp_update" data-id="model_applay" data-dismiss="modal"><?php echo _('Applay');?></button>
            </div>            
        </div>
    </div>
</div>



<script>
    function StatusIconFormatter(value, row) {
		return (value === '1') ? '<i class="fa fa-check-square-o" style="color:green" title="<?php echo _("Device is enabled")?>"></i>' : '<i class="fa fa-square-o" title="<?php echo _("Device is disabled")?>"></i>';
	}
    function DisplayDnsFormatter(value, row, index) {
        var exp_model = ['Expansion Module', 'No awalable', 'One ExpModule', 'Tow ExpModule'];
        return  exp_model[value];
    }

//    function DispayInputFormatter(value, row, index) {
//        return  (value == null) ?  '<input class="tabl-edit form-control" name="' + row['model'] + '_template" type="text" value="">'  : '<input class="tabl-edit form-control" name="' + row['model'] + '_template" type="text" value="' + value + '">';
//    }
    
    function DispayActionsModelFormatter(value, row, index) {
        var exp_model = '';
//        exp_model += '<a href="#edit_model"   class="btn btn-info"   onclick="load_model(this, &quot;'+row['model']+'&quot;)" data-toggle="modal"><i class="fa fa-pencil"></i></a>';
        exp_model += '<a href="#edit_model"   onclick="load_model(this, &quot;'+row['model']+'&quot;)" data-toggle="modal"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;';
        exp_model += '</a> &nbsp;<a class="btn-item-delete" data-for="model" data-id="' + row['model'] + '"><i class="fa fa-trash"></i></a>';
        return  exp_model;
    }

    function SetColColorFirm(value, row, index) {
        if (row['validate'].split(';')[0] === 'no') {
            return  'No found '+ value;
        }
        return value;
    }
    function SetColColorTempl(value, row, index) {
        if (row['validate'].split(';')[1] === 'no') {
            return  'No found '  + value ;
        }
        return value;

    }
    
    function SetRowColor(row, index) {
        var tclass = "active";
        if (row['enabled'] === 1) {
            tclass = (index % 2 === 0) ? "info" : "info";
        }    
        if ((row['validate'] === 'yes;yes') || (row['validate'] === 'yes;-')) {
//            tclass = (row['enabled'] === '1') ?  "danger" : "warning";
        } else {
            tclass = (row['enabled'] === '1') ?  "danger" : "warning";
        }
        return {classes: tclass};
    }
    
    function load_model(elmnt,clr) {
//        $("#edit_devmodel").text(clr);
        var drow = $("#table-models").bootstrapTable('getRowByUniqueId',clr);
        if (drow == null) {
            alert(drow);
        } else {
            document.getElementById("editd_model").value = clr;
            document.getElementById("editd_loadimage").value = drow['loadimage'];
            document.getElementById("editd_nametemplate").value = drow['nametemplate'];
        }
    }
</script>
