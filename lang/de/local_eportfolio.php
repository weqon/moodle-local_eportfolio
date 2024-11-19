<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     local_eportfolio
 * @category    string
 * @copyright   2024 weQon UG <support@weqon.net>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ePortfolio';
$string['navbar'] = 'ePortfolio';

$string['actions:header'] = 'Aktion ausführen';

// Set db/access - permissions.
$string['eportfolio:view_eport'] = 'ePortfolio anzeigen';

$string['error:noguestaccess'] = 'Sie sind als Gast angemeldet. Der Gastzugriff ist für dieses Plugin nicht erlaubt!';
$string['error:missingcapability'] =
        'Sie haben nicht die erforderlichen Berechtigungen, um auf das Plung "eportfolio" zuzugreifen!';

$string['error:missingconfig'] = 'Bitte kontaktieren Sie Ihre/n Moodle-Administrator/in.';
$string['error:missingconfig:description'] = 'Das ePortfolio wurde noch nicht vollständig konfiguriert.<br>
Bitte die Installations-Anweisungen für das Plugin beachten.';
$string['error:missingconfig:gradingteacher'] = 'Rolle für Bewertungen nicht konfiguriert.';
$string['error:missingconfig:studentroles'] = 'Rolle für Teilnehmer/innen nicht konfiguriert.';
$string['error:missingconfig:missingh5pcapability'] = 'Berechtigung für H5P nicht konfiguriert.';

// Overview.
$string['overview:header'] = 'ePortfolio - Übersicht';

$string['overview:shareoption:share'] = 'Zur Ansicht';
$string['overview:shareoption:grade'] = 'Zur Bewertung';
$string['overview:shareoption:template'] = 'Als Vorlage';
$string['overview:helpfaq:title'] = 'Hilfe & FAQ';

$string['navbar:tab:myeportfolios'] = 'Meine ePortfolios';
$string['navbar:tab:mysharedeportfolios'] = 'Von mir geteilte ePortfolios';
$string['navbar:tab:mysharedeportfoliosgrade'] = 'Von mir zur Bewertung geteilte ePortfolios';
$string['navbar:tab:sharedeportfolios'] = 'Mit mir geteilte ePortfolios';
$string['navbar:tab:sharedeportfoliosgrade'] = 'Mit mir zur Bewertung geteilte ePortfolios';
$string['navbar:tab:sharedtemplates'] = 'ePortfolio Vorlagen';

$string['overview:table:actions'] = 'Aktionen';
$string['overview:table:actions:share'] = 'ePortfolio teilen';
$string['overview:table:actions:edit'] = 'Datei bearbeiten';
$string['overview:table:actions:delete'] = 'Datei löschen';
$string['overview:table:actions:view'] = 'Datei anzeigen';
$string['overview:table:actions:viewgradeform'] = 'Zur Bewertung';
$string['overview:table:actions:undo'] = 'Teilung zurückziehen';
$string['overview:table:actions:undo:template'] = 'Teilung als Vorlage zurückziehen';
$string['overview:table:actions:template'] = 'Diese Vorlage verwenden';

$string['overview:table:viewfile'] = 'Datei anzeigen';
$string['overview:table:viewcourse'] = 'Kurs anzeigen';
$string['overview:table:viewgradeform'] = 'Zur Bewertung';
$string['overview:table:selection'] = 'Auswahl';
$string['overview:table:filename'] = 'Dateiname';
$string['overview:table:filetimecreated'] = 'Angelegt am';
$string['overview:table:filetimemodified'] = 'Aktualisiert am';
$string['overview:table:filesize'] = 'Größe';
$string['overview:table:coursefullname'] = 'Geteilt im Kurs';
$string['overview:table:sharedby'] = 'Geteilt von';
$string['overview:table:participants'] = 'Geteilt mit';
$string['overview:table:sharestart'] = 'Geteilt am';
$string['overview:table:shareend'] = 'Geteilt bis';
$string['overview:table:grading'] = 'Bewertung';
$string['overview:table:graded'] = 'Bewertet?';
$string['overview:table:graded:pending'] = 'Ausstehend';
$string['overview:table:graded:done'] = 'Bewertet mit:';
$string['overview:table:istemplate'] = 'Dieses ePortfolio wurde für andere Nutzer:innen als Vorlage zur Verfügung gestellt.';
$string['overview:table:filedeleted'] = 'Sie haben das ePortfolio aus Ihrer persönlichen Übersicht gelöscht.
 Die zur Bewertung geteilte Datei muss endgültig im Kurs gelöscht werden.';

$string['overview:eportfolio:fileselect'] = 'Dateiauswahl';
$string['overview:eportfolio:uploadnewfile'] = 'H5P-Datei hochladen';
$string['overview:eportfolio:createnewfile'] = 'Neue H5P-Datei anlegen';
$string['overview:eportfolio:downloadfiles'] = 'Ausgewählte ePortfolios herunterladen';

$string['overview:eportfolio:nofiles:my'] =
        'Sie haben noch keine Dateien in Ihrem ePortfolio angelegt oder hochgeladen.';
$string['overview:eportfolio:nofiles:myshared'] =
        'Sie haben noch keine Dateien aus Ihrem ePortfolio zur Ansicht geteilt.';
$string['overview:eportfolio:nofiles:mygrade'] =
        'Sie haben noch keine Dateien aus Ihrem ePortfolio zur Bewertung geteilt.';
$string['overview:eportfolio:nofiles:shared'] = 'Mit Ihnen wurden noch keine ePortfolios zur Ansicht geteilt.';
$string['overview:eportfolio:nofiles:grade'] = 'Mit Ihnen wurden noch keine ePortfolios zur Bewertung geteilt.';
$string['overview:eportfolio:nofiles:template'] = 'Mit Ihnen wurden noch keine Vorlagen geteilt.';

// Customfield.
$string['customfield:name'] = 'ePortfolio';
$string['customfield:description'] = 'Diesen Kurs für ePortfolios freischalten';

// View.
$string['view:header'] = 'Ansicht ePortfolio';
$string['view:eportfolio:button:backtoeportfolio'] = 'Zurück zur Übersicht';
$string['view:eportfolio:button:backtocourse'] = 'Zurück zum Kurs';
$string['view:eportfolio:button:edit'] = 'H5P-Datei bearbeiten';
$string['view:eportfolio:sharedby'] = 'Geteilt von';
$string['view:eportfolio:timecreated'] = 'Angelegt am';
$string['view:eportfolio:timemodified'] = 'Aktualisiert am';

// Sharing.
$string['sharing:header'] = 'ePortfolio teilen';
$string['sharing:form:step:nocourseselection'] = 'Aktuell ist noch kein Kurs zum Teilen Ihres ePortfolios verfügbar.';
$string['sharing:form:step:courseselection'] = 'Kurs auswählen';
$string['sharing:form:step:shareoptionselection'] = 'Art der Teilung';
$string['sharing:form:select:hint'] = 'Bitte einen Kurs auswählen';
$string['sharing:form:step:userselection'] = 'Teilnehmer/innen auswählen';
$string['sharing:form:step:confirm'] = 'ePortfolio teilen';
$string['sharing:form:courseselection'] = 'Kurs zum Teilen auswählen';
$string['sharing:form:courseselection:desc'] = 'Bitte wählen Sie einen Kurs aus, in dem Sie Ihr ePortfolio teilen möchten.<br>
Sie können nur Kurse auswählen, die als ePortfolio-Kurs angelegt wurden und in denen Sie eingeschrieben sind.';
$string['sharing:form:shareoptionselection'] = 'Art der Teilung auswählen';
$string['sharing:form:shareoptionselection:desc'] = 'Bitte wählen Sie aus, wie Sie das ePortfolio teilen möchten.<br><br>
<b>Zur Ansicht:</b>
Teilnehmende im Kurs können das ePortfolio anschauen.<br>
<b>Zur Bewertung:</b>
Trainer:innen im Kurs können das ePortfolio bewerten.<br>
<b>Als Vorlage</b>:
Teilnehmende im Kurs können das ePortfolio als Vorlage weiterverwenden.<br><br>
Optional können Sie auch ein Datum auswählen, wie lange das ePortfolio verfügbar sein soll.';
$string['sharing:form:sharedcourses'] = 'Aktuell ausgewählter Kurs';
$string['sharing:form:sharedcourses_help'] = 'Sie könne nur Kurse auswählen, in denen Sie eingeschrieben sind.';
$string['sharing:form:select:allcourses'] = 'Alle Kurse';
$string['sharing:form:select:singlecourse'] = 'Kurs auswählen';
$string['sharing:form:shareoption'] = 'Art der Teilung';
$string['sharing:form:select:share'] = 'Zur Ansicht';
$string['sharing:form:select:grade'] = 'Zur Bewertung';
$string['sharing:form:select:template'] = 'Als Vorlage';
$string['sharing:form:enddate:enable'] = 'Enddatum setzen';
$string['sharing:form:enddate:label'] = 'Datumsauswahl aktivieren';
$string['sharing:form:enddate:select'] = 'Verfügbar bis';
$string['sharing:form:sharedusers'] = 'ePortfolio für den gesamten Kurs oder ausgewählte Nutzer:innen freigeben';
$string['sharing:form:sharedusers:desc'] = 'Bitte wählen Sie, ob Sie Ihr ePortfolio für den gesamten Kurs oder für ausgewählte Nutzer:innen freigeben möchten.<br>
Sie können Ihr ePortfolio für alle eingeschriebenen Nutzer:innen im Kurs teilen oder nur für bestimmte Rollen, Nutzer:innen oder Kursgruppen freigeben.';
$string['sharing:form:select:pleaseselect'] = 'Bitte wählen';
$string['sharing:form:fullcourse'] = 'ePortfolio teilen mit';
$string['sharing:form:select:fullcourse'] = 'alle im Kurs';
$string['sharing:form:select:targetgroup'] = 'ausgewählte Nutzer:innen';
$string['sharing:form:roles'] = 'Verfügbare Rollen';
$string['sharing:form:roles_help'] = 'Nur Nutzer:innen mit dieser Rollenzuweisung können das ePortfolio ansehen.';
$string['sharing:form:enrolledusers'] = 'Verfügbare Teilnehmer:innen';
$string['sharing:form:enrolledusers_help'] = 'Nur explizit ausgewählte Nutzer:innen können das ePortfolio ansehen.';
$string['sharing:form:groups'] = 'Verfügbare Kursgruppen';
$string['sharing:form:groups_help'] =
        'Nur die zugewiesenen Nutzer:innen der ausgewählten Kursgruppen können das ePortfolio ansehen.';

$string['sharing:alreadyshared:info'] = 'Das ePortfolio wurde bereits in folgenden Kursen geteilt:';
$string['sharing:alreadyshared:course'] = 'Kurs';
$string['sharing:alreadyshared:shareoption'] = 'Art der Teilung';

$string['sharing:share:successful'] = 'Das ePortfolio wurde erfolgreich im ausgewählten Kurs geteilt!';
$string['sharing:share:inserterror'] = 'Beim Teilen des ePortfolios ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut!';
$string['sharing:share:alreadyexists'] = 'Das ePortfolio wurde bereits unter den gleichen Bedingungen geteilt!';

// Forms general.
$string['form:field:required'] = 'Bitte dieses Feld ausfüllen!';
$string['form:cancelled'] = 'Die Aktion wurde abgebrochen!';

// Upload form.
$string['uploadform:header'] = 'H5P-Datei hochladen';
$string['uploadform:title'] = 'Titel/Bezeichnung';
$string['uploadform:description'] = 'Beschreibung';
$string['uploadform:file'] = 'Datei auswählen';
$string['uploadform:save'] = 'Datei hochladen';
$string['uploadform:template:header'] = 'Diese Datei als Vorlage zur Verfügung stellen';
$string['uploadform:template:check'] = 'Als Vorlage bereitstellen';
$string['uploadform:template:check_help'] =
        'Wenn Sie das Portfolio als Vorlage teilen, können andere Nutzer:innen dieses kopieren und verwenden.';
$string['uploadform:template:checklabel'] = 'Datei als Vorlage hochladen';
$string['uploadform:successful'] = 'Die Datei wurde erfolgreich hochgeladen';
$string['uploadform:error'] = 'Beim Hochladen der Datei ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';
$string['uploadform:cancelled'] = 'Die Aktion wurde abgebrochen!';

// Create new H5P file.
$string['create:header'] = 'ePortfolio - Neue H5P-Datei anlegen';
$string['contenteditor'] = 'Inhaltseditor';
$string['create:success'] = 'Der H5P Inhalt wurde erfolgreich erstellt.';
$string['create:error'] = 'Es trat bei der Erstellung des Inhalts ein Fehler auf.';
$string['create:library'] = 'Auswahl Bibliothek';
$string['h5plibraries'] = 'H5P Bibliotheken';

// Edit H5P file.
$string['edit:header'] = 'ePortfolio - bearbeiten';
$string['edit:success'] = 'Der H5P Inhalt wurde erfolgreich aktualisiert.';
$string['edit:error'] = 'Beim Speichern der Änderungen trat ein Fehler auf!';

// HelpFAQ.
$string['helpfaq:header'] = 'Hilfe & FAQ';

// Delete files & Undo shared files.
$string['undo:header'] = 'Geteiltes ePortfolio zurückziehen';
$string['undo:confirm'] = 'Bestätigen';
$string['undo:checkconfirm'] = 'Möchten Sie die Teilung für das ausgewählte ePortfolio wirklich zurückziehen?';
$string['undo:success'] = 'Die Teilung wurde erfolgreich zurückgezogen!';
$string['undo:error'] = 'Beim Zurückziehen der Teilung ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';
$string['delete:header'] = 'ePortfolio löschen';
$string['delete:confirm'] = 'Bestätigen';
$string['delete:nocourses'] = 'In keinen Kursen geteilt.';
$string['delete:checkconfirm'] = 'Möchten Sie das ausgewählte ePortfolio wirklich löschen?';
$string['delete:success'] = 'Das ePortfolio wurde erfolgreich gelöscht!';
$string['delete:error'] = 'Beim Löschen des ePortfolios ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';
$string['use:template:header'] = 'ePortfolio Vorlage verwenden';
$string['use:template:confirm'] = 'Bestätigen';
$string['use:template:checkconfirm'] = 'Möchten Sie die ausgewählte ePortfolio Vorlage verwenden?';
$string['use:template:success'] = 'Die Vorlage wurde erfolgreich zur weiteren Verwendung in Ihr ePortfolio kopiert!';
$string['use:template:error'] = 'Beim Kopieren der Vorlage ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut!';

// Events.
$string['event:eportfolio:viewed:name'] = 'ePortfolio Ansicht';
$string['event:eportfolio:shared:name'] = 'ePortfolio Teilung';
$string['event:eportfolio:created:name'] = 'ePortfolio erstellt';
$string['event:eportfolio:edited:name'] = 'ePortfolio bearbeitet';
$string['event:eportfolio:deleted:name'] = 'ePortfolio gelöscht';
$string['event:eportfolio:viewed'] =
        'The user with the id \'{$a->userid}\' viewed the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:shared:share'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:shared:grade'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} for grading (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:shared:template'] =
        'The user with the id \'{$a->userid}\' shared the ePortfolio {$a->filename} as template (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:undo'] =
        'The user with the id \'{$a->userid}\' withdrawn the sharing of the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:created'] =
        'The user with the id \'{$a->userid}\' created a new ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:edited'] =
        'The user with the id \'{$a->userid}\' edited the ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';
$string['event:eportfolio:deleted'] =
        'The user with the id \'{$a->userid}\' deleted ePortfolio {$a->filename} (fileid: \'{$a->fileid}\')';

// Message provider.
$string['messageprovider:sharing'] = 'Mitteilung über ein geteiltes ePortfolio';
$string['message:emailmessage'] =
        '<p>Mit Ihnen wurde ein ePortfolio geteilt. Art der Teilung: {$a->shareoption}<br>Geteilt von: {$a->userfrom}<br>Dateiname: {$a->filename}<br>URL: {$a->viewurl}</p>';
$string['message:smallmessage'] =
        '<p>Mit Ihnen wurde ein ePortfolio geteilt. Art der Teilung: {$a->shareoption}<br>Geteilt von: {$a->userfrom}<br>Dateiname: {$a->filename}<br>URL: {$a->viewurl}</p>';
$string['message:subject'] = 'Mitteilung über ein geteiltes ePortfolio';
$string['message:contexturlname'] = 'Geteiltes ePortfolio anzeigen';

// Download ePortfolio.
$string['download:error'] = 'Es konnten keine Dateien gefunden werden!';

// Settings.
$string['settings:general'] = 'Einstellungen';
$string['settings:gradingteacher'] = 'Rolle für Bewertungen';
$string['settings:gradingteacher:desc'] = 'Bitte wählen Sie die Rolle/n aus, die in der Aktivität "ePortfolio"
zur Bewertung geteilte ePortfolios bewerten dürfen.';
$string['settings:studentroles'] = 'Rolle für Teilnehmer/innen';
$string['settings:studentroles:desc'] = 'Bitte wählen Sie die Rolle/n aus, in denen Ihre Teilnehmer/innen
im Kurs eingeschrieben sind.';
$string['settings:globalnavbar:enable'] = 'Eintrag Hauptnavigation';
$string['settings:globalnavbar:enable:desc'] = 'In der Hauptnavigation wird ein Eintrag für das ePortfolio angezeigt.';

// Privacy provider.
$string['privacy:metadata:local_eportfolio'] = 'Vom ePortfolio-Plugin freigegebene Daten';
$string['privacy:metadata:local_eportfolio:usermodified'] = 'Nutzer:innen ID, die die ePortfolio-Daten angelegt/freigegeben hat';
$string['privacy:metadata:local_eportfolio:enrolled'] = 'Nutzer:innen IDs, mit denen die ePortfolio-Daten geteilt wurden';
