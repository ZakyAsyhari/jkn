<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ws_bpjs extends CI_Controller
{
	var $consid 		= '23124';
	var $timestamp 		= '';
	var $signature		= '';
	var $secret			= '6dA1995F61';
	var $keys			= 'bd5c6bfaf6d062a4a6f29012a050faeb';
	var $kodeppk 		= '';
	var $baseurl		= 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev/';
	var $basehfis		= 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/';
	var $method			= array('cariPesertaBpjs' 	=> 'Peserta/nik/',
								'carinokartu'		=> 'Peserta/nokartu/',
								'refpoli'			=> 'ref/poli',
								'refdokter'			=> 'ref/dokter',
								'jadwaldokter'		=> 'jadwaldokter/',

								);
	var $debug= false;
	public function construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		

	}

	public function generateHeader() 
	{
		$tStamp = time();
		$this->timestamp = $tStamp;
		
		$signature = hash_hmac('sha256', $this->consid."&" . $tStamp, $this->secret, true);
		$encodedSignature = base64_encode($signature);
		$this->signature = $encodedSignature;

		$headers =[
			"X-cons-id: " . $this->consid,
			"X-timestamp: " . $tStamp,
			"X-signature: " . $encodedSignature,
			"user_key: " . $this->keys
	];
	// print_r($headers);exit();

		return $headers;
	}

	public function getMethod($method){

		$availableMethod = $this->method;

		if(!array_key_exists($method, $availableMethod)){
			return false;
		}

		return $this->baseurl.$availableMethod[$method];
	}

	public function getMethodhfis($method){

		$availableMethod = $this->method;

		if(!array_key_exists($method, $availableMethod)){
			return false;
		}

		return $this->basehfis.$availableMethod[$method];
	}

	public function execute($url, $request=null, $method="GET"){
		$headers = $this->generateHeader();
		
		// if($this->debug==true){
		// 	// show_array($headers);
		// 	// show_array($url);
		// 	// show_array(json_decode($request));
		// }

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		if($request){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $request );
		}
		$content = curl_exec($ch);
		// print_r($content);
		// exit();
		if ($content === false) {
			echo 'Curl error: ' . curl_error($ch);
			exit();
		} else {
        	// echo 'Operation completed without any errors';
			// exit();
		}

		curl_close($ch);
		$time['time']=$this->timestamp;
		$merger_content=json_encode(array_merge(json_decode($content, true),$time));
		// print_r($merger_content);
		$final_decode = consFinal($merger_content);

		
		return $final_decode;
	}

	public function executeHfis($url, $request=null, $method="GET"){
		$headers = $this->generateHeader();
		
		// if($this->debug==true){
		// 	// show_array($headers);
		// 	// show_array($url);
		// 	// show_array(json_decode($request));
		// }

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		if($request){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $request );
		}
		$content = curl_exec($ch);
		// print_r($content);
		// exit();
		if ($content === false) {
			echo 'Curl error: ' . curl_error($ch);
			exit();
		} else {
        	// echo 'Operation completed without any errors';
			// exit();
		}

		curl_close($ch);
		$time['time']=$this->timestamp;
		$merger_content=json_encode(array_merge(json_decode($content, true),$time));
		// print_r($merger_content);
		$final_decode = consFinalhFis($merger_content);

		// DEBUG PURPOSE
		// if($this->debug==true){
		// 	show_array($final_decode);	
		// 	dd(json_decode($final_decode));
		// }
		return $final_decode;
	}


	public function cariPesertaBpjs($nik){
		$noka = trim($nik,' ');
		$date = date('Y-m-d');

		$url = $this->getMethod('cariPesertaBpjs').$nik.'/tglSEP/'.$date;
		return $this->execute($url);
		
	}

	public function carinokartu($noka){
		$noka = trim($noka,' ');
		$date = date('Y-m-d');

		$url = $this->getMethod('carinokartu').$noka.'/tglSEP/'.$date;
		return $this->execute($url);
		
	}

	public function refpoli(){
		$url = $this->getMethodhfis('refpoli');
		return $this->executeHfis($url);
		
	}

	public function refdokter(){
		$url = $this->getMethodhfis('refdokter');
		return $this->executeHfis($url);
		
	}

	public function jadwaldokter(){
		$kodepoli = $this->input->post('poli');
		$tanggal = $this->input->post('tanggal');
		// print_r($kodepoli);
		// exit();

		$url = $this->getMethodhfis('jadwaldokter');
		return $this->executeHfis($url.'kodepoli/'.$kodepoli.'/tanggal/'.$tanggal);
		
	}

}
?>