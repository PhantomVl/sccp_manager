## Welcome to Chan_SCCP GUI Manager for FreePBX

```
 SSSSS      CCCCC     CCCCC   PPPP       
SS   SS    CC    C   CC   CC  PP  P     
SS    S    CC        CC       PPPP      
SS         CC   CC   CC   CC  PP       
 SSS        CCCCC     CCCCC   PP       
   SSS                                 
     SS     GGGGG    UU   UU  IIII
     SS    GG        UU   UU   II           
S    SS    GG  GGG   UU   UU   II
SS   SS    GG    G   UU   UU   II
 SSSSS      GGGGG     UUUUU   IIII
```

### Hot Link 

[![Download Sccp-Mamager](https://img.shields.io/badge/SccpGUI-build-ff69b4.svg)](https://github.com/PhantomVl/sccp_manager/archive/master.zip)
[![Download Chan-SCCP channel driver for Asterisk](https://img.shields.io/sourceforge/dt/chan-sccp-b.svg)](https://github.com/chan-sccp/chan-sccp/releases/latest)
[![Chan-SCCP Documentation](https://img.shields.io/badge/docs-wiki-blue.svg)](https://github.com/chan-sccp/chan-sccp/wiki)

This module has been developed to help IT Staff with their Asterisk-Cisco infrastructure deployment,
providing easily provisioning and managing Cisco IP phones and extensions in a similar way as it does with Cisco CallManager.
The idea of creating a module is borrowed from (https://github.com/Cynjut/SCCP_Manager)
SCCP Manager is free software. Please see the file COPYING for details.

This module has been developed to help IT Staff with their Asterisk-Cisco infrastructure deployment,
providing easily provisioning and managing Cisco IP phones and extensions in a similar way as it does with Cisco CallManager.

This module will suit you if you are planing the to migrate from CallManager to Asterisk (or did it), SCCP-Manager allows you to administer SCCP extensions and a wide range of Cisco phone types (including IP Communicator).
You can control phone buttons (depending on the phone model) assigning multiple lines, speeddials and BLF’s.
And you can use the driver functions "sccp_chain" from the GUI module.


### Prerequisites
Make sure you have the following installed on your system:
- c-compiler:
  - gcc >= 4.4  (note: older not supported, higher advised)
  - clang >= 3.6  (note: older not supported, higher advised)
- gnu make
- pbx:
  - asterisk >= 1.6.2 (absolute minimum)
  - asterisk >= 13.7 or asterisk >= 14.0 recommended
- gui:
  - freepbx >= 13.0.192
- standard posix compatible applications like sed, awk, tr

### Requirements
- Chan_SCCP module 4.3.0 (or later) channel driver for Asterisk: [See our WIKI] (https://github.com/chan-sccp/chan-sccp/wiki/Building-and-Installation-Guide)
  - configure flags ./configure --enable-conference --enable-advanced-functions --enable-distributed-devicestate 

- Real Time cofiguration for Chan_SCCP
  - Creating mysql DB from sorce (mysql -u root asterisk < mysql-v5.sql)

- TFTP Server running under (recomended) /tftpboot/ [See our WIKI] (https://github.com/chan-sccp/chan-sccp/wiki/setup-tftp-service)
  - You will need the phone settings templates. You can use the templates taken from the distribution "chan-sccp" 
    sample: copy /usr/src/chan-sccp/conf/tftp/*.xml* /tftpboot/templets/

- cofigure DHCP server [See our WIKI] (https://github.com/chan-sccp/chan-sccp/wiki/setup-dhcp-service)

### Setting up a FreePBX system
[See our WIKI](http://wiki.freepbx.org/display/FOP/Install+FreePBX)

### Setting up a Sccp 
[See our WIKI] https://github.com/chan-sccp/chan-sccp/wiki/How-to-setup-the-chan_sccp-Module


### Module installation

1. Download module into your local system. (/var/www/html/admin/module/)
2. Goto FreePBX Admin -> Module Admin.
3. Click Upload Modules.
4. Browse to the location of the module on your computer and select Upload.
5. Click Manage Local Modules.
6. Find and click SCCP Manager. Check Install. Click Process button.
7. Confirm installation.
8. Close Status window.
9. Apply Config to FreePBX.

### IMPORTANT NOTE: 
- This system assumes you are using the SCCP Real-time Database. If you are
not yet using the RTD, you will need to set it up for this module to be
effective. 
- For the correct operation of the cisco-phones you will be tempted cisco phone firmware (recomended v 8.1 or later) 
- You can also use cisco language profiles to personalize sysnem and any cisco phones.

