# ToDo – Aufgabenmodul für HumHub

ToDo ist ein Space-basiertes HumHub-Modul für Aufgaben, Checklisten und Pendenzen.
Aufgaben können Personen und Aufgabenlisten zugeordnet, kommentiert, terminiert und
über einen einfachen Status-Workflow bearbeitet werden.

## Voraussetzungen

- HumHub 1.18 oder neuer
- PHP 8.2 oder neuer
- aktivierter stündlicher HumHub-Cronjob für Erinnerungen
- optional: HumHub-Kalendermodul für die Kalendersynchronisation

## Funktionen

- Aufgaben pro Space erstellen, bearbeiten und löschen
- Status `Offen`, `In Bearbeitung` und `Geschlossen`
- Prioritäten und Fälligkeitsdaten
- mehrere zuständige Personen pro Aufgabe
- Checklisten mit mehreren Zuständigen
- frei verwaltbare und sortierbare Aufgabenlisten
- Kommentare, Dateianhänge und Benachrichtigungen
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

## Konfiguration

Globale Einstellungen befinden sich in der Modulkonfiguration im
Administrationsbereich. Space-spezifische Einstellungen und Berechtigungen werden im
jeweiligen Space verwaltet.

Für Erinnerungen verwendet das Modul den stündlichen HumHub-Cronlauf:

```bash
php protected/yii cron/run
```

## Version

Aktuelle Modulversion: **1.10.0**. Änderungen sind im [CHANGELOG.md](CHANGELOG.md)
dokumentiert.

## Mitwirken und Sicherheit

Hinweise für Beiträge stehen in [CONTRIBUTING.md](CONTRIBUTING.md). Sicherheitsprobleme
bitte gemäss [SECURITY.md](SECURITY.md) vertraulich melden.

## Lizenz

Dieses Projekt steht unter der GNU Affero General Public License, Version 3 oder
neuer. Einzelheiten enthält die Datei [LICENSE](LICENSE).
