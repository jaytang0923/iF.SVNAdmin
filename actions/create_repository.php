<?php
if (!defined('ACTION_HANDLING')) {
	die("HaHa!");
}

$engine = \svnadmin\core\Engine::getInstance();

//
// Authentication
//

if (!$engine->isProviderActive(PROVIDER_REPOSITORY_EDIT)) {
	$engine->forwardError(ERROR_INVALID_MODULE);
}

$engine->checkUserAuthentication(true, ACL_MOD_REPO, ACL_ACTION_ADD);

//
// HTTP Request Vars
//

$varParentIdentifierEnc = get_request_var('pi');
$reponame = get_request_var("reponame");
$repotype = get_request_var("repotype");

$varParentIdentifier = rawurldecode($varParentIdentifierEnc);


//
// Validation
//

if ($reponame == NULL) {
	$engine->addException(new ValidationException(tr("You have to fill out all fields.")));
}
else {
	//echo get_request_var("ps").'_'.get_request_var("protype").'_'.$reponame;
	$reponame=get_request_var("ps").'_'.get_request_var("protype").'_'.$reponame;
	$r = new \svnadmin\core\entities\Repository($reponame, $varParentIdentifier);
	$newap=null;
	
	// Create repository.
	try {
		$engine->getRepositoryEditProvider()->create($r, $repotype);
		$engine->getRepositoryEditProvider()->save();
		$engine->addMessage(tr("The repository %0 has been created successfully", array($reponame)));

		// Create the access path now.
		try {
			if (get_request_var("accesspathcreate") != NULL
				&& $engine->isProviderActive(PROVIDER_ACCESSPATH_EDIT)) {
				
				$ap = new \svnadmin\core\entities\AccessPath($reponame . ':/');

				if ($engine->getAccessPathEditProvider()->createAccessPath($ap)) {
					$engine->getAccessPathEditProvider()->save();
					$newap=$ap;
				}
			}
		}
		catch (Exception $e2) {
			$engine->addException($e2);
		}

		/*
		 Jay add 2014.08.19,auto add repo admin
		*/
		
		//1.set repo admin
		try{
			if($newap!=null){
				if($engine->getAclManager()->assignAccessPathAdmin($newap->getPath(),$engine->getSessionUsername()))
					$engine->addMessage(tr("Assigned user %1 to access-path %0 successfully !",array($newap->getPath(),$engine->getSessionUsername())));
				//else
				//	$engine->addException(new Exception(tr("Could not assign user %1 to access-path %0", array($ap->getPath(),$engine->getSessionUsername())));
		
				$engine->getAclManager()->save();
			}
		}
		catch (Exception $e4){
			$engine->addException($e4);
		}
		
		//2.add read-write permission.
		$oU = new \svnadmin\core\entities\User;
		$oU->id = $engine->getSessionUsername();
		$oU->name = $engine->getSessionUsername();
		
		$oP = new \svnadmin\core\entities\Permission;
		$oP->perm = \svnadmin\core\entities\Permission::$PERM_READWRITE;
		try {
			$b = $appEngine->getAccessPathEditProvider()->assignUserToAccessPath($oU, $newap, $oP);
			if (!$b) {
				throw new Exception("Setting Wrtie/Read ERROR.");
			}
			$appEngine->addMessage(tr("Grant %0 permission to %1 on %2", array($oP->perm, $oU->name, $newap->path)));
			$engine->getAccessPathEditProvider()->save();
		}
		catch (Exception $e5) {
			$appEngine->addException($e5);
		}
		
		/*End*/
		
		
		// Create a initial repository structure.
		try {
			$repoPredefinedStructure = get_request_var("repostructuretype");
			if ($repoPredefinedStructure != NULL) {
				
				switch ($repoPredefinedStructure) {
					case "single":
						$engine->getRepositoryEditProvider()
							->mkdir($r, array('trunk', 'branches', 'tags'));
						break;

					case "multi":
						$projectName = get_request_var("projectname");
						if ($projectName != NULL) {
							$engine->getRepositoryEditProvider()
								->mkdir($r, array(
									$projectName . '/trunk',
									$projectName . '/branches',
									$projectName . '/tags'
								));
						}
						else {
							throw new ValidationException(tr("Missing project name"));
						}
						break;
				}
			}
		}
		catch (Exception $e3) {
			$engine->addException($e3);
		}
	}
	catch (Exception $e) {
		$engine->addException($e);
	}
}
?>