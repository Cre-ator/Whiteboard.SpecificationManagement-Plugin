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

   $bug_count_old = count( $relevant_bugs_old );
   $bug_count_act = count( $relevant_bugs_act );

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

   for ( $bug_index_old = 0; $bug_index_old < $bug_count_old; $bug_index_old++ )
   {
      $bug_id_old = $relevant_bugs_old[$bug_index_old];
      $bug_old = bug_get( $bug_id_old );

      for ( $bug_index_act = 0; $bug_index_act < $bug_count_act; $bug_index_act++ )
      {
         $bug_id_act = $relevant_bugs_act[$bug_index_act];
         $bug_act = bug_get( $bug_id_act );

         if ( relationship_exists( $bug_id_old, $bug_id_act ) )
         {
            $relationship = $database_api->getBugRelationshipTypeTwo( $bug_id_act, $bug_id_old );
            if ( ( $key = array_search( $bug_id_old, $relevant_bugs_old ) ) !== false )
            {
               unset( $relevant_bugs_old[$key] );
            }
            if ( ( $key = array_search( $bug_id_act, $relevant_bugs_act ) ) !== false )
            {
               unset( $relevant_bugs_act[$key] );
            }

            echo '<tr>';
            echo '<td>';
            echo bug_format_id( $relationship[2] ) . ' ==>';
            echo '</td>';
            echo '<td>';
            echo bug_format_id( $relationship[1] );
            echo '</td>';
            echo '</tr>';
         }
      }
   }

   $scnd_bug_count_old = count( $relevant_bugs_old );
   $scnd_bug_count_act = count( $relevant_bugs_act );

   for ( $bug_index = 0; $bug_index < max( $scnd_bug_count_old, $scnd_bug_count_act ); $bug_index++ )
   {
      $scnd_bug_id_old = null;
      $scnd_bug_id_act = null;

      if ( $bug_index < $scnd_bug_count_old || !empty( $relevant_bugs_old ) )
      {
         if ( key_exists( $bug_index, $relevant_bugs_old ) )
         {
            $scnd_bug_id_old = $relevant_bugs_old[$bug_index];
         }
      }

      if ( $bug_index < $scnd_bug_count_act || !empty( $relevant_bugs_act ) )
      {
         if ( key_exists( $bug_index, $relevant_bugs_act ) )
         {
            $scnd_bug_id_act = $relevant_bugs_act[$bug_index];
         }
      }

      if ( !( is_null( $scnd_bug_id_old ) && is_null( $scnd_bug_id_act ) ) )
      {
         echo '<tr>';
         echo '<td>';
         if ( !is_null( $scnd_bug_id_old ) )
         {
            echo bug_format_id( $scnd_bug_id_old );
         }
         echo '</td>';
         echo '<td>';
         if ( !is_null( $scnd_bug_id_act ) )
         {
            echo bug_format_id( $scnd_bug_id_act );
         }
         echo '</td>';
         echo '</tr>';
      }
   }

   echo '</table>';
   echo '</td>';
   echo '</tr>';
   echo '</tbody>';

   echo '</table>';
}
html_page_bottom1();