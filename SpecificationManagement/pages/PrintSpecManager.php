<?php
include SPECIFICATIONMANAGEMENT_CORE_URI . 'PluginManager.php';

html_page_top1(plugin_lang_get('page_title'));
html_head_end();
html_body_begin();

// actual Project ID
$actProject = helper_get_current_project();

// array filled with selected bugs
$validBugs = array();

// PluginManager object
$pluginManager = new PluginManager();

// Get valid bugs by currently selected project
$allValidBugsByCurrentProject = $pluginManager->getValidBugsByCurrentProject($actProject);

// Fill array with selected bugs
while($validBug = db_fetch_array($allValidBugsByCurrentProject))
{
   $validBugs[] = $validBug;
}

// Get amount of selected bugs
$bugCount = count($validBugs);

echo '<table class="width100" cellspacing="1">';
   echo '<tr>';
      echo '<td class="form-title" colspan="11">';
         echo '<div class="center">';
         echo string_display_line(config_get('window_title')) . ' - Specification Manager';
         echo '</div>';
      echo '</td>';         
   echo '</tr>';
   echo '<tr>';
      echo '<td class="print-spacer" colspan="11">';
      echo '<hr />';
      echo '</td>';
   echo '</tr>';
   echo '<tr class="print-category">';
		echo '<td class="print" width="10%">' . plugin_lang_get('bug_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('prio_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('category_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('severity_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('status_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('lupdate_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('sum_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('requirement_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('resource_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('version_thead') . '</td>';
		echo '<td class="print" width="10%">' . plugin_lang_get('subproject_thead') . '</td>';
   echo '</tr>';
   echo '<tr>';
      echo '<td class="print-spacer" colspan="11">';
      echo '<hr />';
      echo '</td>';
   echo '</tr>';

for($i=0; $i<$bugCount; $i++)
{
	$validBug = $validBugs[$i];
	echo '<tr>';
		// Column Bug
      echo '<td class="print">';
      echo bug_format_id($validBug['id']);
      echo '</td>';
			
      // Column Priority
	   echo '<td class="print">';
	   if ($validBug['priority'] == 10)
	   {
	   	echo '<img src="http://localhost/mantis13/images/priority_low_2.gif" alt title="none">';
	   }
	   elseif ($validBug['priority'] == 20)
	   {
	   	echo '<img src="http://localhost/mantis13/images/priority_low_1.gif" alt title="low">';
	   }
	   elseif ($validBug['priority'] == 30)
	   {
	   	echo '<img src="http://localhost/mantis13/images/priority_normal.gif" alt title="normal">';
	   }
	   elseif ($validBug['priority'] == 40)
	   {
	   	echo '<img src="http://localhost/mantis13/images/priority_1.gif" alt title="high">';
	   }
	   elseif ($validBug['priority'] == 50)
	   {
	   	echo '<img src="http://localhost/mantis13/images/priority_2.gif" alt title="urgent">';
	   }
	   elseif ($validBug['priority'] == 60)
	   {
	   	echo '<img src="http://localhost/mantis13/images/priority_3.gif" alt title="immediate">';
	   }
	   echo '</td>';
			
      // Column Category
	   echo '<td>';
	   echo $pluginManager->getCategoryNameById($validBug['category_id']);
	   echo '</td>';
	
	   // Column Severity
	   echo '<td>';
	   echo MantisEnum::getLabel(plugin_lang_get('severity_enum_string'), $validBug['severity']);
	   echo '</td>';
		   
		// Column Status
		echo '<td>';
		echo MantisEnum::getLabel(plugin_lang_get('status_enum_string'), $validBug['status']);
		echo '</td>';
	
	   // Column Last updated
	   echo '<td class="print">';
	   echo date('Y-m-d', $validBug['last_updated']);
	   echo '</td>';
	
	   // Column Summary
	   echo '<td class="print">';
	   echo $validBug['summary'];
	   echo '</td>';
	
	   // Column Requirement
	   echo '<td class="print">';
	   echo $validBug['requirement'];
	   echo '</td>';
      
	   // Column Resource
	   echo '<td class="print">';
	   echo $validBug['resource'];
	   echo '</td>';
		   
	   // Column Version			   
      echo '<td class="print">';
      echo $validBug['version'];
      echo '</td>';
	      
      // Column Subproject    
		echo '<td>';
		echo $pluginManager->getSubprojectByCurrentProject($actProject);
		echo '</td>';
	echo '</tr>';
   }
echo '</table>';

html_body_end();
html_end();