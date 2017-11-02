<?php
/**
 * 
 */
namespace FreePBX\modules\Sccp_manager;
class extconfigs {
        public function __construct() {

        }

        public function info() {
           return Array('Version' => '13.0.2',
                        'about' =>'Default Setings and Enums ver: 13.0.2');
        }
        
        public function getextConfig($id = '', $index = '') {
            switch ($id) {
                case 'keyset':
                    $result =  $this->keysetdefault;
                    break;
                case 'sccp_lang':
                    $result =  $this->cisco_language;
                    break;
                case 'sccpDefaults':
                    $result =  $this->sccpDefaults;
                    break;
                case 'sccp_timezone':
                    $result =  $this->cisco_timezone;
                    break;
                case 'cisco_time':
                    $result = array();
                    foreach ($this->cisco_timezone as $key => $value) {
                        $result[] = array('id'=> ($value['offset']/60) ,'val'=>$key.((empty($value['daylight']))? '': '/'.$value['daylight']));
                    }
                    break;
                    
                case 'cisco_timezone':
                    $result = array();
                    foreach ($this->cisco_timezone as $key => $value) {
                        $result[] = array('id'=> $key ,'val'=>$key.((empty($value['daylight']))? '': '/'.$value['daylight']));
//                        $result[$key] =$key.((empty($value['daylight']))? '': '/'.$value['daylight']);
                    }
                    break;
                default:
                    return array('noId');
                    break;
            }
            if (empty($index)) {
                return $result;
            } else {
                if (isset($result[$index])) {
                    return $result[$index];
                } else {
                    return array();
                }
            }
        }

        private $sccpDefaults = array(
            "servername" => 'VPBXSCCP',
            "bindaddr" => '0.0.0.0', "port" => '2000',			# chan_sccp also supports ipv6
                                                                        # bindaddr = "::" will support ipv6 and ipv4 at the same time
            "deny" => '0.0.0.0/0.0.0.0',
            "permit" => '0.0.0.0/0.0.0.0',					# defaults to 'internal' which means:
                                                                        # permit:127.0.0.0/255.0.0.0,permit:10.0.0.0/255.0.0.0,permit:172.0.0.0/255.224.0.0,permit:192.168.0.0/255.255.0.0"
            "dateformat" => 'D.M.Y',					# This is the german default format. Should be "D/M/Y" or "D/M/YA" instead
            "disallow" => 'all', "allow" => 'alaw;ulaw',
            "devicetable" => 'sccpdevice',
            "hotline_enabled" => 'no',
            "hotline_context" => 'default',
            "hotline_extension" => '*60',
            "hotline_label" => 'hotline',
            "linetable" => 'sccpline',
            "tftp_path" => '/tftpboot'
        );
                
        private $keysetdefault = array('onhook' => 'redial,newcall,cfwdall,dnd,pickup,gpickup,private',
            'connected' => 'hold,endcall,park,vidmode,select,cfwdall,cfwdbusy,idivert',
            'onhold' => 'resume,newcall,endcall,transfer,conflist,select,dirtrfr,idivert,meetme',
            'ringin' => 'answer,endcall,transvm,idivert',
            'offhook' => 'redial,endcall,private,cfwdall,cfwdbusy,pickup,gpickup,meetme,barge',
            'conntrans' => 'hold,endcall,transfer,conf,park,select,dirtrfr,vidmode,meetme,cfwdall,cfwdbusy',
            'digitsfoll' => 'back,endcall,dial',
            'connconf' => 'conflist,newcall,endcall,hold,vidmode',
            'ringout' => 'empty,endcall,transfer,cfwdall,idivert',
            'offhookfeat' => 'redial,endcall',
            'onhint' => 'redial,newcall,pickup,gpickup,barge',
            'onstealable' => 'redial,newcall,cfwdall,pickup,gpickup,dnd,intrcpt',
            'holdconf' => 'resume,newcall,endcall,join',
            'uriaction' => 'default');
//   Cisco  Language Code / Directory  
        
        private $cisco_language = array('ar_SA' => array('code' => 'ar', 'language' => 'Arabic', 'locale' => 'Arabic_Saudi_Arabia'),
            'bg_BG' => array('code' => 'bg', 'language' => 'Bulgarian', 'locale' => 'Bulgarian_Bulgaria'),
            'cz_CZ' => array('code' => 'cz', 'language' => 'Czech', 'locale' => 'Czech_Czech_Republic'),
            'da_DK' => array('code' => 'da', 'language' => 'Danish', 'locale' => 'Danish_Denmark'),
            'de_DE' => array('code' => 'de', 'language' => 'German', 'locale' => 'German_Germany'),
            'el_GR' => array('code' => 'el', 'language' => 'Greek', 'locale' => 'Greek_Greece'),
            'en_AU' => array('code' => 'en', 'language' => 'English', 'locale' => 'AU_English_United_States'),
            'en_GB' => array('code' => 'en', 'language' => 'English', 'locale' => 'English_United_Kingdom'),
            'en_US' => array('code' => 'en', 'language' => 'English', 'locale' => 'English_United_States'),
            'es_ES' => array('code' => 'es', 'language' => 'Spanish', 'locale' => 'Spanish_Spain'),
            'et_EE' => array('code' => 'et', 'language' => 'Estonian', 'locale' => 'Estonian_Estonia'),
            'fi_FI' => array('code' => 'fi', 'language' => 'Finnish', 'locale' => 'Finnish_Finland'),
            'fr_CA' => array('code' => 'fr', 'language' => 'French', 'locale' => 'French_Canada'),
            'fr_FR' => array('code' => 'fr', 'language' => 'French', 'locale' => 'French_France'),
            'he_IL' => array('code' => 'he', 'language' => 'Hebrew', 'locale' => 'Hebrew_Israel'),
            'hr_HR' => array('code' => 'hr', 'language' => 'Croatian', 'locale' => 'Croatian_Croatia'),
            'hu_HU' => array('code' => 'hu', 'language' => 'Hungarian', 'locale' => 'Hungarian_Hungary'),
            'it_IT' => array('code' => 'it', 'language' => 'Italian', 'locale' => 'Italian_Italy'),
            'ja_JP' => array('code' => 'ja', 'language' => 'Japanese', 'locale' => 'Japanese_Japan'),
            'ko_KO' => array('code' => 'ko', 'language' => 'Korean', 'locale' => 'Korean_Korea_Republic'),
            'lt_LT' => array('code' => 'lt', 'language' => 'Lithuanian', 'locale' => 'Lithuanian_Lithuania'),
            'lv_LV' => array('code' => 'lv', 'language' => 'Latvian', 'locale' => 'Latvian_Latvia'),
            'nl_NL' => array('code' => 'nl', 'language' => 'Dutch', 'locale' => 'Dutch_Netherlands'),
            'no_NO' => array('code' => 'no', 'language' => 'Norwegian', 'locale' => 'Norwegian_Norway'),
            'pl_PL' => array('code' => 'pl', 'language' => 'Polish', 'locale' => 'Polish_Poland'),
            'pt_BR' => array('code' => 'pt', 'language' => 'Portuguese', 'locale' => 'Portuguese_Brazil'),
            'pt_PT' => array('code' => 'pt', 'language' => 'Portuguese', 'locale' => 'Portuguese_Portugal'),
            'ro_RO' => array('code' => 'ro', 'language' => 'Romanian', 'locale' => 'Romanian_Romania'),
            'ru_RU' => array('code' => 'ru', 'language' => 'Russian', 'locale' => 'Russian_Russian_Federation'),
            'sk_SK' => array('code' => 'sk', 'language' => 'Slovakian', 'locale' => 'Slovak_Slovakia'),
            'sl_SL' => array('code' => 'sl', 'language' => 'Slovenian', 'locale' => 'Slovenian_Slovenia'),
            'sr_ME' => array('code' => 'sr', 'language' => 'Serbian', 'locale' => 'Serbian_Republic_of_Montenegro'),
            'sr_RS' => array('code' => 'rs', 'language' => 'Serbian', 'locale' => 'Serbian_Republic_of_Serbia'),
            'sv_SE' => array('code' => 'sv', 'language' => 'Swedish', 'locale' => 'Swedish_Sweden'),
            'th_TH' => array('code' => 'th', 'language' => 'Thailand', 'locale' => 'Thai_Thailand'),
            'tr_TR' => array('code' => 'tr', 'language' => 'Turkish', 'locale' => 'Turkish_Turkey'),
            'zh_CN' => array('code' => 'cn', 'language' => 'Chinese', 'locale' => 'Chinese_China'),
            'zh_TW' => array('code' => 'zh', 'language' => 'Chinese', 'locale' => 'Chinese_Taiwan')
        );

        private $cisco_timezone = array(
            'Dateline'             => array('offset' => '-720', 'daylight' => ''),
            'Samoa'                => array('offset' => '-660', 'daylight' => ''),
            'Hawaiian'             => array('offset' => '-600', 'daylight' => ''),
            'Alaskan'              => array('offset' => '-540', 'daylight' => 'Daylight'),
            'Pacific'              => array('offset' => '-480', 'daylight' => 'Daylight'),
            'Mountain'             => array('offset' => '-420', 'daylight' => 'Daylight'),
            'US Mountain'          => array('offset' => '-420', 'daylight' => ''),
            'Central'              => array('offset' => '-360', 'daylight' => 'Daylight'),
            'Mexico'               => array('offset' => '-360', 'daylight' => 'Daylight'),
            'Canada Central'       => array('offset' => '-360', 'daylight' => ''),
            'SA Pacific'           => array('offset' => '-300', 'daylight' => ''),
            'Eastern'              => array('offset' => '-300', 'daylight' => 'Daylight'),
            'US Eastern'           => array('offset' => '-300', 'daylight' => ''),
            'Atlantic'             => array('offset' => '-240', 'daylight' => 'Daylight'),
            'SA Western'           => array('offset' => '-240', 'daylight' => ''),
            'Pacific SA'           => array('offset' => '-240', 'daylight' => ''),
            'Newfoundland'         => array('offset' => '-210', 'daylight' => 'Daylight'),
            'E. South America'     => array('offset' => '-180', 'daylight' => 'Daylight'),
            'SA Eastern'           => array('offset' => '-180', 'daylight' => ''),
            'Pacific SA'           => array('offset' => '-180', 'daylight' => 'Daylight'),
            'Mid-Atlantic'         => array('offset' => '-120', 'daylight' => 'Daylight'),
            'Azores'               => array('offset' => '-060', 'daylight' => 'Daylight'),
            'GMT'                  => array('offset' => '00',  'daylight' => 'Daylight'),
            'Greenwich'            => array('offset' => '00',  'daylight' => ''),
            'W. Europe'            => array('offset' => '60',   'daylight' => 'Daylight'),
            'GTB'                  => array('offset' => '60',   'daylight' => 'Daylight'),
            'Egypt'                => array('offset' => '60',   'daylight' => 'Daylight'),
            'E. Europe'            => array('offset' => '60',   'daylight' => 'Daylight'),
            'Romance'              => array('offset' => '120',  'daylight' => 'Daylight'),
            'Central Europe'       => array('offset' => '120',  'daylight' => 'Daylight'),
            'South Africa'         => array('offset' => '120',  'daylight' => ''),
            'Jerusalem'            => array('offset' => '120',  'daylight' => 'Daylight'),
            'Saudi Arabia'         => array('offset' => '180',  'daylight' => ''),
/*              Russion  Regions                                                                 */            
            'Russian/Kaliningrad'  => array('offset' => '120', 'daylight' => ''),
            'Russian/Moscow'       => array('offset' => '180', 'daylight' => ''),
            'Russian/St.Peterburg' => array('offset' => '180', 'daylight' => ''),
            'Russian/Samara'       => array('offset' => '240', 'daylight' => ''),
            'Russian/Novosibirsk'  => array('offset' => '300', 'daylight' => ''),
            'Russian/Ekaterinburg' => array('offset' => '300', 'daylight' => ''),
            'Russian/Irkutsk'      => array('offset' => '480', 'daylight' => ''),
            'Russian/Yakutsk'      => array('offset' => '540', 'daylight' => ''),
            'Russian/Khabarovsk'   => array('offset' => '600', 'daylight' => ''),
            'Russian/Vladivostok'  => array('offset' => '600', 'daylight' => ''),
            'Russian/Sakhalin'     => array('offset' => '660', 'daylight' => ''),
            'Russian/Magadan'      => array('offset' => '660', 'daylight' => ''),
            'Russian/Kamchatka'    => array('offset' => '720', 'daylight' => ''),
/*              EnD - Russion  Regions                                                             */            
            
            'Iran'                 => array('offset' => '210',  'daylight' => 'Daylight'),
            'Caucasus'             => array('offset' => '240', 'daylight' => 'Daylight'),
            'Arabian'              => array('offset' => '240', 'daylight' => ''),
            'Afghanistan'          => array('offset' => '270', 'daylight' => ''),
            'West Asia'            => array('offset' => '300', 'daylight' => ''),
            'India'                => array('offset' => '330', 'daylight' => ''),
            'Central Asia'         => array('offset' => '360', 'daylight' => ''),
            'SE Asia'              => array('offset' => '420', 'daylight' => ''),
            'China'                => array('offset' => '480', 'daylight' => ''),
            'Taipei'               => array('offset' => '480', 'daylight' => ''),
            'Tokyo'                => array('offset' => '540', 'daylight' => ''),
            'Cen. Australia'       => array('offset' => '570', 'daylight' => 'Daylight'),
            'AUS Central'          => array('offset' => '570', 'daylight' => ''),
            'E. Australia'         => array('offset' => '600', 'daylight' => ''),
            'AUS Eastern'          => array('offset' => '600', 'daylight' => 'Daylight'),
            'West Pacific'         => array('offset' => '600', 'daylight' => ''),
            'Tasmania'             => array('offset' => '600', 'daylight' => 'Daylight'),
            'Central Pacific'      => array('offset' => '660', 'daylight' => ''),
            'Fiji'                 => array('offset' => '720', 'daylight' => ''),
            'New Zealand'          => array('offset' => '720', 'daylight' => 'Daylight')
            );
        
}