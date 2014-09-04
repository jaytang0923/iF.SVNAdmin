<?php
/**
 * iF.SVNAdmin
 * Copyright (c) 2010 by Manuel Freiholz
 * http://www.insanefactory.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 */
include("include/config.inc.php");

//
// Authentication
//

$engine = \svnadmin\core\Engine::getInstance();

if (!$engine->isProviderActive(PROVIDER_REPOSITORY_VIEW)) {
	$engine->forwardError(ERROR_INVALID_MODULE);
}

$engine->checkUserAuthentication(true, ACL_MOD_REPO, ACL_ACTION_VIEW);
$appTR->loadModule("repositorylist");

//
// Actions
//

if (check_request_var("delete")) {
	$engine->handleAction("delete_repository");
}
else if (check_request_var('dump')) {
	$engine->handleAction('dump_repository');
	exit(0);
}
else if (check_request_var('load')) {
	
	exit(0);
}

//
// View data
//

$repositoryParentList = array();
$repositoryList = array();
$userspaths=array();
$usersrepos=array();

$oU = new \svnadmin\core\entities\User;
$oU->id = $engine->getSessionUsername();
$oU->name = $engine->getSessionUsername();

try {
	// Repository parent locations.
	$repositoryParentList = $engine->getRepositoryViewProvider()->getRepositoryParents();
	
	// Repositories of all locations.
	foreach ($repositoryParentList as $rp) {
		$repositoryList[$rp->identifier] = $engine->getRepositoryViewProvider()->getRepositoriesOfParent($rp);
		$userspaths[$rp->identifier]=$engine->getAccessPathViewProvider()->getPathsOfUser($oU);
		
		if($oU->getName()=='admin')
		{
			$usersrepos[$rp->identifier]=$repositoryList[$rp->identifier];
			continue;
		}
		$usersrepos[$rp->identifier]=array();
		
		//each repos maybe has more than 1 paths,or 0.
		foreach($repositoryList[$rp->identifier] as $repos)
		{
			$repospaths=$engine->getAccessPathViewProvider()->getPathsOfRepository($repos);
			
			//no paths
			if(!count($repospaths))
				continue;

			//list repos's paths  0 or 1
			foreach ($repospaths as $rps)
			{
				//list user's paths
				foreach ($userspaths[$rp->identifier] as $ups)
				{
					if($rps->getPath()==$ups->getPath())
					{
						//we should show repos ,not paths	
						//XXX in case of one repos has mul paths and all paths for one user.
						array_push($usersrepos[$rp->identifier],$repos);
					}
					
				}
			}
			
		}
		
		usort($usersrepos[$rp->identifier], array('\svnadmin\core\entities\Repository', 'compare'));
	}
	
	// Show options column?
	if (($engine->isProviderActive(PROVIDER_REPOSITORY_EDIT)
		&& $engine->hasPermission(ACL_MOD_REPO, ACL_ACTION_DUMP)
		&& $engine->getConfig()->getValueAsBoolean('GUI', 'RepositoryDumpEnabled', false))
		){
		SetValue('ShowOptions', true);
		SetValue('ShowDumpOption', true);
	}
}
catch (Exception $ex) {
	$engine->addException($ex);
}

SetValue('RepositoryParentList', $repositoryParentList);
SetValue('RepositoryList', $usersrepos);
SetValue('ShowDeleteButton', $engine->getConfig()->getValueAsBoolean('GUI', 'RepositoryDeleteEnabled', true));
ProcessTemplate('repository/repositorylist.html.php');
?>