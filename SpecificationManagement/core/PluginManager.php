<?php

/**
 * core functions needed in the plugin several times
 * 
 * @author schwarz
 *
 */
class PluginManager
{
	public function getActMantisVersion()
	{
		return substr(MANTIS_VERSION, 0, 4);
	}
	
	public function updateReqResInBugtableByBugId($requirement, $resource, $bugId)
	{
		$sqlquery = 'UPDATE mantis_bug_table';
		$sqlquery = $sqlquery .= ' SET requirement =' . '"' . $requirement . '",';
		$sqlquery = $sqlquery .= ' resource =' . '"' . $resource . '"';
		$sqlquery = $sqlquery .= ' WHERE mantis_bug_table.id = ' . $bugId;
		
		$applyUpdate = db_query($sqlquery);
		
		return $applyUpdate;
	}
	
	public function getSubprojectByCurrentProject($actProject)
	{
		$sqlquery = 'SELECT mantis_project_table.name AS ""';
		$sqlquery = $sqlquery .= ' FROM mantis_project_table, mantis_project_hierarchy_table';
		$sqlquery = $sqlquery .= ' WHERE mantis_project_table.id = (';
		$sqlquery = $sqlquery .= ' SELECT mantis_project_hierarchy_table.child_id';
		$sqlquery = $sqlquery .= ' FROM mantis_project_hierarchy_table';
		$sqlquery = $sqlquery .= ' WHERE mantis_project_hierarchy_table.parent_id = ' . $actProject;
		$sqlquery = $sqlquery .= ' )';
		
		$subprojectByCurrentProject = db_query($sqlquery);
		
		return $subprojectByCurrentProject;
	}
	
	public function getValidBugsByCurrentProject($actProject)
	{		
		$sqlquery = 'SELECT *';
		$sqlquery = $sqlquery .= ' FROM mantis_bug_table';
		$sqlquery = $sqlquery .= ' WHERE (NOT mantis_bug_table.requirement = ""';
		$sqlquery = $sqlquery .= ' OR NOT mantis_bug_table.requirement = null';
		$sqlquery = $sqlquery .= ' OR NOT mantis_bug_table.resource = ""';
		$sqlquery = $sqlquery .= ' OR NOT mantis_bug_table.resource = null)';
		if ($actProject != '')
		{
			$sqlquery = $sqlquery .= ' AND mantis_bug_table.project_id = ' . $actProject;
		}
		$sqlquery = $sqlquery .= ' ORDER BY mantis_bug_table.last_updated DESC';
		 
		$allValidBugsByCurrentProject = db_query($sqlquery);
		
		return $allValidBugsByCurrentProject;
	}
	
	public function getCategoryNameById($categoryId)
	{
		$sqlquery = 'SELECT DISTINCT mantis_category_table.name AS ""';
		$sqlquery = $sqlquery .= ' FROM mantis_category_table, mantis_bug_table';
		$sqlquery = $sqlquery .= ' WHERE mantis_category_table.id = ' . $categoryId;
		
		$categoryName = db_query($sqlquery);
		
		return $categoryName;
	}
}