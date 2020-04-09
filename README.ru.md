## Welcome to Develop Chan_SCCP GUI Manager for FreePBX
| [English](README.md) | [Russian](README.ru.md) | [OLD Stable Release](https://github.com/PhantomVl/sccp_manager/tree/master)

![Gif](https://github.com/PhantomVl/sccp_manager/raw/develop/.dok/image/Demo_1s5.gif)

  * [Installation](https://github.com/PhantomVl/sccp_manager#installation)
  * [Prerequisites](https://github.com/PhantomVl/sccp_manager#prerequisites)
  * [Links](https://github.com/PhantomVl/sccp_manager#link)
  * [Wiki](https://github.com/PhantomVl/sccp_manager/wiki)
  
## Link

[![Download Sccp-Mamager](https://img.shields.io/badge/SccpGUI-build-ff69b4.svg)](https://github.com/PhantomVl/sccp_manager/archive/master.zip)и традиционно добалены новые баги 
[![Download Chan-SCCP channel driver for Asterisk](https://img.shields.io/sourceforge/dt/chan-sccp-b.svg)](https://github.com/chan-sccp/chan-sccp/releases/latest)
[![Chan-SCCP Documentation] (https://img.shields.io/badge/docs-wiki-blue.svg)](https://github.com/chan-sccp/chan-sccp/wiki)

### История
Корни идея создания этого проекта лежат в несовершенстве уже существующего и заброшенного проекта.
Для желающих попробовать себя на просторах программирования ссылка на проект (https://github.com/Cynjut/SCCP_Manager).

### Кому это надо...
Ну в первую очередь для себя, а заодно и для тех у кого есть куча телефонного хлама от компании Cisco. 
Если вы планируете использовать Aserisk + FreePBX, то я надеюсь, что данный модуль существенно упростит управление и настройки телефонами от Cisco.
В интернете, существует замечательный проект (IMHO) который интегрирует проприетарный протокол Cisco в Asterisk, конечно он пока далек от идеала, 
но все же это замечательная замена серверам CCME, СCM, СUСM !
Ну я совершенно не представляю себе, сколько времени данный проект будет поддерживаться.

### Если ты еще с нами ...

Как я говорил выше, это дополнение к (Aserisk + FreePBX), но нам еще потребуется :
 1. У меня не получилось поставить добиться работы с дисками Aserisk и FreePBX - собираем из исходников 
 1.1. Замечательная копания freepbx. Теперь с SNG7-PBX-64bit-1805 все работает !
 2. Mysql (Maria)
 3. Драйвер протокола SCCP страница (https://github.com/chan-sccp/chan-sccp/)
 4. Ну и этот модуль.

### Вжно! В этой ветке лежат самые последне нововведения и обновления, и самые последние БАГИ ! 
    Пользуйся и наслождайся. Так же не забывай писать нам об ошибках, которые ты нашел ! 
    Это очень нам поможет, Я с радостью исправлю то что ты нашел и добалю новых.

### Wiki - Основные Инструкции по настройке 
Вся документация лежит на Вики [![SCCP Manager Wiki](https://img.shields.io/badge/Wiki-new-blue.svg)](https://github.com/PhantomVl/sccp_manager/wiki)

### Prerequisites - как говориться все, что хуже этого возможно работать тоже будет .... но только вопрос как ?
Make sure you have the following installed on your system:
- c-compiler: (мне то он не нужен, но как собирать все остальное ?)
  - gcc >= 4.4  (note: older not supported, higher advised)
  - clang >= 3.6  (note: older not supported, higher advised)
- gnu make
- pbx:
  - asterisk >= 1.8 (absolute minimum & not recommended)
  - asterisk >= 13.7 or asterisk >= 14.0 or asterisk >= 15.0 (Тестировалось на стендах)
- gui:
  - freepbx >= 13.0.192 (http://wiki.freepbx.org/display/FOP/Install+FreePBX)
- standard posix compatible applications like sed, awk, tr

### Installation Очень короткая инструкция
###### [Полная версия инструкции] (https://github.com/PhantomVl/sccp_manager/wiki/step-by-step-instlation)
 - Chan_SCCP module 4.3.1 (or later) [See our WIKI] (https://github.com/chan-sccp/chan-sccp/wiki/Building-and-Installation-Guide)
>    git clone https://github.com/chan-sccp/chan-sccp.git
>    git checkout develop

   - Важно ! **_Собираем с флагами и создаем БД для работы:_**
>     ./configure ./configure  --enable-conference --enable-advanced-functions --enable-distributed-devicestate --enable-video
>     mysql -u root asterisk < mysql-v5_enum.sql

- Настраиваем TFTP Server, он нужен для телефонов /tftpboot/ [See our WIKI] (https://github.com/chan-sccp/chan-sccp/wiki/setup-tftp-service)
- Настраиваем DHCP serve, как ни странно он тоже нужен [See our WIKI] (https://github.com/chan-sccp/chan-sccp/wiki/setup-dhcp-service)

- Установка модуля
>     cd /var/www/html/admin/modules/
>     git clone https://github.com/PhantomVl/sccp_manager.git
>     cd /var/www/html/admin/modules/sccp_manager/
>     git checkout develop
>     amportal chown
>     amportal a ma install sccp_manager
    
- Настройка модуля
    1. Открываем "SCCP Connectivity" -> "Server Config" и делаем все, что вам нужно.
    2. Жмем "Сохранить"  ..... И Все ! Дальше настройки в рамках концепции Freepbx.

- Обновление модуля
>     cd /var/www/html/admin/modules/sccp_manager/
>     git fetch
>     git pull
>     git checkout extension_mobility
>       or
>     git checkout develop


### Важно:   
   - !!! Если это это проект не заработал на твоей системе - переключись на ветку мастер [master](https://github.com/PhantomVl/sccp_manager/tree/master) 
     !!! Но есть ограничение - ветка master не поддерживает изменения в chan-sccp сделаные после октября 2018 г.
   - Желательно иметь Firmware телефонов Cisco, языковые пакеты ну всякое разное.
   - Возможно, ты найдешь, то что ищешь, в проекте  (https://github.com/dkgroot/provision_sccp)
   - Если что-то не так [Wiki GUI] (https://github.com/PhantomVl/sccp_manager), [Wiki chan-sccp] (https://github.com/chan-sccp/chan-sccp/wiki),
[Wiki FreePbx] (https://wiki.freepbx.org/display/FOP/Install+FreePBX)


