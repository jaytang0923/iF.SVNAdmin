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

if (!$engine->isProviderActive(PROVIDER_REPOSITORY_EDIT)) {
	$engine->forwardInvalidModule(true);
}

$engine->checkUserAuthentication(true, ACL_MOD_SETTINGS, ACL_ACTION_CHANGE);
$appTR->loadModule("repositorycreate");

//
// Actions
//

if (check_request_var('create_product'))
{
	$newpro=get_request_var('newproduct');
	
	if(strlen($newpro)!=0)
	{
		$products=file('data/products');
		$repeat=false;
		foreach($products as $pro)
		{
			if(str_replace(PHP_EOL,'', $pro)==$newpro)
				$repeat=true;
		}
		if($repeat==false)
			$tf=file_put_contents('data/products',$newpro . PHP_EOL,FILE_APPEND);
	}
}

if (check_request_var('delete_product'))
{
	$delpro=get_request_var('ps');
	$products=file('data/products');
	$newproducts=remove_item_by_value($products,$delpro.PHP_EOL,true);
	file_put_contents('data/products',$newproducts);
}

//
// View Data
//

ProcessTemplate("repository/repositorycreateproduct.html.php");
?>