<?php
/*
	*********************************************************************

	Plugin Name: WPKit testing
	Description:
	Version:     1.0.0
	Plugin URI:
	Author:      LehaMotovilov
	Author URI:  http://lehamotovilov.com/
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

// Test User Meta
$userMeta = new WPKit\User\UserMetaBox();
$userMeta->add_field( 'user_avatar', 'Avatar', 'image' );

// Test Post Types
$postType = new WPKit\PostType\PostType( 'some-cpt', 'CPT Test' );
