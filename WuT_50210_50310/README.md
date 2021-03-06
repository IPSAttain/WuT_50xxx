# WuT_50210_50310
Beschreibung des Moduls.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* 

### 2. Vorraussetzungen

* IP-Symcon ab Version 5.0
* Den W&T im Modus "TCP-Server Mode (Standard Mode)" verwenden.
* Standardmäßig befindet sich der E/A Com-Server-Port im Mode TCP-Server. 
Es sind keine weiteren Einstellungen notwendig. Von jeder TCP/IP-Station im Netz kann nun eine Verbindung zum ComServer-Port aufgebaut werden. 
Es kann immer nur eine Verbindung gleichzeitig bestehen.

### 3. Software-Installation

* Über den Module Store das 'WuT_50210_50310'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'WuT_50210_50310'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name     | Beschreibung
-------- | ------------------
Port     | Port für Client Socket
Ip Adresse | IP Adresse für Client Socket

### 5. Statusvariablen und Profile

Jeweils 12 Variablen für Ein und Ausgänge werden automatisch angelegt.

#### Statusvariablen

Name   | Typ     | Beschreibung
------ | ------- | ------------
Output | bool    | Darstellung der Ausgangsbits
Input  | bool    | Darstellung des Eingangsbits

#### Profile

Name   | Typ
------ | -------
~Switch | Bool
       |

### 6. WebFront

Die Funktionalität, die das Modul im WebFront bietet.

### 7. PHP-Befehlsreferenz

Name   | Typ
------ | -------
WuT_Initialize | Sendet den Status aller Ausgangs-Variablen zum E/A Device. Es werden Änderungen an den Eingängen abonniert.
RequestAction | Für Ausgangs Status Variablen. Hiermit kann die Änderungen eines Ausgangs erwirkt werden. 
