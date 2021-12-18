<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vclaim extends CI_Controller
{
	var $consid 		= '23124';
	var $timestamp 		= '';
	var $signature		= '';
	var $secret			= '6dA1995F61';
	var $keys			= 'bd5c6bfaf6d062a4a6f29012a050faeb';
	var $kodeppk 		= '';
    var $data_rs        = array('consid'            => '23124',
                                'secret'            => '6dA1995F61',
                                'keys'              => 'bd5c6bfaf6d062a4a6f29012a050faeb',
                                'signature'         => '',
                                'timestamp'         => '',
                                'kodeppk'           => '',
                                );
	var $baseurl		= 'https://apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev/';
	var $method			= array('cariPesertaBpjs' 	=> 'Peserta/nik/',
								'carinokartu'		=> 'Peserta/nokartu/'
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
	public function execute($url, $request=null, $method="GET"){
		$headers = generateHeader($this->data_rs);
		
		// if($this->debug==true){
			// print_r($headers[1]);
            // exit();
		// 	// show_array($url);
		// 	// show_array(json_decode($request));
		// }

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers['head']);
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
		$time['time']=$headers['time'];
		$merger_content=json_encode(array_merge(json_decode($content, true),$time));
		// print_r($merger_content);
		$final_decode = consFinal($merger_content);

		
		return $final_decode;
	}

	public function carinik($nik){
		$noka = trim($nik,' ');
		$date = date('Y-m-d');

		$url = getMethod('cariPesertaBpjs',$this->baseurl,$this->method).$nik.'/tglSEP/'.$date;
		return $this->execute($url);
		
	}

	public function carinokartu($noka){
		$noka = trim($noka,' ');
		$date = date('Y-m-d');

		$url = getMethod('carinokartu',$this->baseurl,$this->method).$noka.'/tglSEP/'.$date;
		return $this->execute($url);
		
	}
}
?>