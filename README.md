## Welcome to Chan_SCCP GUI Manager for FreePBX

![Gif](https://github.com/PhantomVl/sccp_manager/raw/develop/.dok/image/Demo_1s5.gif)

  * [Installation](https://github.com/PhantomVl/sccp_manager#installation)
  * [Prerequisites](https://github.com/PhantomVl/sccp_manager#prerequisites)
  * [Links](https://github.com/PhantomVl/sccp_manager#link)
  * [Wiki](https://github.com/PhantomVl/sccp_manager/wiki)
  
## Link

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

### Wiki
You can find more information and documentation on our [![SCCP Manager Wiki](https://img.shields.io/badge/Wiki-new-blue.svg)](https://github.com/PhantomVl/sccp_manager/wiki)

## Prerequisites
Make sure you have the following installed on your system:
- c-compiler:
  - gcc >= 4.4  (note: older not supported, higher advised)
  - clang >= 3.6  (note: older not supported, higher advised)
- gnu make
- pbx:
  - asterisk >= 1.8 (absolute minimum & not recomended)
  - asterisk >= 13.7 or asterisk >= 14.0 recommended
- gui:
  - freepbx >= 13.0.192
- standard posix compatible applications like sed, awk, tr

### Requirements
- Chan_SCCP module 4.3.0 (or later) channel driver for Asterisk: [See our WIKI](https://github.com/chan-sccp/chan-sccp/wiki/Building-and-Installation-Guide)
  - expected configure flags: 
    ```./configure --enable-conference --enable-advanced-functions --enable-distributed-devicestate```
  - Creating mysql DB from sorce 
    ```mysql -u root asterisk < mysql-v5_enum.sql```

- TFTP Server running under (recomended) /tftpboot/ [See our WIKI](https://github.com/chan-sccp/chan-sccp/wiki/setup-tftp-service)
  - You will need the phone settings templates. You can use the templates taken from the distribution "chan-sccp" 
    ```cp /usr/src/chan-sccp/conf/tftp/\*.xml\* /tftpboot/templates/```

- configure DHCP server [See our WIKI](https://github.com/chan-sccp/chan-sccp/wiki/setup-dhcp-service)

### Setup
- [Setting up a FreePBX system](http://wiki.freepbx.org/display/FOP/Install+FreePBX)
- [Setting up Chan-Sccp](https://github.com/chan-sccp/chan-sccp/wiki/How-to-setup-the-chan_sccp-Module)
- The sccp_manager module will automatically setup and configure asterisk realtime database for chan-sccp.
  For more information about realtime [See chan-sccp wiki](https://github.com/chan-sccp/chan-sccp/wiki/Realtime-Configuration).

## Installation

1. Download module into your local system. (/var/www/html/admin/modules/)
2. Goto FreePBX Admin -> Module Admin.
3. Click Upload Modules.
4. Browse to the location of the module on your computer and select Upload.
5. Click Manage Local Modules.
6. Find and click SCCP Manager. Check Install. Click Process button.
7. Confirm installation.
8. Close Status window.
9. Apply Config to FreePBX.

### Module update to latest state
1. Goto to module into your local system. (/var/www/html/admin/modules/sccp_manager/)

>        cd /var/www/html/admin/modules/sccp_manager/
>        git fetch
>        git pull

### IMPORTANT NOTES: 
- This system assumes/requires that you are using the Asterisk realtime database. If you are not yet using the realtime database, 
you will have to set it up for this module to work ([See](https://github.com/chan-sccp/chan-sccp/wiki/Realtime-Configuration)).
- For the cisco phones to work correctly, they should be provisioned with the latest firmware (v8.1 or higher)
- You can use cisco language profiles (localization) to switch the phones to your locale.

