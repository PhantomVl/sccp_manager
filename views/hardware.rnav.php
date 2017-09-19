<div id="toolbar-sccpbnav">
<a href="config.php?display=sccp_phone#sccpdevice" class = "btn btn-default"><i class="fa fa-list"></i>&nbsp;<?php echo _("List Device")?></a>
<a href="config.php?display=sccp_phone&amp;tech_hardware=cisco" class = "btn btn-default"><i class="fa fa-plus"></i>&nbsp;<?php echo _("Add Device")?></a>
</div>
<table id="sccpnavgrid"
 		data-search="true"
		data-toolbar="#toolbar-sccpnav"
		data-url="ajax.php?module=sccp_manager&amp;command=getPhoneGrid&amp;type=sccp"
		data-cache="false"
		data-toggle="table" 
		class="table">
	<thead>
			<tr>
                            <th data-sortable="true" data-field="mac"><?php echo _('SEP ID') ?></th>
                            <th data-sortable="true" data-field="description"><?php echo _('Descriptions') ?></th>
		</tr>
	</thead>
</table>
<script type="text/javascript">
	$("#sccpnavgrid").on('click-row.bs.table',function(e,row,elem){
		window.location = '?display=sccp_phone&tech_hardware=cisco&id='+row['mac'];
	})
</script>
