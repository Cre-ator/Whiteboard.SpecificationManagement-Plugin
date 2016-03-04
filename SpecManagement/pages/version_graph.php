<?php
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_database_api.php';
require_once SPECMANAGEMENT_CORE_URI . 'specmanagement_print_api.php';

$print_flag = false;
if ( isset( $_POST['print_flag'] ) )
{
   $print_flag = true;
}

/**
 * Page content
 */
calculate_page_content( $print_flag );

/**
 * @param $print_flag
 */
function calculate_page_content( $print_flag )
{
   $specmanagement_print_api = new specmanagement_print_api();

   html_page_top1( plugin_lang_get( 'select_doc_title' ) );
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_FILES_URI . 'specmanagement.css">';
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_FILES_URI . 'horizontal-timeline/css/reset.css">';
   echo '<link rel="stylesheet" href="' . SPECMANAGEMENT_FILES_URI . 'horizontal-timeline/css/style.css">';
   echo '<script src="' . SPECMANAGEMENT_FILES_URI . 'horizontal-timeline/js/modernizr.js"></script>';
   if ( !$print_flag )
   {
      html_page_top2();
      if ( plugin_is_installed( 'WhiteboardMenu' ) )
      {
         require_once WHITEBOARDMENU_CORE_URI . 'whiteboard_print_api.php';
         $whiteboard_print_api = new whiteboard_print_api();
         $whiteboard_print_api->printWhiteboardMenu();
      }
      $specmanagement_print_api->print_plugin_menu();
      echo '<div align="center">';
      echo '<hr size="1" width="100%" />';
   }

   print_table( $print_flag );

   if ( !$print_flag )
   {
      html_page_bottom1();
   }
}

/**
 * @param $print_flag
 */
function print_table( $print_flag )
{
   $plugin_version_row_ids = get_plugin_version_data();
   $mantis_version_hasharray = get_mantis_version_data( $plugin_version_row_ids );

   ?>
<!--   <section class="cd-horizontal-timeline">-->
<!---->
<!--      <div class="events-content">-->
<!--         <ol>-->
<!--            <li class="selected" data-date="16/01/2014">-->
<!--               <h2>Horizontal Timeline</h2>-->
<!--               <em>January 16th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="28/02/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>February 28th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="20/04/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>March 20th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="20/05/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>May 20th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="09/07/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>July 9th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="30/08/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>August 30th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="15/09/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>September 15th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="01/11/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>November 1st, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="10/12/2014">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>December 10th, 2014</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="19/01/2015">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>January 19th, 2015</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!---->
<!--            <li data-date="03/03/2015">-->
<!--               <h2>Event title here</h2>-->
<!--               <em>March 3rd, 2015</em>-->
<!--               <p>-->
<!--                  Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illum praesentium officia, fugit recusandae-->
<!--                  ipsa, quia velit nulla adipisci? Consequuntur aspernatur at, eaque hic repellendus sit dicta-->
<!--                  consequatur quae, ut harum ipsam molestias maxime non nisi reiciendis eligendi! Doloremque quia-->
<!--                  pariatur harum ea amet quibusdam quisquam, quae, temporibus dolores porro doloribus.-->
<!--               </p>-->
<!--            </li>-->
<!--         </ol>-->
<!--      </div> <!-- .events-content -->-->
<!--   </section>-->
   <?php
   echo '<script src="' . SPECMANAGEMENT_FILES_URI . 'horizontal-timeline/js/jquery-2.1.4.js"></script>';
   echo '<script src="' . SPECMANAGEMENT_FILES_URI . 'horizontal-timeline/js/jquery.mobile.custom.min.js"></script>';
   echo '<script src="' . SPECMANAGEMENT_FILES_URI . 'horizontal-timeline/js/main.js"></script>';
   echo '<table>';
   print_thead( $mantis_version_hasharray );
   print_tbody( $print_flag, $mantis_version_hasharray );
   echo '</table>';
}

/**
 * Print head area of table
 */
function print_thead( $mantis_version_hasharray )
{
   echo '<thead>';


   echo '</thead>';
}

/**
 * Print body area of table
 *
 * @param $print_flag
 * @param $mantis_version_hasharray
 */
function print_tbody( $print_flag, $mantis_version_hasharray )
{
   echo '<tbody>';
   echo '<tr>';
   ?>
   <section class="cd-horizontal-timeline">
      <div class="timeline">
         <div class="events-wrapper">
            <div class="events">
               <ol>
                  <?php
                  foreach ( $mantis_version_hasharray as $version_hash )
                  {
                     $mantis_version_date = $version_hash[0];
                     echo '<li><a href="#0" data-date="' . $mantis_version_date . '" class="selected">' . ( date_is_null( $mantis_version_date ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $mantis_version_date ) ) ) . '</a></li>';
                  }
                  ?>
               </ol>
               <span class="filling-line" aria-hidden="true"></span>
            </div> <!-- .events -->
         </div> <!-- .events-wrapper -->
         <ul class="cd-timeline-navigation">
            <li><a href="#0" class="prev inactive">Prev</a></li>
            <li><a href="#0" class="next">Next</a></li>
         </ul> <!-- .cd-timeline-navigation -->
      </div> <!-- .timeline -->
      <?php
      echo '</tr>';
      echo '<tr>';
      ?>
      <div class="events-content">
         <ol>
            <?php
            foreach ( $mantis_version_hasharray as $version_hash )
            {
               $mantis_version_id = $version_hash[1];
               $mantis_version_date = $version_hash[0];

               echo '<li data-date="' . $mantis_version_date . '">';
               echo '<h2>' . version_full_name( $mantis_version_id ) . '</h2>';
               echo '<em>' . ( date_is_null( $mantis_version_date ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $mantis_version_date ) ) ) . '</em>';
               echo '<p>';
               echo '</p>';
               echo '</li>';

               echo '<li><a href="#0" data-date="' . $mantis_version_date . '" class="selected">' . ( date_is_null( $mantis_version_date ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $mantis_version_date ) ) ) . '</a></li>';
            }
            ?>
         </ol>
      </div> <!-- .events-content -->
   </section>
   <?php
   echo '</tr>';
   echo '</tbody>';
}

/**
 * Get plugin version row ids by selected project and its subrojects
 *
 * @return array
 */
function get_plugin_version_data()
{
   $specmanagement_database_api = new specmanagement_database_api();

   $project_id = helper_get_current_project();
   $sub_project_ids = project_hierarchy_get_all_subprojects( $project_id );

   $plugin_version_row_ids = array();
   array_push( $plugin_version_row_ids, $specmanagement_database_api->get_version_row_ids_by_project_id( $project_id ) );
   foreach ( $sub_project_ids as $sub_project_id )
   {
      array_push( $plugin_version_row_ids, $specmanagement_database_api->get_version_row_ids_by_project_id( $sub_project_id ) );
   }

   return $plugin_version_row_ids;
}

/**
 * Filter mantis version ids from plugin rows
 * returns array sorted ascending by version date
 *
 * @param $plugin_version_row_ids
 * @return array
 */
function get_mantis_version_data( $plugin_version_row_ids )
{
   $specmanagement_database_api = new specmanagement_database_api();

   $mantis_version_hasharray = array();
   foreach ( $plugin_version_row_ids as $plugin_version_row_id )
   {
      if ( !is_null( $plugin_version_row_id ) )
      {
         $mantis_version_hash = array();
         foreach ( $plugin_version_row_id as $version_row_primary_id )
         {
            $plugin_version_row = $specmanagement_database_api->get_version_row_by_primary( $version_row_primary_id );
            $mantis_version_id = $plugin_version_row[2];
            $mantis_version_date = intval( version_get_field( $mantis_version_id, 'date_order' ) );
            $mantis_version_hash[0] = $mantis_version_date;
            $mantis_version_hash[1] = $mantis_version_id;

            array_push( $mantis_version_hasharray, $mantis_version_hash );
         }
      }
   }

   foreach ( $mantis_version_hasharray as $mantis_version_id => $value )
   {
      $date[$mantis_version_id] = $value['0'];
      $id[$mantis_version_id] = $value['1'];
   }

   if ( !empty( $mantis_version_hasharray ) )
   {
      array_multisort( $date, SORT_ASC, $mantis_version_hasharray );
   }

   return $mantis_version_hasharray;
}