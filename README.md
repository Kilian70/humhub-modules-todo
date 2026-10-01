# ToDo – Aufgabenmodul für HumHub

<p align="center">
  <img src="resources/module_image.png" alt="ToDo-Modulbild" width="180">
</p>

ToDo ist ein Space-basiertes HumHub-Modul für Aufgaben, Checklisten und Pendenzen.
Aufgaben können Personen und Aufgabenlisten zugeordnet, kommentiert, terminiert und
über einen einfachen Status-Workflow bearbeitet werden.

> **Hinweis:** Dies ist ein unabhängig entwickeltes Community-Modul und kein
> offizielles Modul des HumHub-Projekts.

## Oberfläche

Das Modul bietet eine kompakte Listenansicht und ein Kanban-Board mit den Spalten
**Offen**, **In Bearbeitung** und **Geschlossen**. Aufgaben lassen sich per
Drag-and-drop oder über eine barrierefreie Statusauswahl verschieben. Umfangreiche
Verwaltungs- und Exportfunktionen sind platzsparend in Menüs zusammengefasst.

Die persönliche Übersicht **Meine ToDos** bündelt Aufgaben aus allen sichtbaren
Spaces. Suche, Zuständigkeit, Space, Status, Priorität sowie Ansichten für fällige,
blockierte und untergeordnete Aufgaben helfen beim Eingrenzen großer Datenmengen.

## Voraussetzungen

- HumHub 1.18 oder neuer
- PHP 8.2 oder neuer
- aktivierter stündlicher HumHub-Cronjob für Erinnerungen
- optional: HumHub-Kalendermodul für die Kalendersynchronisation

## Funktionen

- Aufgaben pro Space erstellen, bearbeiten und löschen
- Status `Offen`, `In Bearbeitung` und `Geschlossen`
- Prioritäten und Fälligkeitsdaten
- wiederkehrende Aufgaben mit Intervall und optionalem Enddatum
- vollständige Unteraufgaben mit eigenem Status, Termin und Zuständigen
- Aufgabenabhängigkeiten mit sichtbarer Blockierung
- automatische Benachrichtigung, sobald eine blockierte Aufgabe freigegeben wird
- Aufgaben samt Zuständigen und Checkliste duplizieren oder als Vorlage speichern
- persönliche Aufgabenübersicht über alle sichtbaren Spaces mit Suche und Filtern
- skalierbare Seitennavigation in der persönlichen Aufgabenübersicht
- persönlicher Hauptmenüpunkt „Meine ToDos“ mit wählbarer Sichtbarkeit und Position
- zentrale deutsche und englische Übersetzungen für die Hauptoberflächen
- mehrere zuständige Personen pro Aufgabe
- Checklisten mit mehreren Zuständigen
- frei verwaltbare und sortierbare Aufgabenlisten
- Kommentare, Dateianhänge mit verständlicher Größenprüfung und Benachrichtigungen
- Dashboard- und Space-Widgets
- Suche nach Aufgaben
- Erinnerungen über den stündlichen HumHub-Cronjob
- optionale Synchronisation mit dem HumHub-Kalender
- Berechtigungen für Anzeigen, Erstellen, Bearbeiten und Löschen

## Installation

1. Dieses Repository nach `protected/modules/todo` in der HumHub-Installation kopieren
   oder klonen.
2. Im HumHub-Administrationsbereich **Administration → Module** öffnen.
3. Das Modul **ToDo** aktivieren.
4. Das Modul in den gewünschten Spaces aktivieren und die Berechtigungen festlegen.
5. Sicherstellen, dass der reguläre HumHub-Cronjob ausgeführt wird.

Beispiel zum Klonen:

```bash
git clone https://github.com/Kilian70/humhub-modules-todo.git protected/modules/todo
```

Da dieses Repository privat ist, benötigt Git für den Zugriff eine entsprechende
GitHub-Berechtigung.

## Aktualisierung

Vor einer Aktualisierung sollte ein Backup der Datenbank und der HumHub-Dateien
erstellt werden. Anschliessend den Modulordner aktualisieren; HumHub führt die
enthaltenen Migrationen beim Modul-Upgrade aus.

## Geprüfte Kompatibilität

- Installation und Aufgabenverwaltung unter HumHub 1.18
- Installation und Aufgabenverwaltung unter HumHub 1.19
- Aktivierung, Deaktivierung und vollständige Deinstallation
- helle und dunkle HumHub-Darstellung
- Desktop- und mobile Darstellung
- Belastungstest mit 10.000 Aufgaben, 80.000 Checklistenpunkten, 50.000 Kommentaren,
  20.000 Zuständigkeiten und 30.000 simulierten Anhängen

Die Ansichten verwenden bewusst die von HumHub 1.18 und 1.19 bereitgestellten
Kompatibilitätsklassen. Eine ausschliesslich auf den aktuellen Bootstrap-Stand des
HumHub-`develop`-Zweigs zugeschnittene Oberfläche würde die Unterstützung dieser
beiden stabilen HumHub-Versionen beeinträchtigen.

## Entwicklung und Tests

Die schnelle Modulprüfung kann im Modulverzeichnis ausgeführt werden:

```bash
bash tests/check.sh
```

Zusätzlich steht eine Codeception-Unit-Suite im offiziellen HumHub-Testlayout zur
Verfügung. Mit einer vorhandenen HumHub-Installation wird sie beispielsweise so
ausgeführt:

```bash
php /pfad/zu/humhub/protected/vendor/bin/codecept run unit -c tests/codeception.yml
```

Die GitHub-Actions prüfen den Modulcode sowie die Kompatibilität mit HumHub 1.18
und 1.19 regelmäßig.

## Konfiguration

Globale Einstellungen befinden sich in der Modulkonfiguration im
Administrationsbereich. Space-spezifische Einstellungen und Berechtigungen werden im
jeweiligen Space verwaltet.

Für Erinnerungen verwendet das Modul den stündlichen HumHub-Cronlauf:

```bash
php protected/yii cron/run
```

## Version

Aktuelle Modulversion: **1.41.0**. Änderungen sind im [CHANGELOG.md](CHANGELOG.md)
dokumentiert.

## Mitwirken und Sicherheit

Hinweise für Beiträge stehen in [CONTRIBUTING.md](CONTRIBUTING.md). Sicherheitsprobleme
bitte gemäss [SECURITY.md](SECURITY.md) vertraulich melden.

## Lizenz

Dieses Projekt steht unter der GNU Affero General Public License, Version 3 oder
neuer. Einzelheiten enthält die Datei [LICENSE](LICENSE).
