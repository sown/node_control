<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Package_List_Backfire extends Controller_Package_List
{
	protected $upstream_list_url = 'http://downloads.openwrt.org/backfire/10.03/x86/packages/Packages.gz';
}