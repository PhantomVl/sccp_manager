<?php

/**
 * 
 */

namespace FreePBX\modules\Sccp_manager;

class extconfigs {

    public function __construct($parent_class = null) {
        $this->paren_class = $parent_class;
    }

    public function info() {
        $Ver = '13.0.3';
        return Array('Version' => $Ver,
            'about' => 'Default Setings and Enums ver: ' . $Ver);
    }

    public function getextConfig($id = '', $index = '') {
        switch ($id) {
            case 'keyset':
                $result = $this->keysetdefault;
                break;
            case 'sccp_lang':
                $result = $this->cisco_language;
                break;
            case 'sccpDefaults':
                $result = $this->sccpDefaults;
                break;
            case 'sccp_timezone':
                $result = $this->cisco_timezone;
                break;
            case 'cisco_timezone':
                // could just use the sccp_timezone above
                $result = array();
                foreach ($this->cisco_timezone as $key => $value) {
                    $result[] = array('id' => $key, 'val' => $key);
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
        "bindaddr" => '0.0.0.0', "port" => '2000', # chan_sccp also supports ipv6
        # bindaddr = "::" will support ipv6 and ipv4 at the same time
        "deny" => '0.0.0.0/0.0.0.0',
        "permit" => '0.0.0.0/0.0.0.0', # !TODO!: please change this to 'internal' which would mean:
        # permit:127.0.0.0/255.0.0.0,permit:10.0.0.0/255.0.0.0,permit:172.0.0.0/255.224.0.0,permit:192.168.0.0/255.255.0.0"
        "dateformat" => 'D.M.Y', # This is the german default format. Should be "D/M/Y" or "D/M/YA" instead
        "disallow" => 'all', "allow" => 'alaw;ulaw',
        "hotline_enabled" => 'off',
        "hotline_context" => 'default', # !TODO!: Should this not be from-internal on FreePBX ?
        "hotline_extension" => '*60', # !TODO!: Is this a good default extension to dial for hotline ?
        "hotline_label" => 'hotline',
        "devicetable" => 'sccpdevice',
        "linetable" => 'sccpline',
        "tftp_path" => '/tftpboot'
    );
    private $keysetdefault = array('onhook' => 'redial,newcall,cfwdall,cfwdbusy,cfwdnoanswer,pickup,gpickup,dnd,private',
        'connected' => 'hold,endcall,park,vidmode,select,cfwdall,cfwdbusy,idivert,monitor',
        'onhold' => 'resume,newcall,endcall,transfer,conflist,select,dirtrfr,idivert,meetme',
        'ringin' => 'answer,endcall,transvm,idivert',
        'offhook' => 'redial,endcall,private,cfwdall,cfwdbusy,cfwdnoanswer,pickup,gpickup,meetme,barg',
        'conntrans' => 'hold,endcall,transfer,conf,park,select,dirtrfr,monitor,vidmode,meetme,cfwdal',
        'digitsfoll' => 'back,endcall,dial',
        'connconf' => 'conflist,newcall,endcall,hold,vidmode,monitor',
        'ringout' => 'empty,endcall,transfer',
        'offhookfeat' => 'resume,newcall,endcall',
        'onhint' => 'redial,newcall,pickup,gpickup',
        'onstealable' => 'redial,newcall,barge,intrcpt,cfwdall,pickup,gpickup,dnd',
        'holdconf' => 'resume,newcall,endcall,join',
        'uriaction' => 'default');
//   Cisco  Language Code / Directory  
//
    private $cisco_language = array('ar_SA' => array('code' => 'ar', 'language' => 'Arabic', 'locale' => 'Arabic_Saudi_Arabia', 'codepage' => 'ISO8859-1'),
        'bg_BG' => array('code' => 'bg', 'language' => 'Bulgarian', 'locale' => 'Bulgarian_Bulgaria', 'codepage' => 'ISO8859-1'),
        'cz_CZ' => array('code' => 'cz', 'language' => 'Czech', 'locale' => 'Czech_Czech_Republic', 'codepage' => 'ISO8859-1'),
        'da_DK' => array('code' => 'da', 'language' => 'Danish', 'locale' => 'Danish_Denmark', 'codepage' => 'ISO8859-1'),
        'de_DE' => array('code' => 'de', 'language' => 'German', 'locale' => 'German_Germany', 'codepage' => 'ISO8859-1'),
        'el_GR' => array('code' => 'el', 'language' => 'Greek', 'locale' => 'Greek_Greece', 'codepage' => 'ISO8859-1'),
        'en_AU' => array('code' => 'en', 'language' => 'English', 'locale' => 'AU_English_United_States', 'codepage' => 'ISO8859-1'),
        'en_GB' => array('code' => 'en', 'language' => 'English', 'locale' => 'English_United_Kingdom', 'codepage' => 'ISO8859-1'),
        'en_US' => array('code' => 'en', 'language' => 'English', 'locale' => 'English_United_States', 'codepage' => 'ISO8859-1'),
        'es_ES' => array('code' => 'es', 'language' => 'Spanish', 'locale' => 'Spanish_Spain', 'codepage' => 'ISO8859-1'),
        'et_EE' => array('code' => 'et', 'language' => 'Estonian', 'locale' => 'Estonian_Estonia', 'codepage' => 'ISO8859-1'),
        'fi_FI' => array('code' => 'fi', 'language' => 'Finnish', 'locale' => 'Finnish_Finland', 'codepage' => 'ISO8859-1'),
        'fr_CA' => array('code' => 'fr', 'language' => 'French', 'locale' => 'French_Canada', 'codepage' => 'ISO8859-1'),
        'fr_FR' => array('code' => 'fr', 'language' => 'French', 'locale' => 'French_France', 'codepage' => 'ISO8859-1'),
        'he_IL' => array('code' => 'he', 'language' => 'Hebrew', 'locale' => 'Hebrew_Israel', 'codepage' => 'ISO8859-1'),
        'hr_HR' => array('code' => 'hr', 'language' => 'Croatian', 'locale' => 'Croatian_Croatia', 'codepage' => 'ISO8859-1'),
        'hu_HU' => array('code' => 'hu', 'language' => 'Hungarian', 'locale' => 'Hungarian_Hungary', 'codepage' => 'ISO8859-1'),
        'it_IT' => array('code' => 'it', 'language' => 'Italian', 'locale' => 'Italian_Italy', 'codepage' => 'ISO8859-1'),
        'ja_JP' => array('code' => 'ja', 'language' => 'Japanese', 'locale' => 'Japanese_Japan', 'codepage' => 'ISO8859-1'),
        'ko_KO' => array('code' => 'ko', 'language' => 'Korean', 'locale' => 'Korean_Korea_Republic', 'codepage' => 'ISO8859-1'),
        'lt_LT' => array('code' => 'lt', 'language' => 'Lithuanian', 'locale' => 'Lithuanian_Lithuania', 'codepage' => 'ISO8859-1'),
        'lv_LV' => array('code' => 'lv', 'language' => 'Latvian', 'locale' => 'Latvian_Latvia', 'codepage' => 'ISO8859-1'),
        'nl_NL' => array('code' => 'nl', 'language' => 'Dutch', 'locale' => 'Dutch_Netherlands', 'codepage' => 'ISO8859-1'),
        'no_NO' => array('code' => 'no', 'language' => 'Norwegian', 'locale' => 'Norwegian_Norway', 'codepage' => 'ISO8859-1'),
        'pl_PL' => array('code' => 'pl', 'language' => 'Polish', 'locale' => 'Polish_Poland', 'codepage' => 'ISO8859-1'),
        'pt_BR' => array('code' => 'pt', 'language' => 'Portuguese', 'locale' => 'Portuguese_Brazil', 'codepage' => 'ISO8859-1'),
        'pt_PT' => array('code' => 'pt', 'language' => 'Portuguese', 'locale' => 'Portuguese_Portugal', 'codepage' => 'ISO8859-1'),
        'ro_RO' => array('code' => 'ro', 'language' => 'Romanian', 'locale' => 'Romanian_Romania', 'codepage' => 'ISO8859-1'),
        'ru_RU' => array('code' => 'ru', 'language' => 'Russian', 'locale' => 'Russian_Russian_Federation', 'codepage' => 'CP1251'),
        'sk_SK' => array('code' => 'sk', 'language' => 'Slovakian', 'locale' => 'Slovak_Slovakia', 'codepage' => 'ISO8859-1'),
        'sl_SL' => array('code' => 'sl', 'language' => 'Slovenian', 'locale' => 'Slovenian_Slovenia', 'codepage' => 'ISO8859-1'),
        'sr_ME' => array('code' => 'sr', 'language' => 'Serbian', 'locale' => 'Serbian_Republic_of_Montenegro', 'codepage' => 'ISO8859-1'),
        'sr_RS' => array('code' => 'rs', 'language' => 'Serbian', 'locale' => 'Serbian_Republic_of_Serbia', 'codepage' => 'ISO8859-1'),
        'sv_SE' => array('code' => 'sv', 'language' => 'Swedish', 'locale' => 'Swedish_Sweden', 'codepage' => 'ISO8859-1'),
        'th_TH' => array('code' => 'th', 'language' => 'Thailand', 'locale' => 'Thai_Thailand', 'codepage' => 'ISO8859-1'),
        'tr_TR' => array('code' => 'tr', 'language' => 'Turkish', 'locale' => 'Turkish_Turkey', 'codepage' => 'ISO8859-1'),
        'zh_CN' => array('code' => 'cn', 'language' => 'Chinese', 'locale' => 'Chinese_China', 'codepage' => 'ISO8859-1'),
        'zh_TW' => array('code' => 'zh', 'language' => 'Chinese', 'locale' => 'Chinese_Taiwan', 'codepage' => 'ISO8859-1')
    );

    private $cisco_timezone = array(
        'Africa/Abidjan' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Accra' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Addis_Ababa' => array('Abbreviation' => 'EAT', 'cisco_code' => 'Eastern Africa Time', 'offset' => 180, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Algiers' => array('Abbreviation' => 'CET', 'cisco_code' => 'Central European Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Asmara' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Asmera' => array('Abbreviation' => 'EAT', 'cisco_code' => 'Eastern Africa Time', 'offset' => 180, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Bamako' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Bangui' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Banjul' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Bissau' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Blantyre' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Brazzaville' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Bujumbura' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Cairo' => array('Abbreviation' => 'EET', 'cisco_code' => 'Eastern European Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Casablanca' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => TRUE),
        'Africa/Ceuta' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => TRUE),
        'Africa/Conakry' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Dakar' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Dar_es_Salaam' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Djibouti' => array('Abbreviation' => 'EAT', 'cisco_code' => 'Eastern Africa Time', 'offset' => 180, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Douala' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/El_Aaiun' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Freetown' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Gaborone' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Harare' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Johannesburg' => array('Abbreviation' => 'SAST', 'cisco_code' => 'South Africa Standard Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Kampala' => array('Abbreviation' => 'EAT', 'cisco_code' => 'Eastern Africa Time', 'offset' => 180, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Khartoum' => array('Abbreviation' => 'EAT', 'cisco_code' => 'Eastern Africa Time', 'offset' => 180, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Kigali' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Kinshasa' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Lagos' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Libreville' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Lome' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Luanda' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Lubumbashi' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Lusaka' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Malabo' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Maputo' => array('Abbreviation' => 'CAT', 'cisco_code' => 'Central Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Maseru' => array('Abbreviation' => 'SAST', 'cisco_code' => 'South Africa Standard Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Mbabane' => array('Abbreviation' => 'SAST', 'cisco_code' => 'Eastern Africa Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Mogadishu' => array('Abbreviation' => 'EAT', 'cisco_code' => 'Eastern Africa Time', 'offset' => 180, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Monrovia' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Nairobi' => array('Abbreviation' => 'EAT', 'cisco_code' => 'Eastern Africa Time', 'offset' => 180, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Ndjamena' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Niamey' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Nouakchott' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Ouagadougou' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Porto-Novo' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Sao_Tome' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Timbuktu' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Tripoli' => array('Abbreviation' => 'EET', 'cisco_code' => 'Eastern European Time', 'offset' => 120, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Tunis' => array('Abbreviation' => 'CET', 'cisco_code' => 'Central European Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'Africa/Windhoek' => array('Abbreviation' => 'WAT', 'cisco_code' => 'West Africa Time', 'offset' => 60, 'region' => 'Africa', 'daylight' => FALSE),
        'America/Adak' => array('Abbreviation' => 'HADT', 'cisco_code' => 'Hawaii-Aleutian Daylight Time', 'offset' => -600, 'region' => 'America', 'daylight' => FALSE),
        'America/Anchorage' => array('Abbreviation' => 'AKDT', 'cisco_code' => 'Alaska Daylight Time', 'offset' => -540, 'region' => 'America', 'daylight' => FALSE),
        'America/Anguilla' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Antigua' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Araguaina' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasilia Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Buenos_Aires' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Catamarca' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/ComodRivadavia' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Cordoba' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Jujuy' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/La_Rioja' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Mendoza' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Rio_Gallegos' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Salta' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/San_Juan' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/San_Luis' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Tucuman' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Argentina/Ushuaia' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Aruba' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Asuncion' => array('Abbreviation' => 'PYT', 'cisco_code' => 'Paraguay Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Atikokan' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Atka' => array('Abbreviation' => 'HADT', 'cisco_code' => 'Hawaii-Aleutian Daylight Time', 'offset' => -540, 'region' => 'America', 'daylight' => FALSE),
        'America/Bahia' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasilia Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Barbados' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Belem' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasília time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Belize' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Blanc-Sablon' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Boa_Vista' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Bogota' => array('Abbreviation' => 'COT', 'cisco_code' => 'Columbia Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Boise' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Moutain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Buenos_Aires' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Cambridge_Bay' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Campo_Grande' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Cancun' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Caracas' => array('Abbreviation' => 'VET', 'cisco_code' => 'Venezuelan Standard Time', 'offset' => -270, 'region' => 'America', 'daylight' => FALSE),
        'America/Catamarca' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Cayenne' => array('Abbreviation' => 'GFT', 'cisco_code' => 'French Guiana Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Cayman' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Chicago' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Chihuahua' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Coral_Harbour' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Cordoba' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Costa_Rica' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Cuiaba' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Curacao' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Danmarkshavn' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwhich Mean Time', 'offset' => 0, 'region' => 'America', 'daylight' => FALSE),
        'America/Dawson' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Dawson_Creek' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Denver' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Detroit' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Dominica' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Edmonton' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Eirunepe' => array('Abbreviation' => 'ACST', 'cisco_code' => 'Australian Central Standard Time', 'offset' => 570, 'region' => 'America', 'daylight' => FALSE),
        'America/El_Salvador' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Ensenada' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -480, 'region' => 'America', 'daylight' => FALSE),
        'America/Fort_Wayne' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Fortaleza' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasilia Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Glace_Bay' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Godthab' => array('Abbreviation' => 'WGST', 'cisco_code' => 'Western Greenland Summer Time', 'offset' => -180, 'region' => 'America', 'daylight' => TRUE),
        'America/Goose_Bay' => array('Abbreviation' => 'ADT', 'cisco_code' => 'Atlantic Daylight Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Grand_Turk' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Grenada' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Guadeloupe' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Guatemala' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Guayaquil' => array('Abbreviation' => 'ECT', 'cisco_code' => 'Ecuador Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Guyana' => array('Abbreviation' => 'GYT', 'cisco_code' => 'Guyana Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Halifax' => array('Abbreviation' => 'ADT', 'cisco_code' => 'Atlantic Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Havana' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Cuba Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Hermosillo' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Indianapolis' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Knox' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Marengo' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Petersburg' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Tell_City' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Vevay' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Vincennes' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Indiana/Winamac' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Indianapolis' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Inuvik' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Iqaluit' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Jamaica' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Jujuy' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Juneau' => array('Abbreviation' => 'AKDT', 'cisco_code' => 'Alaska Daylight Time', 'offset' => -540, 'region' => 'America', 'daylight' => FALSE),
        'America/Kentucky/Louisville' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Kentucky/Monticello' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Knox_IN' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/La_Paz' => array('Abbreviation' => 'BOT', 'cisco_code' => 'Bolivia Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Lima' => array('Abbreviation' => 'PET', 'cisco_code' => 'Peru Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Los_Angeles' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -480, 'region' => 'America', 'daylight' => FALSE),
        'America/Louisville' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Maceio' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasilia Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Managua' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Manaus' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Marigot' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Martinique' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Mazatlan' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Mendoza' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Menominee' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Merida' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Mexico_City' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Miquelon' => array('Abbreviation' => 'PMDT', 'cisco_code' => 'Pierre & Miquelon Daylight Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Moncton' => array('Abbreviation' => 'ADT', 'cisco_code' => 'Atlantic Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Monterrey' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Montevideo' => array('Abbreviation' => 'UYT', 'cisco_code' => 'Uruguay Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Montreal' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Montserrat' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Nassau' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/New_York' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Nipigon' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Nome' => array('Abbreviation' => 'AKDT', 'cisco_code' => 'Alaska Daylight Time', 'offset' => -540, 'region' => 'America', 'daylight' => FALSE),
        'America/Noronha' => array('Abbreviation' => 'FNT', 'cisco_code' => 'Fernando de Noronha Time', 'offset' => -120, 'region' => 'America', 'daylight' => FALSE),
        'America/North_Dakota/Center' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/North_Dakota/New_Salem' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -480, 'region' => 'America', 'daylight' => FALSE),
        'America/Panama' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Pangnirtung' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Paramaribo' => array('Abbreviation' => 'SRT', 'cisco_code' => 'Suriname Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Phoenix' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/Port_of_Spain' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Port-au-Prince' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Porto_Acre' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Porto_Velho' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Puerto_Rico' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Rainy_River' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Rankin_Inlet' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Recife' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasilia Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Regina' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Resolute' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Rio_Branco' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Rosario' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Santarem' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasilia Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Santiago' => array('Abbreviation' => 'ART', 'cisco_code' => 'Argentina Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Santo_Domingo' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Sao_Paulo' => array('Abbreviation' => 'BRT', 'cisco_code' => 'Brasilla Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Scoresbysund' => array('Abbreviation' => 'EGST', 'cisco_code' => 'Eastern Greenland Summer Time', 'offset' => 0, 'region' => 'America', 'daylight' => TRUE),
        'America/Shiprock' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'America/St_Barthelemy' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/St_Johns' => array('Abbreviation' => 'NDT', 'cisco_code' => 'Newfoundland Daylight Time', 'offset' => -210, 'region' => 'America', 'daylight' => FALSE),
        'America/St_Kitts' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/St_Lucia' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/St_Thomas' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/St_Vincent' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Swift_Current' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Tegucigalpa' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Thule' => array('Abbreviation' => 'ADT', 'cisco_code' => 'Atlantic Daylight Time', 'offset' => -180, 'region' => 'America', 'daylight' => FALSE),
        'America/Thunder_Bay' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Tijuana' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -480, 'region' => 'America', 'daylight' => FALSE),
        'America/Toronto' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Tortola' => array('Abbreviation' => 'AST', 'cisco_code' => 'Atlantic Standard Time', 'offset' => -240, 'region' => 'America', 'daylight' => FALSE),
        'America/Vancouver' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -480, 'region' => 'America', 'daylight' => FALSE),
        'America/Virgin' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'America', 'daylight' => FALSE),
        'America/Whitehorse' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -480, 'region' => 'America', 'daylight' => FALSE),
        'America/Winnipeg' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => 'America', 'daylight' => FALSE),
        'America/Yakutat' => array('Abbreviation' => 'YDT', 'cisco_code' => 'Yukon Daylight Time', 'offset' => -480, 'region' => 'America', 'daylight' => FALSE),
        'America/Yellowknife' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'America', 'daylight' => FALSE),
        'Antarctica/Casey' => array('Abbreviation' => 'CAST', 'cisco_code' => 'Casey Time', 'offset' => 480, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/Davis' => array('Abbreviation' => 'DAVT', 'cisco_code' => 'Davis Time', 'offset' => 420, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/DumontDurville' => array('Abbreviation' => 'DDUT', 'cisco_code' => 'Dumont-d`Urville Time', 'offset' => 600, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/Mawson' => array('Abbreviation' => 'MAWT', 'cisco_code' => 'Mawson Time', 'offset' => 300, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/McMurdo' => array('Abbreviation' => 'NZST', 'cisco_code' => 'New Zealand Standard Time', 'offset' => 720, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/Palmer' => array('Abbreviation' => 'NZST', 'cisco_code' => 'New Zealand Standard Time', 'offset' => 720, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/Rothera' => array('Abbreviation' => 'ROTT', 'cisco_code' => 'Rothera Time', 'offset' => -180, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/South_Pole' => array('Abbreviation' => 'NZST', 'cisco_code' => 'New Zealand Standard Time', 'offset' => 720, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/Syowa' => array('Abbreviation' => 'SYOT', 'cisco_code' => 'Syowa Time Time Zone', 'offset' => 180, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Antarctica/Vostok' => array('Abbreviation' => 'VOST', 'cisco_code' => 'Vostok Time', 'offset' => 360, 'region' => 'Antarctica', 'daylight' => FALSE),
        'Arctic/Longyearbyen' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 60, 'region' => 'Arctic', 'daylight' => TRUE),
        'Asia/Aden' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Almaty' => array('Abbreviation' => 'ALMT', 'cisco_code' => 'Alma-Ata Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Amman' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 120, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Anadyr' => array('Abbreviation' => 'MAGST', 'cisco_code' => 'Magadan Summer Time', 'offset' => 720, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Aqtau' => array('Abbreviation' => 'AQTT', 'cisco_code' => 'Aqtobe Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Aqtobe' => array('Abbreviation' => 'AQTT', 'cisco_code' => 'Aqtobe Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Ashgabat' => array('Abbreviation' => 'TMT', 'cisco_code' => 'Turkmenistan Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Ashkhabad' => array('Abbreviation' => 'TMT', 'cisco_code' => 'Turkmenistan Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Baghdad' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Bahrain' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Baku' => array('Abbreviation' => 'AZST', 'cisco_code' => 'Azerbaijan Summer Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Bangkok' => array('Abbreviation' => 'ICT', 'cisco_code' => 'Indochina Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Beirut' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 120, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Bishkek' => array('Abbreviation' => 'KGT', 'cisco_code' => 'Kyrgyzstan Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Brunei' => array('Abbreviation' => 'BNT', 'cisco_code' => 'Brunei Darussalam Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Calcutta' => array('Abbreviation' => 'IST', 'cisco_code' => 'India Summer Time', 'offset' => 330, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Choibalsan' => array('Abbreviation' => 'ULAT', 'cisco_code' => 'Ulaanbaatar Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Chongqing' => array('Abbreviation' => 'CST', 'cisco_code' => 'China Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Chungking' => array('Abbreviation' => 'CST', 'cisco_code' => 'China Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Colombo' => array('Abbreviation' => 'IST', 'cisco_code' => 'India Standard Time', 'offset' => 330, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Dacca' => array('Abbreviation' => 'BST', 'cisco_code' => 'Bangladesh Standard Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Damascus' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 120, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Dhaka' => array('Abbreviation' => 'BST', 'cisco_code' => 'Bangladesh Standard Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Dili' => array('Abbreviation' => 'TLT', 'cisco_code' => 'East Timor Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Dubai' => array('Abbreviation' => 'GST', 'cisco_code' => 'Gulf Standard Time', 'offset' => 240, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Dushanbe' => array('Abbreviation' => 'TJT', 'cisco_code' => 'Tajikistan Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Gaza' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 120, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Harbin' => array('Abbreviation' => 'CST', 'cisco_code' => 'China Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Ho_Chi_Minh' => array('Abbreviation' => 'ICT', 'cisco_code' => 'Indochina Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Hong_Kong' => array('Abbreviation' => 'HKT', 'cisco_code' => 'Hong Kong Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Hovd' => array('Abbreviation' => 'HOVT', 'cisco_code' => 'Hovd Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Irkutsk' => array('Abbreviation' => 'IRKST', 'cisco_code' => 'Irkutsk Summer Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Istanbul' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 120, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Jakarta' => array('Abbreviation' => 'WIT', 'cisco_code' => 'Western Indonesian Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Jayapura' => array('Abbreviation' => 'WIT', 'cisco_code' => 'Eastern Indonesian Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Jerusalem' => array('Abbreviation' => 'IDT', 'cisco_code' => 'Israel Daylight Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Kabul' => array('Abbreviation' => 'AFT', 'cisco_code' => 'Afghanistan Time', 'offset' => 270, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Kamchatka' => array('Abbreviation' => 'PETST', 'cisco_code' => 'Kamchatka Summer Time', 'offset' => 720, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Karachi' => array('Abbreviation' => 'PKT', 'cisco_code' => 'Pakistan Standard Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Kashgar' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Kathmandu' => array('Abbreviation' => 'NPT', 'cisco_code' => 'Nepal Time', 'offset' => 345, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Katmandu' => array('Abbreviation' => 'NPT', 'cisco_code' => 'Nepal Time', 'offset' => 345, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Kolkata' => array('Abbreviation' => 'IST', 'cisco_code' => 'India Standard Time', 'offset' => 330, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Krasnoyarsk' => array('Abbreviation' => 'KRAST', 'cisco_code' => 'Krasnoyarsk Summer Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Kuala_Lumpur' => array('Abbreviation' => 'MYT', 'cisco_code' => 'Malaysia Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Kuching' => array('Abbreviation' => 'MYT', 'cisco_code' => 'Malaysia Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Kuwait' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Macao' => array('Abbreviation' => 'CST', 'cisco_code' => 'China Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Macau' => array('Abbreviation' => 'CST', 'cisco_code' => 'Chinsa Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Magadan' => array('Abbreviation' => 'MAGST', 'cisco_code' => 'Magadan Summer Time', 'offset' => 720, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Makassar' => array('Abbreviation' => 'WITA', 'cisco_code' => 'Central Indonesian Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Manila' => array('Abbreviation' => 'PHT', 'cisco_code' => 'Philippine Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Muscat' => array('Abbreviation' => 'GST', 'cisco_code' => 'Gulf Standard Time', 'offset' => 240, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Nicosia' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 120, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Novosibirsk' => array('Abbreviation' => 'NOVST', 'cisco_code' => 'Novosibirsk Summer Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Omsk' => array('Abbreviation' => 'OMSST', 'cisco_code' => 'Omsk Summer Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Oral' => array('Abbreviation' => 'ORAT', 'cisco_code' => 'ORAT', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Phnom_Penh' => array('Abbreviation' => 'ICT', 'cisco_code' => 'Indochina Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Pontianak' => array('Abbreviation' => 'WIB', 'cisco_code' => 'Western Indonesian Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Pyongyang' => array('Abbreviation' => 'KST', 'cisco_code' => 'Korea Standard Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Qatar' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Qyzylorda' => array('Abbreviation' => 'QYZT', 'cisco_code' => 'Qyzylorda Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Rangoon' => array('Abbreviation' => 'MMT', 'cisco_code' => 'Myanmar Time', 'offset' => 390, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Riyadh' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Riyadh' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Riyadh87' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arab Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Riyadh89' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Saigon' => array('Abbreviation' => 'ICT', 'cisco_code' => 'Indochina Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Sakhalin' => array('Abbreviation' => 'SAST', 'cisco_code' => 'South Africa Standard Time', 'offset' => 600, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Samarkand' => array('Abbreviation' => 'UZT', 'cisco_code' => 'Uzbekistan Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Seoul' => array('Abbreviation' => 'KST', 'cisco_code' => 'Korea Standard Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Shanghai' => array('Abbreviation' => 'CCT', 'cisco_code' => 'China Taiwan Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Singapore' => array('Abbreviation' => 'SGT', 'cisco_code' => 'Singapore Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Taipei' => array('Abbreviation' => 'CST', 'cisco_code' => 'China Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Tashkent' => array('Abbreviation' => 'UZT', 'cisco_code' => 'Uzbekistan Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Tbilisi' => array('Abbreviation' => 'GET', 'cisco_code' => 'Georgia Standard Time', 'offset' => 240, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Tehran' => array('Abbreviation' => 'IRDT', 'cisco_code' => 'Iran Daylight Time', 'offset' => 270, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Tel_Aviv' => array('Abbreviation' => 'IDT', 'cisco_code' => 'Israel Daylight Time', 'offset' => 180, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Thimbu' => array('Abbreviation' => 'BTT', 'cisco_code' => 'Bhutan Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Thimphu' => array('Abbreviation' => 'BTT', 'cisco_code' => 'Bhutan Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Tokyo' => array('Abbreviation' => 'JST', 'cisco_code' => 'Japan Standard Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Ujung_Pandang' => array('Abbreviation' => 'WITA', 'cisco_code' => 'Central Indonesian Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Ulaanbaatar' => array('Abbreviation' => 'ULAT', 'cisco_code' => 'Ulaanbaatar Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Ulan_Bator' => array('Abbreviation' => 'IRKST', 'cisco_code' => 'Irkutsk Summer Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Urumqi' => array('Abbreviation' => 'CST', 'cisco_code' => 'Chinsa Standard Time', 'offset' => 480, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Vientiane' => array('Abbreviation' => 'ICT', 'cisco_code' => 'Indochina Time', 'offset' => 420, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Vladivostok' => array('Abbreviation' => 'VLAST', 'cisco_code' => 'Vladivostok Summer Time', 'offset' => 660, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Yakutsk' => array('Abbreviation' => 'YAKT', 'cisco_code' => 'Yakutsk Time', 'offset' => 540, 'region' => 'Asia', 'daylight' => FALSE),
        'Asia/Yekaterinburg' => array('Abbreviation' => 'YEKST', 'cisco_code' => 'Yekaterinburg Summer Time', 'offset' => 360, 'region' => 'Asia', 'daylight' => TRUE),
        'Asia/Yerevan' => array('Abbreviation' => 'AST', 'cisco_code' => 'Armenia Summer Time', 'offset' => 300, 'region' => 'Asia', 'daylight' => TRUE),
        'Atlantic/Azores' => array('Abbreviation' => 'AZOST', 'cisco_code' => 'Azores Summer Time', 'offset' => 0, 'region' => 'Atlantic', 'daylight' => TRUE),
        'Atlantic/Bermuda' => array('Abbreviation' => 'ADT', 'cisco_code' => 'Atlantic Daylight Time', 'offset' => -180, 'region' => 'Atlantic', 'daylight' => FALSE),
        'Atlantic/Canary' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 60, 'region' => 'Atlantic', 'daylight' => TRUE),
        'Atlantic/Cape_Verde' => array('Abbreviation' => 'CVT', 'cisco_code' => 'Current Cape Verde Time', 'offset' => -60, 'region' => 'Atlantic', 'daylight' => FALSE),
        'Atlantic/Faeroe' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 60, 'region' => 'Atlantic', 'daylight' => TRUE),
        'Atlantic/Faroe' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 60, 'region' => 'Atlantic', 'daylight' => TRUE),
        'Atlantic/Jan_Mayen' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Atlantic', 'daylight' => TRUE),
        'Atlantic/Madeira' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 60, 'region' => 'Atlantic', 'daylight' => TRUE),
        'Atlantic/Reykjavik' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Atlantic', 'daylight' => FALSE),
        'Atlantic/South_Georgia' => array('Abbreviation' => 'GST', 'cisco_code' => 'Guam Standard Time', 'offset' => -120, 'region' => 'Atlantic', 'daylight' => FALSE),
        'Atlantic/St_Helena' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => 'Atlantic', 'daylight' => FALSE),
        'Atlantic/Stanley' => array('Abbreviation' => 'FKST', 'cisco_code' => 'Falkland Islands Summer Time', 'offset' => -180, 'region' => 'Atlantic', 'daylight' => TRUE),
        'Australia/ACT' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Adelaide' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 570, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Brisbane' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 630, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Broken_Hill' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 570, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Canberra' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Currie' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Darwin' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 570, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Eucla' => array('Abbreviation' => 'CWST', 'cisco_code' => 'Central Western Summer Time', 'offset' => 525, 'region' => 'Australia', 'daylight' => TRUE),
        'Australia/Hobart' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/LHI' => array('Abbreviation' => 'LHST', 'cisco_code' => 'Lord Howe Standard Time', 'offset' => 630, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Lord_Howe' => array('Abbreviation' => 'LHST', 'cisco_code' => 'Lord Howe Standard Time', 'offset' => 630, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Melbourne' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/North' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 570, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/NSW' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Perth' => array('Abbreviation' => 'WST', 'cisco_code' => 'Western Standard Time', 'offset' => 480, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Queensland' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/South' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 570, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Sydney' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Tasmania' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Victoria' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => 600, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/West' => array('Abbreviation' => 'WST', 'cisco_code' => 'Western Standard Time', 'offset' => 480, 'region' => 'Australia', 'daylight' => FALSE),
        'Australia/Yancowinna' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 570, 'region' => 'Australia', 'daylight' => FALSE),
        'Brazil/Acre' => array('Abbreviation' => 'AMT', 'cisco_code' => 'Amazon Time', 'offset' => -240, 'region' => 'Brazil', 'daylight' => FALSE),
        'Brazil/DeNoronha' => array('Abbreviation' => 'FNT', 'cisco_code' => 'Fernando de Noronha Time', 'offset' => -120, 'region' => 'Brazil', 'daylight' => FALSE),
        'Brazil/East' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -180, 'region' => 'Brazil', 'daylight' => FALSE),
        'Brazil/West' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Brazil', 'daylight' => TRUE),
        'Canada/Atlantic' => array('Abbreviation' => 'ADT', 'cisco_code' => 'Atlantic Daylight Time', 'offset' => -180, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/Central' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -300, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/East-Saskatchewan' => array('Abbreviation' => 'CST', 'cisco_code' => 'Cenntral Standard Time', 'offset' => -360, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/Eastern' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/Mountain' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/Newfoundland' => array('Abbreviation' => 'NDT', 'cisco_code' => 'Newfoundland Daylight Time', 'offset' => -150, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/Pacific' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -420, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/Saskatchewan' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'Canada', 'daylight' => FALSE),
        'Canada/Yukon' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'Canada', 'daylight' => FALSE),
        'CET' => array('Abbreviation' => 'CET', 'cisco_code' => 'Central Europian Time', 'offset' => 60, 'region' => '', 'daylight' => FALSE),
        'CST6CDT' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Central Daylight Time', 'offset' => -360, 'region' => '', 'daylight' => FALSE),
        'Cuba' => array('Abbreviation' => 'CDT', 'cisco_code' => 'Cuba Daylight Time', 'offset' => -300, 'region' => '', 'daylight' => FALSE),
        'EET' => array('Abbreviation' => 'EET', 'cisco_code' => 'Eastern European Time', 'offset' => 120, 'region' => '', 'daylight' => FALSE),
        'Egypt' => array('Abbreviation' => 'EET', 'cisco_code' => 'Eastern European Time', 'offset' => 120, 'region' => '', 'daylight' => FALSE),
        'Eire' => array('Abbreviation' => 'IST', 'cisco_code' => 'Irish Standard Time', 'offset' => 60, 'region' => '', 'daylight' => FALSE),
        'EST' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => '', 'daylight' => FALSE),
        'EST5EDT' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => '', 'daylight' => FALSE),
        'Europe/Amsterdam' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Amsterdam' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Andorra' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Andorra' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Athens' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Athens' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Belfast' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Belfast' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Belgrade' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Belgrade' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Berlin' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Berlin' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Bratislava' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Bratislava' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Brussels' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Brussels' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Bucharest' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Bucharest' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Budapest' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Budapest' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Chisinau' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Copenhagen' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Copenhagen' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Dublin' => array('Abbreviation' => 'IST', 'cisco_code' => 'Irish Standard Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Dublin' => array('Abbreviation' => 'IST', 'cisco_code' => 'Irish Standard Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Gibraltar' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Gibraltar' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Guernsey' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Guernsey' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Helsinki' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Helsinki' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Isle_of_Man' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Isle_of_Man' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Istanbul' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Istanbul' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Jersey' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Jersey' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Kaliningrad' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Kiev' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Kiev' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Lisbon' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Lisbon' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Ljubljana' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/London' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/London' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Luxembourg' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Luxembourg' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Madrid' => array('Abbreviation' => 'CET', 'cisco_code' => 'Central European Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Madrid' => array('Abbreviation' => 'CET', 'cisco_code' => 'Central European Time', 'offset' => 60, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Malta' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Malta' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Mariehamn' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Mariehamn' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Minsk' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Monaco' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Monaco' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Moscow' => array('Abbreviation' => 'MSD', 'cisco_code' => 'Moscow Daylight Time', 'offset' => 240, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Moscow' => array('Abbreviation' => 'MSD', 'cisco_code' => 'Moscow Daylight Time', 'offset' => 240, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Nicosia' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Nicosia' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Oslo' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Paris' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Podgorica' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Podgorica' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Prague' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Prague' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Riga' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Riga' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Rome' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Rome' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Samara' => array('Abbreviation' => 'KUYT', 'cisco_code' => 'Kuybyshev Time', 'offset' => 240, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Samara' => array('Abbreviation' => 'KUYT', 'cisco_code' => 'Kuybyshev Time', 'offset' => 240, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/San Marino' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/San Marino' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Sarajevo' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Sarajevo' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Simferopol' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Simferopol' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Skopje' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Sofia' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Sofia' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Stockholm' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Tallinn' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Tallinn' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Tirana' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Tirana' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Tiraspol' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Uzhgorod' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Vaduz' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Vaduz' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Vatican' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Vatican' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Vienna' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Vienna' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Vilnius' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Volgograd' => array('Abbreviation' => 'MSD', 'cisco_code' => 'Moscow Daylight Time', 'offset' => 240, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Volgograd' => array('Abbreviation' => 'MSD', 'cisco_code' => 'Moscow Daylight Time', 'offset' => 240, 'region' => 'Europe', 'daylight' => FALSE),
        'Europe/Warsaw' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Zagreb' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Zaporozhye' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 180, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Zurich' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Europe/Zurich' => array('Abbreviation' => 'CEST', 'cisco_code' => 'Central European Summer Time', 'offset' => 120, 'region' => 'Europe', 'daylight' => TRUE),
        'Factory' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwhich Mean Time', 'offset' => 0, 'region' => '', 'daylight' => FALSE),
        'GB' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => '', 'daylight' => TRUE),
        'GB-Eire' => array('Abbreviation' => 'BST', 'cisco_code' => 'British Summer Time', 'offset' => 60, 'region' => '', 'daylight' => TRUE),
        'GMT' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwhich Mean Time', 'offset' => 0, 'region' => '', 'daylight' => FALSE),
        'GMT0' => array('Abbreviation' => 'AZOST', 'cisco_code' => 'Azores Summer Time', 'offset' => -60, 'region' => '', 'daylight' => TRUE),
        'Greenwich' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => '', 'daylight' => FALSE),
        'Hongkong' => array('Abbreviation' => 'HKT', 'cisco_code' => 'Hong Kong Time', 'offset' => 480, 'region' => '', 'daylight' => FALSE),
        'HST' => array('Abbreviation' => 'HAST', 'cisco_code' => 'Hawaii-Aleutian Standard Time', 'offset' => -600, 'region' => '', 'daylight' => FALSE),
        'Iceland' => array('Abbreviation' => 'GMT', 'cisco_code' => 'Greenwich Mean Time', 'offset' => 0, 'region' => '', 'daylight' => FALSE),
        'Indian/Antananarivo' => array('Abbreviation' => 'EAT', 'cisco_code' => 'East Africa Time', 'offset' => 180, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Chagos' => array('Abbreviation' => 'IOT', 'cisco_code' => 'Indian Chagos Time', 'offset' => 360, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Christmas' => array('Abbreviation' => 'CXT', 'cisco_code' => 'Christmas Island Time', 'offset' => 420, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Cocos' => array('Abbreviation' => 'CCT', 'cisco_code' => 'Cocos Islands Time', 'offset' => 390, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Comoro' => array('Abbreviation' => 'EAT', 'cisco_code' => 'East Africa Time', 'offset' => 180, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Kerguelen' => array('Abbreviation' => 'TFT', 'cisco_code' => 'French Southern and Antartic Time', 'offset' => 300, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Mahe' => array('Abbreviation' => 'IST', 'cisco_code' => 'India Standard Time', 'offset' => 330, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Maldives' => array('Abbreviation' => 'MVT', 'cisco_code' => 'Maldives Time', 'offset' => 300, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Mauritius' => array('Abbreviation' => 'MUT', 'cisco_code' => 'Mauritius Time', 'offset' => 240, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Mayotte' => array('Abbreviation' => 'EAT', 'cisco_code' => 'East Africa Time', 'offset' => 180, 'region' => 'Indian', 'daylight' => FALSE),
        'Indian/Reunion' => array('Abbreviation' => 'RET', 'cisco_code' => 'Reunion Time', 'offset' => 240, 'region' => 'Indian', 'daylight' => FALSE),
        'Iran/Tehran' => array('Abbreviation' => 'IRDT', 'cisco_code' => 'Iran Daylight Time', 'offset' => 270, 'region' => 'Iran', 'daylight' => FALSE),
        'Iran/Tehran' => array('Abbreviation' => 'IRST', 'cisco_code' => 'Iran Standard Time', 'offset' => 210, 'region' => 'Iran', 'daylight' => FALSE),
        'Iran/Tehran' => array('Abbreviation' => 'IRDT', 'cisco_code' => 'Iran Daylight Time', 'offset' => 270, 'region' => 'Iran', 'daylight' => FALSE),
        'Iran/Tehran' => array('Abbreviation' => 'IRST', 'cisco_code' => 'Iran Standard Time', 'offset' => 210, 'region' => 'Iran', 'daylight' => FALSE),
        'Jamaica' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => '', 'daylight' => FALSE),
        'Japan' => array('Abbreviation' => 'JST', 'cisco_code' => 'Japan Standard Time', 'offset' => 540, 'region' => '', 'daylight' => FALSE),
        'Kwajalein' => array('Abbreviation' => 'MHT', 'cisco_code' => 'Marshall Islands Time', 'offset' => 720, 'region' => '', 'daylight' => FALSE),
        'MET' => array('Abbreviation' => 'MEST', 'cisco_code' => 'Middle European Summer Time', 'offset' => 120, 'region' => '', 'daylight' => TRUE),
        'Mideast/Riyadh87' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Mideast', 'daylight' => FALSE),
        'Mideast/Riyadh88' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Mideast', 'daylight' => FALSE),
        'Mideast/Riyadh89' => array('Abbreviation' => 'AST', 'cisco_code' => 'Arabia Standard Time', 'offset' => 180, 'region' => 'Mideast', 'daylight' => FALSE),
        'MST' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => '', 'daylight' => FALSE),
        'MST7MDT' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -360, 'region' => '', 'daylight' => FALSE),
        'Navajo' => array('Abbreviation' => 'MDT', 'cisco_code' => 'Mountain Daylight Time', 'offset' => -420, 'region' => '', 'daylight' => FALSE),
        'NZ' => array('Abbreviation' => 'NZST', 'cisco_code' => 'New Zealand Standard Time', 'offset' => 720, 'region' => '', 'daylight' => FALSE),
        'Pacific/Apia' => array('Abbreviation' => 'WST', 'cisco_code' => 'West Samoa Time', 'offset' => -660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Auckland' => array('Abbreviation' => 'NZST', 'cisco_code' => 'New Zealand Standard Time', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Chatham' => array('Abbreviation' => 'CHAST', 'cisco_code' => 'Chatham Island Standard Time', 'offset' => 765, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Easter' => array('Abbreviation' => 'EAST', 'cisco_code' => 'Easter Island Standard Time', 'offset' => -360, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Efate' => array('Abbreviation' => 'VUT', 'cisco_code' => 'Vanuata Time', 'offset' => 660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Enderbury' => array('Abbreviation' => 'PHOT', 'cisco_code' => 'Phoenix Island Time', 'offset' => 780, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Fakaofo' => array('Abbreviation' => 'TKT', 'cisco_code' => 'Tokelau Time', 'offset' => -600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Fiji' => array('Abbreviation' => 'FJT', 'cisco_code' => 'Fiji Time', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Funafuti' => array('Abbreviation' => 'TVT', 'cisco_code' => 'Tuvalu Time', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Galapagos' => array('Abbreviation' => 'GALT', 'cisco_code' => 'Galapagos Time', 'offset' => -360, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Gambier' => array('Abbreviation' => 'GAMT', 'cisco_code' => 'Gambier Time', 'offset' => 540, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Guadalcanal' => array('Abbreviation' => 'SBT', 'cisco_code' => 'Solomon Island Time', 'offset' => 660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Guam' => array('Abbreviation' => 'ChST', 'cisco_code' => 'Chamorro Standard Time', 'offset' => 600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Honolulu' => array('Abbreviation' => 'HAST', 'cisco_code' => 'Hawaii-Aleutian Standard Time', 'offset' => -600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Johnston' => array('Abbreviation' => 'HDT', 'cisco_code' => 'Hawaiian Daylight Time', 'offset' => -570, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Kiritimati' => array('Abbreviation' => 'LINT', 'cisco_code' => 'Line Islands Time', 'offset' => 840, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Kosrae' => array('Abbreviation' => 'KOST', 'cisco_code' => 'Kosrae Standard Time', 'offset' => 660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Kwajalein' => array('Abbreviation' => 'MHT', 'cisco_code' => 'Marshall Islands Time', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Majuro' => array('Abbreviation' => 'MHT', 'cisco_code' => 'Marshall Islands Time', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Marquesas' => array('Abbreviation' => 'MART', 'cisco_code' => 'Marquesas Time', 'offset' => -570, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Midway' => array('Abbreviation' => 'SST', 'cisco_code' => 'Samoa Standard Time', 'offset' => -660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Nauru' => array('Abbreviation' => 'NRT', 'cisco_code' => 'Nauru Time Zone', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Niue' => array('Abbreviation' => 'NUT', 'cisco_code' => 'Niue Time', 'offset' => -660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Norfolk' => array('Abbreviation' => 'NFT', 'cisco_code' => 'Norfolk Time', 'offset' => 690, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Noumea' => array('Abbreviation' => 'NCT', 'cisco_code' => 'New Caledonia Time', 'offset' => 660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Pago_Pago' => array('Abbreviation' => 'SST', 'cisco_code' => 'Samoa Standard Time', 'offset' => -660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Palau' => array('Abbreviation' => 'PWT', 'cisco_code' => 'Palau Time', 'offset' => 540, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Pitcairn' => array('Abbreviation' => 'PST', 'cisco_code' => 'Pitcairn Standard Time', 'offset' => -480, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Ponape' => array('Abbreviation' => 'PONT', 'cisco_code' => 'Pohnpei Standard Time', 'offset' => 660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Port_Moresby' => array('Abbreviation' => 'PGT', 'cisco_code' => 'Papua New Guinea Time', 'offset' => 600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Rarotonga' => array('Abbreviation' => 'CKT', 'cisco_code' => 'Cook Island Time', 'offset' => -600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Saipan' => array('Abbreviation' => 'ChST', 'cisco_code' => 'Chamorro Standard Time', 'offset' => 600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Samoa' => array('Abbreviation' => 'SST', 'cisco_code' => 'Samoa Standard Time', 'offset' => -660, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Tahiti' => array('Abbreviation' => 'TAHT', 'cisco_code' => 'Tahiti Time', 'offset' => -600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Tarawa' => array('Abbreviation' => 'GILT', 'cisco_code' => 'Gilbert Island Time', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Tongatapu' => array('Abbreviation' => 'TOST', 'cisco_code' => 'Tongatapu Standard Time', 'offset' => 780, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Truk' => array('Abbreviation' => 'TRUT', 'cisco_code' => 'Truk Time', 'offset' => 600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Wake' => array('Abbreviation' => 'WAKT', 'cisco_code' => 'Wake Iland Time Zone', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Wallis' => array('Abbreviation' => 'WFT', 'cisco_code' => 'Wallis and Futuna Time', 'offset' => 720, 'region' => 'Pacific', 'daylight' => FALSE),
        'Pacific/Yap' => array('Abbreviation' => 'YAPT', 'cisco_code' => 'Yap Time', 'offset' => 600, 'region' => 'Pacific', 'daylight' => FALSE),
        'Poland' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -240, 'region' => '', 'daylight' => FALSE),
        'Portugal' => array('Abbreviation' => 'WEST', 'cisco_code' => 'Western European Summer Time', 'offset' => 60, 'region' => '', 'daylight' => TRUE),
        'PRC' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => '', 'daylight' => FALSE),
        'PST8PDT' => array('Abbreviation' => 'PDT', 'cisco_code' => 'Pacific Daylight Time', 'offset' => -420, 'region' => '', 'daylight' => FALSE),
        'ROC' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => 480, 'region' => '', 'daylight' => FALSE),
        'Singapore' => array('Abbreviation' => 'SGT', 'cisco_code' => 'Singapore Time', 'offset' => 480, 'region' => '', 'daylight' => FALSE),
        'Turkey' => array('Abbreviation' => 'EEST', 'cisco_code' => 'Eastern European Summer Time', 'offset' => 120, 'region' => '', 'daylight' => TRUE),
        'UCT' => array('Abbreviation' => 'UCT', 'cisco_code' => 'Universal Time', 'offset' => 0, 'region' => '', 'daylight' => FALSE),
        'United States/Pacific' => array('Abbreviation' => 'PST', 'cisco_code' => 'Pacific Standard Time', 'offset' => -480, 'region' => 'United States', 'daylight' => FALSE),
        'Universal' => array('Abbreviation' => 'UCT', 'cisco_code' => 'Universal Time', 'offset' => 0, 'region' => '', 'daylight' => FALSE),
        'US/Alaska' => array('Abbreviation' => 'AST', 'cisco_code' => 'Alaska Standard Time', 'offset' => -540, 'region' => 'US', 'daylight' => FALSE),
        'US/Aleutian' => array('Abbreviation' => 'HAST', 'cisco_code' => 'Hawaii-Aleutian Standard Time', 'offset' => -600, 'region' => 'US', 'daylight' => FALSE),
        'US/Arizona' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => 'US', 'daylight' => FALSE),
        'US/Central' => array('Abbreviation' => 'CST', 'cisco_code' => 'Central Standard Time', 'offset' => -360, 'region' => 'US', 'daylight' => FALSE),
        'US/East-Indiana' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'US', 'daylight' => FALSE),
        'US/Eastern' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'US', 'daylight' => FALSE),
        'US/Hawaii' => array('Abbreviation' => 'HAST', 'cisco_code' => 'Hawaii-Aleutian Standard Time', 'offset' => -600, 'region' => 'US', 'daylight' => FALSE),
        'US/Indiana-Starke' => array('Abbreviation' => 'EST', 'cisco_code' => 'Eastern Standard Time', 'offset' => -300, 'region' => 'US', 'daylight' => FALSE),
        'US/Michigan' => array('Abbreviation' => 'EDT', 'cisco_code' => 'Eastern Daylight Time', 'offset' => -300, 'region' => 'US', 'daylight' => FALSE),
        'US/Mountain' => array('Abbreviation' => 'MST', 'cisco_code' => 'Mountain Standard Time', 'offset' => -420, 'region' => 'US', 'daylight' => FALSE),
        'US/Samoa' => array('Abbreviation' => 'SST', 'cisco_code' => 'Samoa Standard Time', 'offset' => -660, 'region' => 'US', 'daylight' => FALSE),
        'UTC' => array('Abbreviation' => 'Universal', 'cisco_code' => 'Universal', 'offset' => 0, 'region' => '', 'daylight' => FALSE),
        'W-SU' => array('Abbreviation' => 'MSD', 'cisco_code' => 'Mascow Daylight Time', 'offset' => 180, 'region' => '', 'daylight' => FALSE),
        'Zulu' => array('Abbreviation' => 'Universal', 'cisco_code' => 'Universal Time', 'offset' => 0, 'region' => '', 'daylight' => FALSE),
    );

    public function validate_init_path($confDir = '', $db_vars, $sccp_driver_replace = '') {
//        global $db;
//        global $amp_conf;
// *** Setings for Provision Sccp        
        $adv_config = Array('tftproot' => '', 'firmware' => 'firmware', 'settings' => 'settings',
            'locales' => 'locales', 'languages' => 'languages', 'templates' => 'templates', 'dialplan' => 'dialplan', 'softkey' => 'softkey');
// 'pro' /tftpboot - root dir 
//       /tftpboot/locales/locales/%Languge_name%
//       /tftpboot/settings/XMLdefault.cnf.xml
//       /tftpboot/settings/SEP[MAC].cnf.xml
//       /tftpboot/firmware/79xx/SCCPxxxx.loads
        $adv_tree['pro'] = Array('templates' => 'tftproot', 'settings' => 'tftproot', 'locales' => 'tftproot', 'firmware' => 'tftproot', 'languages' => 'locales', 'dialplan' => 'tftproot', 'softkey' => 'tftproot');

// 'def' /tftpboot - root dir 
//       /tftpboot/languages/%Languge_name%
//       /tftpboot/XMLdefault.cnf.xml
//       /tftpboot/SEP[MAC].cnf.xml
//       /tftpboot/SCCPxxxx.loads
        $adv_tree['def'] = Array('templates' => 'tftproot', 'settings' => '', 'locales' => '', 'firmware' => '', 'languages' => 'tftproot','dialplan' => '', 'softkey' => '');
//        $adv_tree['def']   = Array('templates' => 'tftproot', 'settings' => '', 'locales' => 'tftproot',  'firmware' => 'tftproot', 'languages' => '');
//        $adv_tree['def'] = Array('templates' => 'tftproot', 'settings' => '', 'locales' => 'tftproot', 'firmware' => 'tftproot', 'languages' => 'tftproot');
        
//* **************------ ****        
        $base_tree = Array('tftp_templates' => 'templates', 'tftp_path_store' => 'settings', 'tftp_lang_path' => 'languages', 'tftp_firmware_path' => 'firmware', 'tftp_dialplan' => 'dialplan', 'tftp_softkey' => 'softkey');

        if (empty($confDir)) {
            return array('error' => 'empty СonfDir');
        }

        $base_config = Array('asterisk' => $confDir, 'sccp_conf' => $confDir . '/sccp.conf', 'tftp_path' => '');

//      Test Base dir (/tftproot)
        if (!empty($db_vars["tftp_path"])) {
            if (file_exists($db_vars["tftp_path"]["data"])) {
                $base_config["tftp_path"] = $db_vars["tftp_path"]["data"];
            }
        }
        if (empty($base_config["tftp_path"])) {
            if (file_exists($this->getextConfig('sccpDefaults', "tftp_path"))) {
                $base_config["tftp_path"] = $this->getextConfig('sccpDefaults', "tftp_path");
            }
        }
        if (empty($base_config["tftp_path"])) {
            if (!empty($this->paren_class)) {
                $this->paren_class->class_error['tftp_path'] = 'Tftp path not exist or not defined';
            }
            return array('error' => 'empty tftp_path');
        }
        if (!is_writeable($base_config["tftp_path"])) {
            if (!empty($this->paren_class)) {
                $this->paren_class->class_error['tftp_path'] = 'No write permision on tftp DIR';
            }
            return array('error' => 'No write permision on tftp DIR');
        }
//      END Test Base dir (/tftproot)

        if (!empty($db_vars['tftp_rewrite_path'])) {
            $adv_ini = $db_vars['tftp_rewrite_path']["data"];
        }

        $adv_tree_mode = 'def';
        if (empty($db_vars["tftp_rewrite"])) {
            $db_vars["tftp_rewrite"]["data"] = "off";
        }

        $adv_config['tftproot'] = $base_config["tftp_path"];
        if ($db_vars["tftp_rewrite"]["data"] == 'pro') {
            $adv_tree_mode = 'pro';
            if (!empty($adv_ini)) { // something found in external conflicts
                $adv_ini .= '/index.cnf';
                if (file_exists($adv_ini)) {
                    $adv_ini_array = parse_ini_file($adv_ini);
                    $adv_config = array_merge($adv_config, $adv_ini_array);
                }
            }
        }
        if ($db_vars["tftp_rewrite"]["data"] == 'on') {
            $adv_tree_mode = 'def';
        }
        foreach ($adv_tree[$adv_tree_mode] as $key => $value) {
            if (!empty($adv_config[$key])) {
                if (!empty($value)) {
                    if (substr($adv_config[$key], 0, 1) != "/") {
                        $adv_config[$key] = $adv_config[$value] . '/' . $adv_config[$key];
                    }
                } else {
                    $adv_config[$key] = $adv_config['tftproot'];
                }
            }
        }
        foreach ($base_tree as $key => $value) {
            $base_config[$key] = $adv_config[$value];
            if (!file_exists($base_config[$key])) {
                if (!mkdir($base_config[$key], 0777, true)) {
                    die('Error creating dir : ' . $base_config[$key]);
                }
            }
        }
        print_r($base_config,1);
//        die(print_r($base_config,1));
//        $base_config['External_ini'] = $adv_config;
//        $base_config['External_mode'] =  $adv_tree_mode;

        /*
          if (!empty($this->sccppath["tftp_path"])) {
          $this->sccppath["tftp_DP"] = $this->sccppath["tftp_path"] . '/Dialplan';
          if (!file_exists($this->sccppath["tftp_DP"])) {
          if (!mkdir($this->sccppath["tftp_DP"], 0777, true)) {
          die('Error creating DialPlan template dir');
          }
          }
          }
         */
        //    TFTP -REWrite        double model 
        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            if (!empty($this->paren_class)) {
                $this->paren_class->class_error['DOCUMENT_ROOT'] = 'Empty DOCUMENT_ROOT';
            }
            $base_config['error'] = 'Empty DOCUMENT_ROOT';
            return $base_config;
        }

        if (!file_exists($base_config["tftp_templates"] . '/XMLDefault.cnf.xml_template')) {
            $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/';
            $dst_path = $base_config["tftp_templates"] . '/';
            foreach (glob($src_path . '*.*_template') as $filename) {
                copy($filename, $dst_path . basename($filename));
            }
        }


        $dst = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/core/functions.inc/drivers/Sccp.class.php';
        if (!file_exists($dst) || $sccp_driver_replace == 'yes') {
            $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/' . basename($dst) . '.v' . $db_vars['sccp_compatible']['data'];
            if (file_exists($src_path)) {
                copy($src_path, $dst);
            } else {
                $src_path = $_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/' . basename($dst);
                copy($src_path, $dst);
            }
        }

        if (!file_exists($base_config["sccp_conf"])) { // System re Config 
            $sccpfile = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/admin/modules/sccp_manager/conf/sccp.conf');
            file_put_contents($base_config["sccp_conf"], $sccpfile);
        }

        return $base_config;
    }

    public function validate_RealTime($realm = '') {
        global $amp_conf;
        $res = Array();
        if (empty($realm) ) {
            $realm = 'sccp';
        }
        $cnf_int = \FreePBX::Config();
        $cnf_wr = \FreePBX::WriteConfig();
        $cnf_read = \FreePBX::LoadConfig();
        
        $def_config = array('sccpdevice' => 'mysql,'.$realm.',sccpdeviceconfig', 'sccpline' => 'mysql,'.$realm.',sccpline');
        $backup_ext = array('_custom.conf', '.conf', '_additional.conf');
        $def_bd_config = array('dbhost' => $amp_conf['AMPDBHOST'], 'dbname' => $amp_conf['AMPDBNAME'],
            'dbuser' => $amp_conf['AMPDBUSER'], 'dbpass' => $amp_conf['AMPDBPASS'],
            'dbport' => '3306', 'dbsock' => '/var/lib/mysql/mysql.sock');
        $def_bd_sec = 'sccp';

        $dir = $cnf_int->get('ASTETCDIR');
        $res_conf_sql = ini_get('pdo_mysql.default_socket');
        $res_conf_old = '';
        $res_conf = '';
        $ext_conf = '';

        foreach ($backup_ext as $fext) {
            if (file_exists($dir . '/extconfig' . $fext)) {
                $ext_conf = $cnf_read->getConfig('extconfig' . $fext);
                if (!empty($ext_conf['settings']['sccpdevice'])) {
                    // Add chek line
                    if (strtolower($ext_conf['settings']['sccpdevice']) == strtolower($def_config['sccpdevice'])){
                        $res['sccpdevice'] = 'OK';
                        $res['extconfigfile'] = 'extconfig'. $fext;
                    } else {
                        $res['sccpdevice'] = 'Error in line sccpdevice '. $res['sccpdevice'];
                    }
                }
                if (!empty($ext_conf['settings']['sccpline'])) {
                    if (strtolower($ext_conf['settings']['sccpline']) == strtolower($def_config['sccpline'])){
                        $res['sccpline'] = 'OK';
                    } else {$res['sccpline'] = 'Error in line sccpline';}
                }
            }
        }

        $res['extconfig'] = 'OK';

        if (empty($res['sccpdevice'])) {
            $res['extconfig'] = ' Options "Sccpdevice" not config ';
        }
        if (empty($res['sccpline'])) {
            $res['extconfig'] = ' Options "Sccpline" not config ';
        }

        if (empty($res['extconfigfile'])) {
            $res['extconfig'] = 'File extconfig.conf not exist';
        }


        if (!empty($res_conf_sql)) {
            if (file_exists($res_conf_sql)) {
                $def_bd_config['dbsock'] = $res_conf_sql;
            }
        }
        if (file_exists($dir . '/res_mysql.conf')) {
            $res_conf = $cnf_read->getConfig('res_mysql.conf');
            if (empty($res_conf[$realm])) {
                $res['mysqlconfig'] = 'Not Config in file: res_mysql.conf';
            } else {
                if ($res_conf[$realm]['dbsock'] != $def_bd_config['dbsock']) {
                    $res['mysqlconfig'] = 'Mysql Soket Error in file: res_mysql.conf';
                }
            }
            if (empty($res['mysqlconfig'])) {
                $res['mysqlconfig'] = 'OK';
            }
        }
        
        if (file_exists($dir . '/res_config_mysql.conf')) {
            $res_conf = $cnf_read->getConfig('res_config_mysql.conf');
            if (empty($res_conf[$realm])) {
                $res['mysqlconfig'] = 'Not Config in file: res_config_mysql.conf';
            } else {
                if ($res_conf[$realm]['dbsock'] != $def_bd_config['dbsock']) {
                    $res['mysqlconfig'] = 'Mysql Soket Error in file: res_config_mysql.conf';
                }
            }
            if (empty($res['mysqlconfig'])) {
                $res['mysqlconfig'] = 'OK';
            }
        }
        if (empty($res['mysqlconfig'])) {
            $res['mysqlconfig'] = 'Realtime Error: not found  res_config_mysql.conf or res_mysql.conf configutation on the path :' . $dir;
        }
        return $res;
    }

}
