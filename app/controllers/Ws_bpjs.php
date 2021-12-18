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
    var $data_rs        = array('consid'            => '23124',
                                'secret'            => '6dA1995F61',
                                'keys'              => 'bd5c6bfaf6d062a4a6f29012a050faeb',
                                'signature'         => '',
                                'timestamp'         => '',
                                'kodeppk'           => '',
                                );
	var $method			= array('cariPesertaBpjs' 	=> 'Peserta/nik/',
								'carinokartu'		=> 'Peserta/nokartu/',
								'refpoli'			=> 'ref/poli',
								'refdokter'			=> 'ref/dokter',
								'jadwaldokter'		=> 'jadwaldokter/',
								'listwaktutask'	 	=> 'antrean/getlisttask',
								'updatejadwaldokter'=> 'jadwaldokter/updatejadwaldokter',
								'dashboard'			=> 'dashboard/waktutunggu/',
								'batalantrian'		=> 'antrean/batal'
								);
var $basehfis		= 'https://apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev/';
	var $debug= false;
	public function construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		

	}

	public function executeHfis($url, $request=null, $method="POST"){
		$headers = generateHeader($this->data_rs);
		
		// if($this->debug==true){
		// 	// show_array($headers);
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
		}else if($content == null){
			// echo $content;
			// exit();
		} else {
        	// echo 'Operation completed without any errors';
			// exit();
		}

		curl_close($ch);
		$time['time']=$headers['time'];
		$merger_content=json_encode(array_merge(json_decode($content, true),$time));
		// print_r($merger_content)h;
		$final_decode = consFinalhFis($merger_content);

		// DEBUG PURPOSE
		// if($this->debug==true){
		// 	show_array($final_decode);	
		// 	dd(json_decode($final_decode));
		// }
		return $final_decode;
	}


	public function refpoli(){
		$url = getMethod('refpoli',$this->basehfis,$this->method);
		return $this->executeHfis($url);
		
	}

	public function refdokter(){
		$url = getMethod('refdokter',$this->basehfis,$this->method);
		return $this->executeHfis($url);
		
	}

	public function jadwaldokter(){
		$kodepoli = $this->input->post('poli');
		$tanggal = $this->input->post('tanggal');

		$url = getMethod('jadwaldokter',$this->basehfis,$this->method);
		return $this->executeHfis($url.'kodepoli/'.$kodepoli.'/tanggal/'.$tanggal);
		
	}

	public function updatejadwaldokter(){
		$data = json_encode($this->input->post());
		$url = getMethod('updatejadwaldokter',$this->basehfis,$this->method);
		return $this->executeHfis($url,$data);
		
	}

	public function listWaktutask(){
		$url = getMethod('listwaktutask',$this->basehfis,$this->method);
		return $this->executeHfis($url);
	}

	public function batalantrian(){
		$pesan = '';
		$kodebooking 		= $this->input->post('kodebooking');
		$keterangan 		= $this->input->post('keterangan');

		if(empty($kodebooking)){
			$pesan = "Kode Booking Belum di isi";
		}

		if(empty($keterangan)){
			$pesan = "Keterangan Belum di isi";
		}
		if(!empty($pesan)){
			header('Content-Type: application/json; charset=utf-8');
			die(json_encode(['metadata'=>['message'=>$pesan,'code'=>201]]));
		}
		$url = getMethod('batalantrian',$this->basehfis,$this->method);
		return $this->executeHfis($url);
	}

	public function dashboarpertanggal(){
		$pesan = '';
		$tanggal = $this->input->post('tanggal');
		if(empty($tanggal)){
			$pesan = "Tanggal Belum di isi";
		}

		if(!empty($pesan)){
			header('Content-Type: application/json; charset=utf-8');
			die(json_encode(['metadata'=>['message'=>$pesan,'code'=>201]]));
		}

		$url = getMethod('dashboard',$this->basehfis,$this->method);
		return $this->executeHfis($url.'tanggal/'.$tanggal.'/waktu/'.time());
		
	}

	public function dashboarperbulan(){
		$pesan = '';
		$bulan = $this->input->post('bulan');
		$tahun = $this->input->post('tahun');
		if(empty($bulan)){
			$pesan = "Bulan Belum di isi";
		}

		if(empty($tahun)){
			$pesan = "Tahun Belum di isi";
		}
		if(!empty($pesan)){
			header('Content-Type: application/json; charset=utf-8');
			die(json_encode(['metadata'=>['message'=>$pesan,'code'=>201]]));
		}
		$url = getMethod('dashboard',$this->basehfis,$this->method);
		return $this->executeHfis($url,'bulan/'.$bulan.'/tahun/'.$tahun.'/waktu/'.time());
		
	}
}
?>