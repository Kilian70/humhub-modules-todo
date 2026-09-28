# Mitwirken

Beiträge zum ToDo-Modul sind willkommen.

## Ablauf

1. Für die Änderung einen eigenen Branch erstellen.
2. Änderungen klein und nachvollziehbar halten.
3. Neue oder geänderte PHP-Dateien mit `php -l` prüfen.
4. Änderungen in einer HumHub-1.18-Installation testen.
5. Benutzerrelevante Änderungen im `CHANGELOG.md` ergänzen.
6. Einen Pull Request mit Beschreibung und Testhinweisen erstellen.

## Sichere Deinstallationstests

Für Installations- und Deinstallationstests muss das Modul als separate Kopie oder
in einem eigenen Test-Checkout unter `protected/modules/todo` liegen. Dafür keinen
Symlink auf den echten Entwicklungsordner verwenden: HumHub kann beim Entfernen
des Moduls dem Symlink folgen und dadurch den verknüpften Ordner löschen.

Bitte keine Zugangsdaten, personenbezogenen Daten, Datenbankexporte oder Dateien aus
produktiven HumHub-Installationen committen.

Mit einem Beitrag erklärst du dich damit einverstanden, dass er unter der Lizenz des
Projekts veröffentlicht wird.
