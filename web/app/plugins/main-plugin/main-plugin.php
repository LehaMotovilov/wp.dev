<?php
/*
	*********************************************************************

	Plugin Name: Main Plugin
	Description: Main site plugin. Contains all current sites functionality.
	Version:     1.0.0
	Plugin URI:
	Author:      LehaMotovilov
	Author URI:  http://lehamotovilov.com/
	Text Domain: main-plugin
	Domain Path: /languages/
	Network: 	 True
	License:     GPL v3

	*********************************************************************

	Copyright 2015 Aleksey Motovilov

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	*********************************************************************
*/

// Security check.
defined( 'ABSPATH' ) or die();

// Load all configs.
$config = require_once __DIR__ . '/config/main.php';

// Let's start!
$framework = new LM\Core\Framework();
$framework->run( $config );

// Load Site Functionality
$site = new LM\Site\Init();
$site->run();
