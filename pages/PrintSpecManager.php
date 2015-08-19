<?php

html_page_top1(plugin_lang_get('page_title'));
html_head_end();
html_body_begin();

$t_window_title = string_display_line(config_get('window_title'));

$t_query = 'SELECT *';
$t_query = $t_query .= ' FROM mantis_bug_table';
$t_query = $t_query .= ' WHERE NOT mantis_bug_table.requirement = ""';
$t_query = $t_query .= ' OR NOT mantis_bug_table.requirement = null';
$t_query = $t_query .= ' AND NOT mantis_bug_table.resource = ""';
$t_query = $t_query .= ' OR NOT mantis_bug_table.resource = null';
$t_query = $t_query .= ' ORDER BY mantis_bug_table.last_updated DESC';
   
$t_all_valid_bugs = db_query($t_query);
   
while($t_vbug = db_fetch_array($t_all_valid_bugs))
{
   $t_vbugs[] = $t_vbug;
}
   
$t_vbug_count = count($t_vbugs);

echo '<table class="width100" cellspacing="1">';
   echo '<tr>';
      echo '<td class="form-title" colspan="10">';
         echo '<div class="center">';
         echo $t_window_title . ' - Specification Manager';
         echo '</div>';
      echo '</td>';         
   echo '</tr>';
   echo '<tr>';
      echo '<td class="print-spacer" colspan="10">';
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
   echo '</tr>';
   echo '<tr>';
      echo '<td class="print-spacer" colspan="10">';
      echo '<hr />';
      echo '</td>';
   echo '</tr>';

   for($i=0; $i<$t_vbug_count; $i++)
   {
      $t_vbug = $t_vbugs[$i];
      echo '<tr>';
	      // Column Bug
	      echo '<td class="print">';
	         echo '<a href="view.php?id=' . $t_vbug['id'] . '">' . bug_format_id($t_vbug['id']) . '</a>';
	      echo '</td>';
			
	      // Column Priority
		   echo '<td class="print">';
		   if ($t_vbug['priority'] == 10)
		   {
		   	echo '<img src="http://localhost/mantis13/images/priority_low_2.gif" alt title="none">';
		   }
		   elseif ($t_vbug['priority'] == 20)
		   {
		   	echo '<img src="http://localhost/mantis13/images/priority_low_1.gif" alt title="low">';
		   }
		   elseif ($t_vbug['priority'] == 30)
		   {
		   	echo '<img src="http://localhost/mantis13/images/priority_normal.gif" alt title="normal">';
		   }
		   elseif ($t_vbug['priority'] == 40)
		   {
		   	echo '<img src="http://localhost/mantis13/images/priority_1.gif" alt title="high">';
		   }
		   elseif ($t_vbug['priority'] == 50)
		   {
		   	echo '<img src="http://localhost/mantis13/images/priority_2.gif" alt title="urgent">';
		   }
		   elseif ($t_vbug['priority'] == 60)
		   {
		   	echo '<img src="http://localhost/mantis13/images/priority_3.gif" alt title="immediate">';
		   }
		   echo '</td>';
			
	      // Column Category
	      $t_query = 'SELECT DISTINCT mantis_category_table.name AS ""';
	      $t_query = $t_query .= ' FROM mantis_category_table, mantis_bug_table';
	      $t_query = $t_query .= ' WHERE mantis_category_table.id = ' . $t_vbug['category_id'];
	      
	      $t_category_name = db_query($t_query);
		   echo '<td class="print">';
		   echo $t_category_name;
		   echo '</td>';
	
		   // Column Severity
		   echo '<td class="print">';
		   echo MantisEnum::getLabel('severity_enum_string', $t_vbug['severity']);
		   echo '</td>';
		   
		   // Column Status
		   echo '<td class="print">';
		   echo MantisEnum::getLabel('status_enum_string', $t_vbug['status']);
		   echo '</td>';
	
		   // Column Last updated
		   echo '<td class="print">';
		   echo date('Y-m-d', $t_vbug['last_updated']);
		   echo '</td>';
	
		   // Column Summary
		   echo '<td class="print">';
		   echo $t_vbug['summary'];
		   echo '</td>';
	
		   // Column Requirement
		   echo '<td class="print">';
		   echo $t_vbug['requirement'];
		   echo '</td>';
	      
		   // Column Resource
		   echo '<td class="print">';
		   echo $t_vbug['resource'];
		   echo '</td>';
		   
		   // Column Version			   
	      echo '<td class="print">';
	      echo $t_vbug['version'];
	      echo '</td>';
      echo '</tr>';
   }
echo '</table>';

html_body_end();
html_end();