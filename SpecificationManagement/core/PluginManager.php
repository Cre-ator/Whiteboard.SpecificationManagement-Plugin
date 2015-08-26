<?php

class PluginManager
{
	public function getActMantisVersion()
	{
		return substr( MANTIS_VERSION, 0, 4 );
	}
	
	public function updateReqResInBugtableByBugId( $requirement, $resource, $bugId )
	{
		$sqlquery = ' UPDATE mantis_bug_table' .
						' SET requirement =' . '"' . $requirement . '",' .
						' resource =' . '"' . $resource . '"' .
						' WHERE mantis_bug_table.id = ' . $bugId;
		
		$applyUpdate = db_query( $sqlquery );
		
		return $applyUpdate;
	}
	
	public function getSubprojectByCurrentProject( $actProject )
	{
		$sqlquery = ' SELECT mantis_project_table.name AS ""' .
						' FROM mantis_project_table, mantis_project_hierarchy_table' .
						' WHERE mantis_project_table.id = (' .
						' SELECT mantis_project_hierarchy_table.child_id' .
						' FROM mantis_project_hierarchy_table' .
						' WHERE mantis_project_hierarchy_table.parent_id = ' . $actProject .
						' )';
		
		$subprojectByCurrentProject = db_query( $sqlquery );
		
		return $subprojectByCurrentProject;
	}
	
	public function getValidBugsByCurrentProject( $actProject )
	{		
		$sqlquery = ' SELECT *' .
						' FROM mantis_bug_table' .
						' WHERE (NOT mantis_bug_table.requirement = ""' .
						' OR NOT mantis_bug_table.requirement = null' .
						' OR NOT mantis_bug_table.resource = ""' .
						' OR NOT mantis_bug_table.resource = null)';
		if ( $actProject != '' )
		{
			$sqlquery .= ' AND mantis_bug_table.project_id = ' . $actProject;
		}
		$sqlquery .= ' ORDER BY mantis_bug_table.last_updated DESC';
		
		$allValidBugsByCurrentProject = db_query( $sqlquery );
		
		return $allValidBugsByCurrentProject;
	}
	
	public function getCategoryNameById( $categoryId )
	{
		$sqlquery = ' SELECT DISTINCT mantis_category_table.name AS ""' .
						' FROM mantis_category_table, mantis_bug_table' .
						' WHERE mantis_category_table.id = ' . $categoryId;
		
		$categoryName = db_query( $sqlquery );
		
		return $categoryName;
	}
}