<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// vim: set ai ts=4 sw=4 ft=phtml:
//   print_r($this->sccp_conf_init);
//   print_r($this->sccpvalues);
//print_r('<br><br>');

$driver = $this->FreePBX->Core->getAllDriversInfo();
$core = $this->srvinterface->getSCCPVersion();
$info = array();
$info['srvinterface'] = $this->srvinterface->info();
$info['extconfigs'] = $this->extconfigs->info();
$info['dbinterface'] = $this->dbinterface->info();
$info['XML'] = $this->xmlinterface->info();
$info['sccp_class'] = $driver['sccp'];
$info['Core_sccp'] = array('Version' => $core['Version'],  'about'=> 'Sccp ver.'. $core['Version'].' r'.$core['vCode']. ' Revision :'. $core['RevisionNum']. ' Hash :'. $core['RevisionHash']);
$info['Asterisk'] = array('Version' => FreePBX::Config()->get('ASTVERSION'),  'about'=> 'Asterisk.');
if (!empty($this->sccpvalues['SccpDBmodel'])) {
    $info['DB Model'] = array('Version' => $this->sccpvalues['SccpDBmodel']['data'],  'about'=> 'SCCP DB Configure');
}
if (!empty($this->sccpvalues['tftp_rewrite'])) {
    if ($this->sccpvalues['tftp_rewrite']['data'] == 'pro') {
        $info['Provision_SCCP'] = array('Version' => 'base',  'about'=> 'Provision Sccp enabled');
    } else {
        $info['TFTP_Rewrite'] = array('Version' => 'base',  'about'=> 'Rewrite Supported');
    }
}
$info['Сompatible'] = array('Version' => $this->srvinterface->get_compatible_sccp(),  'about'=> 'Ok');
if (!empty($this->sccpvalues['SccpDBmodel'])) {
    if ($this->srvinterface->get_compatible_sccp()> $this->sccpvalues['SccpDBmodel']['data']){
        $info['Сompatible']['about'] = '<div class="alert signature alert-danger"> Reinstall SCCP manager required</div>';
    }
}
/*
print_r("<br> Request:<br><pre>");
 $json = '';
 print_r("<br>");
 print_r("<br>");
 print_r("<br>");
 print_r("DIRECT START");
 print_r("<br>");
 print_r($this->srvinterface->t_get_meta_data());
 
print("</pre>");
*/
//   $lang_arr =  $this->extconfigs->getextConfig('sccp_lang','sk_SK');    
//   print_r('<br>');
//   print_r(timezone_identifiers_list());
//   print_r('<br>');

//print_r($this->dbinterface->info());

if (!empty($this->class_error)) {
    ?>    
    <div class="fpbx-container container-fluid">
        <div class="row">
            <div class="container">
                <h2 style="border:2px solid Tomato;color:Tomato;" >Sccp Manager Error</h2>
                <div class="table-responsive">          
                    <br> There are Error in the SCCP Module:<br><pre>
                        <?php print_r($this->class_error); ?>
                    </pre>
                    <br> Correct these problems before continuing to work. <br>
                    <br><h3 style="border:2px solid Tomato;color:Green;" > Open 'SCCP Conectivity -> Server Config' to change global settings</h3> <br>
                </div>
            </div>
        </div>
    </div>
<br>
<?php  }  ?>
<div class="fpbx-container container-fluid">
    <div class="row">
        <div class="container">
            <h2>Sccp Manager Info </h2>
            <div class="table-responsive">          
                <table class="table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Version</th>
                            <th>Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($info as $key => $value) {
                            echo '<tr><td>' . $key . '</td><td>' . $value['Version'] . '</td><td>' . $value['about'] . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php  echo $this->ShowGroup('sccp_info',0); ?>

