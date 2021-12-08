<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	public function construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		echo 'Api JKN';
	}
}
?>