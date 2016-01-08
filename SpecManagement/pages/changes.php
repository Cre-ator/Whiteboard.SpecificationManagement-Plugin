<?php
include SPECMANAGEMENT_CORE_URI . 'database_api.php';
include SPECMANAGEMENT_CORE_URI . 'print_api.php';

$database_api = new database_api();
$print_api = new print_api();

/* TODO:
 * - Alle Bugs beider Versionen sammeln
 * - Prüfen, ob Bugs in beiden Versionen enthalten -> Meldung, wenn Bug neu bzw. weg
 * - Für jeden Bug, der in beiden Versionen enthalten ist prüfen, ob irgendwo eine
 *   Änderung vorgenommen wurde -> Meldung über Änderung mit entspr. Details
 */

/* get version if not empty */
if ( isset( $_POST['version_old'] ) && isset( $_POST['version_act'] ) )
{
   $version_old = version_get( $_POST['version_old'] );
   $version_act = version_get( $_POST['version_act'] );

   $version_old_obj = $database_api->getVersionRowByVersionId( $version_old->id );
   $version_act_obj = $database_api->getVersionRowByVersionId( $version_act->id );

   $p_version_old_id = $version_old_obj[0];
   $p_version_act_id = $version_act_obj[0];

   $work_packages_old = $database_api->getDocumentSpecWorkPackages( $p_version_old_id );
   $work_packages_act = $database_api->getDocumentSpecWorkPackages( $p_version_act_id );

   $relevant_bugs_old = $database_api->getAllBugsFromWorkpackages( $work_packages_old, $p_version_old_id );
   $relevant_bugs_act = $database_api->getAllBugsFromWorkpackages( $work_packages_act, $p_version_act_id );


   html_page_top1( plugin_lang_get( 'changes_title' ) . ': ' . $version_old->version . ' / ' . $version_act->version );
   echo '<link rel="stylesheet" href="plugins' . DIRECTORY_SEPARATOR . plugin_get_current() . DIRECTORY_SEPARATOR . 'files/specmanagement.css">';
   html_page_top2();

   if ( plugin_is_installed( 'WhiteboardMenu' ) )
   {
      $print_api->print_whiteboardplugin_menu();
   }

   $print_api->print_plugin_menu();

   echo '<table class="width60">';

   echo '<thead>';
   echo '<tr>';
   echo '<th class="center">' . $version_old->version . '</th>';
   echo '<th class="center">' . $version_act->version . '</th>';
   echo '</tr>';
   echo '</thead>';

   echo '<tbody>';
   echo '<tr>';
   echo '<td class="form-title" colspan="2">' . lang_get( 'summary' ) . '</td>';
   echo '</tr>';
   echo '<tr>';
   echo '<td>1</td>';
   echo '<td>2</td>';
   echo '</tr>';
   echo '</tbody>';

   echo '</table>';

}
html_page_bottom1();