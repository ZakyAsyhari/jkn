<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ws_bpjs extends CI_Controller
{
	var $consid 		= '23124';
	var $timestamp 		= '';
	var $signature		= '';
	var $secret			= '6dA1995F61';
	var $kodeppk 		= '';
	var $baseurl		= 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev/';
	public function construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		$tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
		$this->timestamp = $tStamp;
		
		$signature = hash_hmac('sha256',"231124&" . $tStamp, "6dA1995F61", true);
		$encodedSignature = base64_encode($signature);
		echo $encodedSignature;
		exit();
	}

	public function header() 
	{

	}
}
?>