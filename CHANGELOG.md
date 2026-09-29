# Changelog

## 1.32.1 - 2026-09-29

- fällige Erinnerungen in speicherschonenden Stapeln zu je 100 Aufgaben verarbeitet
- unbegrenztes gleichzeitiges Laden aller Erinnerungskandidaten entfernt
- Hintergrundverarbeitung für große Aufgabenbestände abgesichert
- automatische Prüfung der Stapelverarbeitung ergänzt

## 1.32.0 - 2026-09-29

- kombinierte Datenbankindizes für aktive Aufgaben, Fälligkeit und Erinnerungen ergänzt
- automatische Archivierung abgeschlossener Aufgaben beschleunigt
- Filter nach zuständiger Person und Label mit Rückwärtsindizes optimiert
- neue Indizes vollständig update- und rückbaubar umgesetzt

## 1.31.1 - 2026-09-29

- Blockierungsstatus aller sichtbaren Kanban-Karten in einer gemeinsamen Datenbankabfrage geladen
- eine zusätzliche Datenbankabfrage pro Kanban-Karte vermieden
- sichtbaren Aufgabenbereich und Gesamtzahl unter Liste und Kanban angezeigt
- Schutzprüfung gegen die erneute Einführung des Kanban-Abfrageproblems ergänzt

## 1.31.0 - 2026-09-29

- verständliche Größenangaben bei Einzel- und Mehrfachuploads ergänzt
- große Dateien bereits vor dem Absenden im Browser geprüft
- serverseitige Prüfung an HumHub-, PHP- und Anfragegrenzen angeglichen
- zu große Einzeldateien und zu große Gesamtauswahlen mit klaren Meldungen abgewiesen
- Uploaddialog an den hellen und dunklen Modus angepasst
- Grenzwerttests unter HumHub 1.19 mit einer separaten Modulkopie erfolgreich durchgeführt

## 1.30.2 - 2026-09-28

- offenen Status in der Aufgabenliste im dunklen Modus wieder lesbar gemacht
- Kontrast der Zurücksetzen-Schaltflächen in Aufgabenliste und Übersicht korrigiert
- aktive Standardfilter der globalen Aufgabenübersicht an den dunklen Modus angepasst

## 1.30.1 - 2026-09-28

- Kontrast der inaktiven Ansichts-, Verwaltungs-, Export- und Filterschaltflächen im dunklen Modus korrigiert
- Hover- und Tastaturfokuszustände an HumHubs Farbschema angepasst

## 1.30.0 - 2026-09-28

- Kanban-Aufgaben vollständig per Tastatur öffnungs- und verschiebbar gemacht
- dauerhaft erreichbare Statusauswahl als Alternative zu Drag-and-drop ergänzt
- Screenreader-Beschriftungen, Statusmeldungen und sichtbare Fokusmarkierungen verbessert
- Suchfeld, Filter, Symbolschaltflächen und Aufgabenlisten-Auswahl korrekt beschriftet
- Aufgabenlisten-Auswahl mit Pfeiltasten- und Escape-Steuerung ergänzt
- Kontraste benutzerdefinierter Labels automatisch an deren Hintergrundfarbe angepasst
- Kanban-Karten und Auswahllisten an HumHubs hellen und dunklen Modus angepasst
- Kommentarbenachrichtigungen mit der HumHub-1.19-Eigenschaft für den Ersteller korrigiert
- Erinnerungen erreichen nun auch den Aufgabenersteller zuverlässig
- Deinstallation entfernt auch sämtliche ToDo-Benachrichtigungen aus HumHubs zentraler Tabelle

## 1.29.0 - 2026-09-28

- Werkzeugleiste der Aufgabenansicht übersichtlicher gegliedert
- Aufgabenlisten, Labels und Vorlagen im Menü „Verwalten“ zusammengefasst
- CSV sowie Drucken/PDF im Menü „Exportieren“ gebündelt
- Filter vollständig als „Alle Aufgaben“ und „Meine Aufgaben“ deutsch benannt
- Aufgabenliste zusätzlich auf Kanban-Karten eingeblendet
- benötigte Kanban-Beziehungen effizient gemeinsam geladen
- mobile Darstellung an die neuen Dropdown-Menüs angepasst

## 1.28.1 - 2026-09-28

- Erinnerungsverarbeitung gegen gleichzeitig laufende Cron-Prozesse abgesichert
- jede Erinnerungsstufe wird vor dem Einreihen atomar reserviert
- fehlgeschlagene oder empfängerlose Reservierungen werden zuverlässig freigegeben
- parallele Stundenläufe mit 300 fälligen Aufgaben ohne Doppelverarbeitung und Deadlocks getestet
- Konkurrenztests für Wiederholungen und Kanban-Reihenfolgen erfolgreich durchgeführt

## 1.28.0 - 2026-09-28

- „ToDo“ in das HumHub-Erstellen-Menü des Space-Streams integriert
- der Eintrag öffnet das vollständige bestehende Aufgabenformular
- Anzeige nur bei aktiviertem Modul und vorhandener Berechtigung „ToDo erstellen“
- platzsparende Einsortierung hinter den vorrangigen Erstellaktionen
- native HumHub-Integration ohne parallele Formularlogik

## 1.27.2 - 2026-09-28

- Dateiaktionen strikt auf den aktuell geöffneten Space begrenzt
- Aufgabenübersicht für große Datenbestände deutlich beschleunigt
- blockierte Aufgaben werden in einer Sammelabfrage statt einzeln geprüft
- Qualitäts- und Sicherheitstests um Regressionstests für beide Korrekturen erweitert
- Belastungstest mit 1.000 Aufgaben unter HumHub 1.19 erfolgreich durchgeführt

## 1.27.1 - 2026-09-28

- unklare Aufgabenzähler wie „(1 | 2)“ aus dem Space-Menü entfernt
- der Menüeintrag heißt jetzt unabhängig von der Aufgabenanzahl immer schlicht „ToDo“
- zwei unnötige Datenbankabfragen beim Aufbau des Space-Menüs entfallen
- die rote Warnmarkierung für eigene überfällige Aufgaben bleibt erhalten
- quadratisches Modulbild für die HumHub-Modulverwaltung ergänzt

## 1.27.0 - 2026-09-28

- übersichtliche Druckansicht für Aufgaben ergänzt
- die Druckansicht übernimmt wie der CSV-Export die aktuelle Ansicht und alle Filter
- über den Browser kann die Liste gedruckt oder direkt als PDF gespeichert werden
- platzsparendes Querformat mit wiederholter Tabellenüberschrift auf Folgeseiten
- die Druckansicht enthält ausschließlich Aufgaben, die der Benutzer sehen darf

## 1.26.0 - 2026-09-28

- CSV-Export für Aufgaben ergänzt
- der Export übernimmt die aktuelle Ansicht und alle gesetzten Filter
- enthalten sind Titel, Aufgabenliste, Status, Priorität, Fälligkeit, Zuständige, Labels und Erstellungsangaben
- UTF-8 und Semikolon-Trennung sorgen für eine zuverlässige Darstellung in Excel
- Tabellenformeln aus Aufgabentexten werden beim Export aus Sicherheitsgründen neutralisiert

## 1.25.0 - 2026-09-28

- persönliche Standardvorgabe für Aufgabenbenachrichtigungen ergänzt
- die Vorgabe befindet sich unter Kontoeinstellungen → ToDo-Menü
- Aufgaben ohne individuelle Auswahl übernehmen den persönlichen Standard automatisch
- pro Aufgabe kann weiterhin eine abweichende Einstellung gewählt oder wieder auf den Standard zurückgestellt werden

## 1.24.0 - 2026-09-28

- persönliche Benachrichtigungseinstellung pro Aufgabe ergänzt
- wählbar sind alle Meldungen, nur Kommentare und Zuweisungen, nur Erinnerungen oder stumm
- die Auswahl ist einklappbar und gilt ausschließlich für den jeweiligen Benutzer und die jeweilige Aufgabe
- bestehende Aufgaben behalten standardmäßig das bisherige Benachrichtigungsverhalten
- Erwähnungen und HumHub-Follower bleiben von der Einstellung unberührt

## 1.23.0 - 2026-09-28

- Papierkorb für gelöschte Aufgaben ergänzt
- Aufgaben können innerhalb von 30 Tagen vollständig wiederhergestellt werden
- nur Space-Administratoren dürfen Aufgaben sofort endgültig löschen
- Papierkorbeinträge werden nach 30 Tagen automatisch endgültig entfernt
- Papierkorbaufgaben werden aus Suche, Übersichten, Widgets, Erinnerungen und Kanban ausgeblendet
- Verschieben und Wiederherstellen werden im Aktivitätsprotokoll dokumentiert

## 1.22.1 - 2026-09-28

- Aktionsleiste der Aufgabenübersicht für Smartphones neu angeordnet
- Suche und Verwaltungsaktionen passen sich nun an die verfügbare Breite an
- horizontales Überlaufen und abgeschnittene Kanban-Inhalte auf kleinen Bildschirmen behoben

## 1.22.0 - 2026-09-28

- automatische Archivierung pro Space ergänzt
- wahlweise nach 30, 60 oder 90 Tagen; standardmässig ausgeschaltet
- nur erledigte Aufgaben mit vorhandenem Abschlussdatum werden berücksichtigt
- automatisch archivierte Aufgaben bleiben vollständig wiederherstellbar
- automatische Archivierung wird als Systemeintrag im Aktivitätsprotokoll dokumentiert
- bestehende Space-Widget-Einstellungen werden nun zuverlässig im richtigen Space-Kontext gespeichert

## 1.21.0 - 2026-09-28

- geschlossene Aufgaben können archiviert werden
- archivierte Aufgaben verschwinden aus Liste und Kanban, ohne gelöscht zu werden
- eigene Archivansicht mit Filtern ergänzt
- archivierte Aufgaben können vollständig wiederhergestellt werden
- Dateien, Kommentare, Checklisten und Aktivitätsverlauf bleiben erhalten
- Archivieren und Wiederherstellen werden im Aktivitätsprotokoll dokumentiert

## 1.20.0 - 2026-09-28

- Kanban-Karten lassen sich innerhalb einer Spalte per Drag-and-drop sortieren
- persönliche Kartenreihenfolge wird pro Benutzer dauerhaft gespeichert
- beim Verschieben zwischen Spalten werden Status und Reihenfolge gemeinsam aktualisiert
- persönliche Reihenfolge verändert die Ansicht anderer Mitglieder nicht
- Reihenfolgedaten werden bei gelöschten Aufgaben und bei der Moduldeinstallation entfernt

## 1.19.1 - 2026-09-28

- Namenskonflikt der Aufgabenlabel-Relation mit HumHubs eigener Content-Label-Methode behoben
- Kompatibilität mit HumHub 1.18 und 1.19 wiederhergestellt

## 1.19.0 - 2026-09-28

- wiederverwendbare farbige Labels pro Space ergänzt
- mehrere Labels können einer Aufgabe zugeordnet werden
- Labels erscheinen kompakt in Listen-, Detail- und Kanban-Ansicht
- einklappbaren Filter nach Labels ergänzt
- zentrale Verwaltung für Namen und Farben ergänzt
- Duplikate und wiederkehrende Folgeaufgaben übernehmen ihre Labels
- Labeländerungen werden im Aktivitätsprotokoll festgehalten
- vollständige Entfernung der Labeldaten bei der Moduldeinstallation ergänzt

## 1.18.1 - 2026-09-28

- gesamte Kanban-Karte öffnet nun die zugehörige Aufgabe
- Tastaturbedienung der Kanban-Karten mit Enter und Leertaste ergänzt
- Drag-and-drop bleibt unverändert nutzbar

## 1.18.0 - 2026-09-28

- optionale kompakte Kanban-Ansicht mit den Spalten Offen, In Bearbeitung und Geschlossen ergänzt
- Statuswechsel per Drag-and-drop mit denselben Berechtigungen und Prüfungen wie in der Aufgabendetailansicht umgesetzt
- Sicherheitsabfrage für offene Checklisten und Sperre für blockierte Aufgaben beibehalten
- Filter nach Priorität, Aufgabenliste und zuständiger Person ergänzt und einklappbar gehalten
- zuletzt gewählte Listen- oder Kanban-Ansicht wird pro Benutzer gespeichert
- mobile Statusauswahl als zuverlässige Alternative zu Drag-and-drop ergänzt

## 1.17.0 - 2026-09-28

- zentrale Aufgabenübersicht vollständig auf Deutsch und Englisch umgestellt
- Vorlagenverwaltung und persönliche Menüeinstellungen übersetzt
- wichtigste Aufgabenlisten-, Erstellungs- und Bearbeitungsansichten internationalisiert
- fehlende englische Texte für bald fällige Erinnerungen ergänzt
- automatischen Test für fehlende deutsche oder englische Übersetzungsschlüssel ergänzt

## 1.16.2 - 2026-09-28

- Bezeichnungen der persönlichen Menüpositionen präzisiert
- irreführende Zusage «direkt nach der Übersicht» entfernt
- Hinweis ergänzt, dass andere Module die genaue Reihenfolge im Hauptmenü beeinflussen

## 1.16.1 - 2026-09-28

- dauerhaft sichtbaren Hauptmenüpunkt «Meine ToDos» ergänzt
- die globale Aufgabenübersicht ist dadurch unabhängig vom Dashboard-Widget erreichbar
- der Menüpunkt wird in der Aufgabenübersicht als aktiv markiert und für Gäste ausgeblendet
- jeder Benutzer kann den Menüpunkt in den Kontoeinstellungen ein- oder ausblenden
- die persönliche Position kann auf vorne, Mitte oder hinten gestellt werden

## 1.16.0 - 2026-09-28

- zentrale persönliche Aufgabenübersicht über alle sichtbaren Spaces ausgebaut
- Filter nach Space, Zuständigkeit, Status und Priorität ergänzt
- Suche in Titel und Beschreibung ergänzt
- Schnellansichten für überfällige, bald fällige, blockierte Aufgaben und Unteraufgaben ergänzt
- Space, Fälligkeit, Hauptaufgabe und Zuständige werden direkt in der Übersicht angezeigt
- bestehender Link «Zeige alle» im Dashboard führt auf die neue Übersicht

## 1.15.0 - 2026-09-28

- bestehende Aufgaben können als wiederverwendbare Space-Vorlagen gespeichert werden
- eigene Vorlagenverwaltung mit Bearbeiten, Verwenden und Löschen ergänzt
- Vorlagen übernehmen Titel, Beschreibung, Priorität, Zuständige und Checklistenpunkte
- beim Verwenden öffnet sich die neue Aufgabe zur Ergänzung von Termin und weiteren Details
- Vorlagen enthalten bewusst keinen Status, Termin, Verlauf, Dateien oder Abhängigkeiten
- Aufgaben lassen sich direkt aus der Detailansicht duplizieren
- Kopien übernehmen Beschreibung, Liste, Priorität, Fälligkeit, Zuständige und Wiederholung
- Checklisten und deren Zuständige werden als offene Punkte übernommen
- eine Kopie startet immer mit dem Status «Offen» und erhält einen eindeutigen Titelzusatz
- Kommentare, Dateien, Verlauf, Abhängigkeiten und Unteraufgaben werden bewusst nicht kopiert
- Quelle und Kopie erhalten nachvollziehbare Einträge im Aktivitätsprotokoll

## 1.14.0 - 2026-09-28

- Zuständige werden benachrichtigt, sobald die letzte offene Voraussetzung erledigt ist
- falls keine andere zuständige Person vorhanden ist, wird der Ersteller informiert
- die Person, welche die Voraussetzung abschliesst, erhält keine unnötige Eigenbenachrichtigung
- die automatische Freigabe wird im Aktivitätsprotokoll der abhängigen Aufgabe dokumentiert
- Benachrichtigungen respektieren weiterhin Sichtbarkeit und ToDo-Anzeigerecht

## 1.13.0 - 2026-09-28

- Aufgaben können andere Aufgaben als Voraussetzung erhalten
- offene Voraussetzungen kennzeichnen die abhängige Aufgabe sichtbar als blockiert
- blockierte Aufgaben können erst nach Abschluss aller Voraussetzungen geschlossen werden
- Voraussetzungen lassen sich direkt in der Aufgabendetailansicht hinzufügen und entfernen
- zyklische und Space-übergreifende Abhängigkeiten werden verhindert
- Änderungen an Voraussetzungen werden im Aktivitätsprotokoll dokumentiert
- Abhängigkeiten werden bei der vollständigen Moduldeinstallation entfernt

## 1.12.0 - 2026-09-28

- vollständige Aufgaben können als Unteraufgaben einer Hauptaufgabe angelegt werden
- Unteraufgaben behalten eigene Zuständige, Termine, Erinnerungen, Dateien und Kommunikation
- einklappbare Unteraufgabenübersicht mit Status und Fortschritt ergänzt
- Rücksprung von der Unteraufgabe zur Hauptaufgabe ergänzt
- Unteraufgaben erscheinen nicht doppelt in der normalen Space-Aufgabenliste
- Space-übergreifende Verknüpfungen und zyklische Aufgabenhierarchien werden verhindert
- wiederkehrende Unteraufgaben bleiben derselben Hauptaufgabe zugeordnet

## 1.11.0 - 2026-09-28

- tägliche, wöchentliche, monatliche und jährliche Wiederholungen ergänzt
- frei wählbares Wiederholungsintervall und optionales Enddatum ergänzt
- beim Abschliessen wird genau eine neue offene Folgeaufgabe erzeugt
- Zuständige und Checkliste werden übernommen; Checklisten-Termine werden passend verschoben
- Monatsenden und Schaltjahre werden kalendergerecht behandelt
- Erzeugung und Ende einer Aufgabenserie werden im Aktivitätsprotokoll dokumentiert
- automatische Tests für Datumsberechnung und Serienende ergänzt

## 1.10.0 - 2026-09-28

- konfigurierbare Vorwarnung bis zu 30 Tage vor Fälligkeit ergänzt
- getrennte einmalige Erinnerungen vor Fälligkeit, am Fälligkeitstag und bei Überfälligkeit
- wiederholte tägliche Überfälligkeitsmeldungen verhindert
- Änderung des Fälligkeitsdatums setzt die Erinnerungsstufen der Aufgabe zurück
- globale ToDo-Konfiguration um Schalter und Vorwarnzeit erweitert
- automatische Tests für die Erinnerungsstufen ergänzt

## 1.9.0 - 2026-09-28

- einklappbares Aktivitätsprotokoll in der Aufgabendetailansicht ergänzt
- Erstellen und Ändern von Aufgaben sowie Zuweisungen werden protokolliert
- Status-, Checklisten- und Dateiaktionen werden mit Benutzer und Zeitpunkt festgehalten
- Verlauf ist auf die neuesten 100 Einträge begrenzt und standardmäßig geschlossen
- Verlaufsdaten werden bei der Moduldeinstallation vollständig entfernt

## 1.8.9 - 2026-09-28

- Berechtigungsentscheidungen in einer zentralen, testbaren Regel zusammengeführt
- automatische Rollenmatrix für Ersteller, Zuständige, fremde Mitglieder, Moderation, Administration und Gäste ergänzt
- Berechtigungstests in die lokale Qualitätsprüfung und GitHub Actions aufgenommen

## 1.8.8 - 2026-09-28

- Space-Mitglieder dürfen standardmäßig ToDos anzeigen und erstellen
- Ersteller dürfen ihre eigenen ToDos vollständig bearbeiten und löschen
- zugewiesene Personen dürfen Status und Checkliste der Aufgabe bearbeiten
- Moderatoren, Administratoren und Besitzer dürfen alle Aufgaben bearbeiten
- nur Besitzer und Administratoren dürfen standardmäßig alle Aufgaben löschen
- Gäste erhalten keine standardmäßigen ToDo-Schreibrechte mehr

## 1.8.7 - 2026-09-28

- Kompatibilität mit der geänderten Space-Modul-API in HumHub 1.19 hergestellt
- aktive Menüerkennung an die aktuelle HumHub-API angepasst
- Kommentarbereich für HumHub 1.18 und 1.19 kompatibel gemacht
- Dashboard-Abfragen für MySQL im Strict Mode korrigiert
- vollständige Deinstallation entfernt ToDo-Tabellen und zugehörige HumHub-Inhalte
- zusätzliche Regressionstests für die HumHub-1.19-Kompatibilität ergänzt

## 1.8.6 - 2026-08-19

- Composer-Metadaten um PHP-Anforderung und PSR-4-Autoloading ergänzt
- Aufgabenübersicht und Suche auf 25 Einträge pro Seite begrenzt
- neue Installationen aktivieren das Modul nicht mehr ungeprüft in allen Spaces
- deutsche und englische Übersetzungsgrundlage ergänzt
- automatisierte Syntax-, Metadaten- und Strukturprüfungen ergänzt
- GitHub-Actions-Workflow für PHP 8.2 und 8.3 ergänzt

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
