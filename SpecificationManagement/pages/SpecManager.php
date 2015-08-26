<?php
include SPECIFICATIONMANAGEMENT_CORE_URI . 'PluginManager.php';

html_page_top1( plugin_lang_get( 'page_title' ) );
html_page_top2();

// actual Project ID
$actProject = helper_get_current_project();

// array filled with selected bugs
$validBugs = array();

// PluginManager object
$pluginManager = new PluginManager();

// Get valid bugs by currently selected project
$allValidBugsByCurrentProject = $pluginManager->getValidBugsByCurrentProject( $actProject );

// Fill array with selected bugs
while ( $validBug = db_fetch_array( $allValidBugsByCurrentProject ) )
{
	$validBugs[] = $validBug;
}

// Get amount of selected bugs
$bugCount = count( $validBugs );

if ( $pluginManager->getActMantisVersion() == '1.2.' )
{
	echo '<table id="buglist" class="width100" cellspacing="1">';
}
else
{
	echo '<table>';
}
	echo '<thead>';
		echo '<tr class="buglist_nav">';
			echo '<td class="form-title" colspan="10">' . plugin_lang_get( 'table_title' ) . '</td>';
			echo '<td class="right alternate-views-links" colspan="11">';
				echo '<span class="small">';
					echo '[ <a href="' . plugin_page( 'PrintSpecManager' ) . '">' . plugin_lang_get( 'print_button' ) . '</a> ]';
				echo '</span>';
			echo '</td>';
		echo '</tr>';
		echo '<tr class="buglist-header row-category">';
			echo '<th>' . plugin_lang_get( 'bug_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'prio_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'category_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'severity_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'status_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'lupdate_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'sum_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'requirement_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'resource_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'version_thead' ) . '</th>';
			echo '<th>' . plugin_lang_get( 'subproject_thead' ) . '</th>';
		echo '</tr>';
		echo '<tr class="spacer">';
			echo '<td colspan="11" />';
		echo '</tr>';
	echo '</thead>';
   
	echo '<tbody>';

	for( $i=0; $i<$bugCount; $i++ )
	{
		$validBug = $validBugs[$i];
		if ( $pluginManager->getActMantisVersion() == '1.2.' )
		{
			echo '<tr bgcolor="' . get_status_color( $validBug['status'], auth_get_current_user_id(), $validBug['project_id'] ) . helper_alternate_class( $i ) . '">';
		}
		else
		{
			echo '<tr bgcolor="' . get_status_color( $validBug['status'], auth_get_current_user_id(), $validBug['project_id'] ) . '">';
		}
			// Column Bug
			echo '<td>';
				echo '<a href="view.php?id=' . $validBug['id'] . '">' . bug_format_id( $validBug['id'] ) . '</a>';
			echo '</td>';
		
			// Column Priority
			echo '<td class="column-Priority">';
			if ( $validBug['priority'] == 10 )
			{
				echo '<img src="http://localhost/mantis13/images/priority_low_2.gif" alt title="none">';
			}
			elseif ( $validBug['priority'] == 20 )
			{
				echo '<img src="http://localhost/mantis13/images/priority_low_1.gif" alt title="low">';
			}
			elseif ( $validBug['priority'] == 30 )
			{
				echo '<img src="http://localhost/mantis13/images/priority_normal.gif" alt title="normal">';
			}
			elseif ( $validBug['priority'] == 40 )
			{
				echo '<img src="http://localhost/mantis13/images/priority_1.gif" alt title="high">';
			}
			elseif ( $validBug['priority'] == 50 )
			{
				echo '<img src="http://localhost/mantis13/images/priority_2.gif" alt title="urgent">';
			}
			elseif ( $validBug['priority'] == 60 )
			{
				echo '<img src="http://localhost/mantis13/images/priority_3.gif" alt title="immediate">';
			}
			echo '</td>';
		
			// Column Category
			echo '<td>';
			echo $pluginManager->getCategoryNameById( $validBug['category_id'] );
			echo '</td>';
		
			// Column Severity
			echo '<td>';
			echo MantisEnum::getLabel( plugin_lang_get( 'severity_enum_string' ), $validBug['severity'] );
			echo '</td>';
			   
			// Column Status
			echo '<td>';
			echo MantisEnum::getLabel( plugin_lang_get( 'status_enum_string' ), $validBug['status'] );
			echo '</td>';
		
			// Column Last updated
			echo '<td>';
			echo date( 'Y-m-d', $validBug['last_updated'] );
			echo '</td>';
		
			// Column Summary
			echo '<td>';
			echo $validBug['summary'];
			echo '</td>';
		
			// Column Requirement
			echo '<td>';
			echo $validBug['requirement'];
			echo '</td>';
		      
			// Column Resource
			echo '<td>';
			echo $validBug['resource'];
			echo '</td>';
			   
			// Column Version			   
			echo '<td>';
			echo $validBug['version'];
			echo '</td>';
            
			// Column Subproject    
			echo '<td>';
			echo $pluginManager->getSubprojectByCurrentProject( $actProject );
			echo '</td>';
		echo '</tr>';
	}
	echo '</tbody>';
echo '</table>';

html_page_bottom1();