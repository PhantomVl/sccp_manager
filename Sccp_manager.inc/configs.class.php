<?php
/**
 * 
 */
namespace FreePBX\modules\Sccp_manager;
class configs {
//        protected $freepbx;
//        protected $database;

        public function __construct() {
//                $this->freepbx = $freepbx;
//                $this->database = $freepbx->Database;
        }
        public function getConfig($id = '', $index = '') {
            switch ($id) {
                case 'keyset':
                    if (empty($index)) {
                        return $this->keysetdefault;
                    } else {
                        if (isset($this->keysetdefault[$index])) {
                            return $this->keysetdefault[$index];
                        } else {
                            return array('');
                        }
                    }
                    break;
                case 'sccp_lang':
                    return $this->cisco_language;
                    break;

                default:
                    return array('noId');
                    break;
            }
            return array();
        }
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
    
        
}