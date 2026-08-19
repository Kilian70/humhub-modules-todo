# Changelog

## 1.8.5 - 2026-08-19

- Warnung beim Schliessen einer Aufgabe mit offenen Checklistenpunkten
- Die Warnung zeigt die Anzahl der noch offenen Checklistenpunkte
- «Abbrechen» lässt die Aufgabe im bisherigen Status
- «OK» schliesst die Aufgabe bewusst trotz offener Checklistenpunkte
- Serverseitige Prüfung verhindert ein Umgehen der Warnung über einen manipulierten direkten Request
- Abschlussprotokoll mit geschlossen von / geschlossen am bleibt erhalten

## 1.8.4 - 2026-08-19

- Darstellung der direkten Bearbeitung in der Detailansicht korrigiert
- Status und Priorität verwenden HumHub-/Bootstrap-kompatible Dropdown-Felder
- Fälligkeitsdatum verwendet ein deutlich sichtbares kompaktes Datumsfeld
- Kleine Bearbeiten-Symbole markieren die direkt änderbaren Felder
- Sofortiges Speichern und Berechtigungsprüfung aus 1.8.3 bleiben unverändert

## 1.8.3 - 2026-08-19

- Status direkt in der Detailansicht änderbar
- Priorität direkt in der Detailansicht änderbar
- Fälligkeitsdatum direkt in der Detailansicht änderbar
- Änderungen werden unmittelbar per POST/CSRF gespeichert
- Direkte Änderungen benötigen weiterhin das ToDo-Bearbeitungsrecht
- Status «Geschlossen» protokolliert weiterhin geschlossen von / geschlossen am
- Wird eine geschlossene Aufgabe wieder geöffnet, werden die Abschlussdaten zurückgesetzt
- Änderungen am Fälligkeitsdatum laufen weiterhin durch das Task-Modell und damit durch die bestehende Kalender-Synchronisation

## 1.8.2 - 2026-08-19

- Status kann direkt in der Aufgaben-Detailansicht geändert werden
- Statuswechsel verwendet Offen / In Bearbeitung / Geschlossen
- Beim direkten Wechsel auf «Geschlossen» werden geschlossen von und geschlossen am automatisch gesetzt
- Beim Wiederöffnen werden die Abschlussdaten zurückgesetzt
- Direkter Statuswechsel ist POST/CSRF-geschützt und benötigt das ToDo-Bearbeitungsrecht
- Datei-Upload wurde vor den Kommunikationsbereich verschoben
- Bestehende Dateien und Fotovorschauen stehen damit zusammen mit «Datei hinzufügen» vor der Kommunikation

## 1.8.1 - 2026-08-19

- Ganze Aufgabenzeile in der Übersicht ist anklickbar und öffnet die Detailansicht
- Bearbeiten- und Löschen-Buttons bleiben separat funktionsfähig
- Klicks auf Aktionsbuttons werden nicht an die Aufgabenzeile weitergegeben
- Tastaturbedienung mit Enter/Leertaste ergänzt
- Dezenter Hover- und Fokus-Effekt für klickbare Aufgabenzeilen

## 1.8.0 - 2026-08-19

- Neuer Aufgabenstatus: Offen / In Bearbeitung / Geschlossen
- Bestehende erledigte Aufgaben werden bei der Migration auf «Geschlossen» übernommen
- Beim erstmaligen Schliessen werden Benutzer und Zeitpunkt gespeichert
- Wird eine geschlossene Aufgabe wieder geöffnet, werden die Abschlussdaten zurückgesetzt
- Direktes Abhaken einer ganzen Aufgabe in der Übersicht entfernt
- Status wird kompakt in Übersicht und Detailansicht angezeigt
- Neue Space-Seite «Aufgabenlisten verwalten»
- Aufgabenlisten dort anlegen, umbenennen, Farbe ändern, hoch/runter verschieben und löschen
- Beim Löschen einer Aufgabenliste bleiben die Aufgaben erhalten und werden «Unsortiert»
- Listen-Sortierbuttons aus der normalen Aufgabenübersicht entfernt

## 1.7.3 - 2026-08-19

- Sichtbarkeit der ↑/↓-Buttons für Aufgabenlisten korrigiert
- Verschieben verwendet jetzt echte POST-Formularbuttons statt data-method-Links
- Buttons verwenden HumHub/Bootstrap-kompatible btn-default-Klasse
- Erste Liste zeigt nur ↓, mittlere Listen ↑/↓, letzte Liste nur ↑
- «Unsortiert» bleibt ohne Verschiebepfeile

## 1.7.2 - 2026-08-19

- Aufgabenlisten können in der gruppierten Übersicht mit ↑/↓ neu angeordnet werden
- Reihenfolge wird pro Space über das bestehende sort_order-Feld gespeichert
- «Unsortiert» bleibt fest an erster Stelle
- Auch «Unsortiert» hat jetzt einen + Button für eine neue Aufgabe ohne Liste
- Verschieben ist POST/CSRF-geschützt und erfordert das ToDo-Bearbeitungsrecht
- Bereits vorhandene anklickbare Aufgabentitel und Überfällig-Markierung bleiben erhalten

## 1.7.1 - 2026-08-19

- Aufgabenlistenfeld zeigt beim Anklicken alle bestehenden Listen des aktuellen Spaces
- Bestehende Listen können direkt ausgewählt und während der Eingabe durchsucht/gefiltert werden
- Listenfarbe wird in der Auswahl angezeigt
- Bei einem neuen Namen erscheint «Aufgabenliste ‹Name› erstellen»
- Beim Bearbeiten bleibt die aktuelle Aufgabenliste im Eingabefeld vorausgewählt
- Inline-Neuanlage aus 1.7.0 bleibt erhalten; die neue Liste wird beim Speichern angelegt

## 1.7.0 - 2026-08-19

- Aufgabenlisten als eigene, Space-bezogene Struktur eingeführt
- Aufgaben können optional einer Aufgabenliste zugeordnet werden
- Neue Aufgabenlisten können direkt beim Erstellen/Bearbeiten einer Aufgabe durch Eingabe eines neuen Namens angelegt werden
- Jede Aufgabenliste erhält automatisch eine gut unterscheidbare Farbe
- Neue Standardansicht «Nach Aufgabenliste» mit kompakten auf-/zuklappbaren Gruppen
- Gruppenüberschrift zeigt Farbe und Anzahl Aufgaben
- Plus-Button in jeder Gruppe erstellt eine Aufgabe mit bereits vorbelegter Aufgabenliste
- «Unsortiert» bleibt für Aufgaben ohne Liste erhalten
- Bestehende Ansichten «Nach Datum» und «Nach Zuständig» bleiben verfügbar

## 1.6.1 - 2026-08-19

- Space-Widget «ToDo – Aufgaben» wird nicht mehr global administriert
- Jeder Space verwaltet sein ToDo-Widget selbst
- Space-Admins können das Widget pro Space ein-/ausschalten
- Position und Anzahl Einträge sind pro Space separat einstellbar
- Dashboard-Widget «Meine ToDos» bleibt eine globale Moduleinstellung
- Space-Einstellungen werden über die HumHub Content-Container-Einstellungen gespeichert

## 1.6.0 - 2026-08-19

- Dashboard-Widget «Meine ToDos»
- Space-Widget «ToDo – Aufgaben»
- Widgets zeigen standardmässig maximal 5 offene Aufgaben
- Überfällige Aufgaben werden markiert
- Sortierung nach Fälligkeit; Aufgaben ohne Datum folgen danach
- Dashboard zeigt persönliche ToDos aus mehreren Spaces
- Eigene globale Übersicht «Meine ToDos» mit «Zeige alle»
- Modul-Adminbereich für Aktivierung, Position (sortOrder) und Anzahl Einträge beider Widgets
- Space- und ToDo-Sichtbarkeitsrechte werden berücksichtigt

## 1.5.0 - 2026-08-19

- Neuer Bereich «Kommunikation» direkt in jeder Aufgabe
- Verwendung des nativen HumHub-Kommentarsystems statt eines separaten Chats
- Antworten, @Erwähnungen und HumHub-Kommentarbenachrichtigungen bleiben verfügbar
- Zuständige Personen erhalten zusätzlich eine ToDo-Benachrichtigung bei neuen Nachrichten
- Kommentare respektieren die normalen HumHub-Kommentar- und Space-Berechtigungen
- Beim Löschen der Aufgabe werden Kommentare über HumHubs bestehende Content-Integration bereinigt

## 1.4.3 - 2026-08-19

- «Datei hinzufügen» öffnet direkt in der Aufgabenansicht einen kompakten Upload-Dialog
- Kein Umweg mehr über «Aufgabe bearbeiten»
- Pro Upload kann optional direkt ein Titel bzw. eine kurze Beschreibung erfasst werden
- Ohne Titel wird weiterhin automatisch der Dateiname ohne Endung verwendet
- Upload verwendet dieselben Dateitypen, Berechtigungen und CSRF-Prüfungen wie das bestehende Aufgabenformular

## 1.4.2 - 2026-08-19

- Bildanhänge werden direkt in der Aufgabe als kompakte Vorschau angezeigt
- Klick auf die Vorschau öffnet das Originalbild über HumHubs geschützten Datei-Endpunkt
- Bilder werden lazy geladen, damit Aufgaben mit mehreren Fotos nicht unnötig schwer werden
- Jeder Anhang kann einen eigenen Titel bzw. eine kurze Beschreibung erhalten
- Der Original-Dateiname bleibt zusätzlich sichtbar
- Leerer Titel fällt automatisch auf den Dateinamen ohne Endung zurück
- Dateititel können direkt in der Aufgabenansicht bearbeitet werden
- Titeländerungen sind POST/CSRF-geschützt und verwenden dieselben Bearbeitungsrechte wie das Löschen von Anhängen

## 1.4.1 - 2026-08-19

- Teilnahmefunktion für automatisch aus ToDo erzeugte Kalendereinträge deaktiviert
- Keine Buttons «Teilnehmen», «Vielleicht» oder «Ablehnen» bei ToDo-Terminen
- Gilt für Hauptaufgaben und Checklistenpunkte
- Bereits verknüpfte ToDo-Kalendereinträge werden beim nächsten Synchronisieren entsprechend aktualisiert

## 1.4.0 - 2026-08-19

- Optionale Integration mit dem offiziellen HumHub-Kalendermodul
- Fälligkeit einer Hauptaufgabe kann optional als ganztägiger Kalendereintrag synchronisiert werden
- Termin eines Checklistenpunkts kann ebenfalls optional synchronisiert werden
- Änderungen an Titel oder Datum aktualisieren den verknüpften Kalendereintrag
- Entfernen des Termins oder Deaktivieren der Kalenderoption entfernt den verknüpften Eintrag
- Beim Löschen einer Aufgabe bzw. eines Checklistenpunkts wird der verknüpfte Kalendereintrag bereinigt
- Kalenderfelder werden nur angezeigt, wenn das Kalender-Modul im aktuellen Space aktiviert ist
- Keine harte Abhängigkeit vom Kalender-Modul; ToDo funktioniert weiterhin eigenständig

## 1.3.1 - 2026-08-19

- HumHub-eigenen `UserPickerField` für Zuständigkeiten in der Checkliste eingebaut
- Mehrere zuständige Space-Mitglieder pro Checklistenpunkt möglich
- Bestehende Einzel-Zuständigkeiten werden beim Update übernommen
- Serverseitige Space-Mitgliedschaftsprüfung bleibt bestehen
- Einzelne Checklistenpunkte kompakt optisch getrennt
- Erledigte Punkte dezent grün hinterlegt
- Abstände und Innenränder reduziert, damit die Checkliste kompakt bleibt

## 1.3.0 - 2026-08-19

- Optionaler Termin pro Checklistenpunkt
- Optionale Zuständigkeit pro Checklistenpunkt, beschränkt auf bestätigte Mitglieder des aktuellen Spaces
- Automatische Speicherung von „Erledigt von“ und „Erledigt am“
- Beim Wiederöffnen werden die Erledigungsangaben zurückgesetzt
- Titel, Termin und Zuständigkeit bestehender Punkte können bearbeitet werden

## 1.2.0 - 2026-08-19

- Dateianhänge auf das native HumHub File-/Attachment-System umgestellt
- Neue Uploads werden als `humhub\modules\file\models\File` gespeichert und an die Aufgabe gebunden
- Downloads verwenden die HumHub-Datei-URL mit Hash und HumHub-Storage
- Direkte Datei-Downloads berücksichtigen zusätzlich das ToDo-Recht `ViewTasks`
- Dateilöschung prüft Space, Aufgabe und Datei-Zuordnung per GUID
- Bestehende Dateien aus `@runtime/todo-files` werden beim Update automatisch in HumHub-Dateien migriert
- Upload-Feld auch beim Erstellen einer Aufgabe ergänzt
- HumHub `createPermission`/`managePermission` an die ToDo-Berechtigungen angebunden

## 1.1.1 - Security hardening

- Enforce `ViewTasks` permission in the module search controller and global search provider.
- Validate notification and reminder recipients against HumHub content visibility and `ViewTasks`.
- Tighten task validation for title length, date, priority and status.
- Escape dynamic task/user values in HTML notification output.

## 1.1.0 - 2026-08-19

- Checkliste pro Aufgabe ergänzt
- Checklistenpunkte können abgehakt, wieder geöffnet, nach oben/unten verschoben und gelöscht werden
- Reihenfolge der Checkliste wird persistent gespeichert
- Schreibende Checklisten-Aktionen auf POST beschränkt
- Cron-Reminder nur noch einmal über `config.php` registriert
- Reminder aktualisiert nur noch `reminder_sent_at` statt die gesamte Aufgabe erneut zu speichern
- Notification-Kategorie über Modul-Event registriert
- erste Bootstrap-5-Anpassungen (`btn-outline-secondary` statt `btn-default`)
- Modulversion auf 1.1.0 erhöht
- `composer.json` für PHP 8.2 / HumHub-Modultyp ergänzt
