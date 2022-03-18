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
								'batalantrian'		=> 'antrean/batal',
								'updateantrian'		=> 'antrean/updatewaktu',
								'tambahantrian'		=> 'antrean/add'
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
		$arr = (array)json_decode($content, true);
		if($arr == null){
			echo $content;
			exit();
		}
		if ($content === false) {
			echo 'Curl error: ' . curl_error($ch);
			exit();
		}else if($content == null){
			// echo $content;
			// exit();
		}else if($content == 'Not Found' || $content == 'Not Found'){
			echo 'Curl error: ' . curl_error($ch);
			exit();
		} else {
        	// echo 'Operation completed without any errors';
			// exit();
		}

		curl_close($ch);
		$time['time']=$headers['time'];
		$merger_content=json_encode(array_merge((array)json_decode($content, true),$time));
		$final_decode = consFinalhFis($merger_content);
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
		$pesan = '';
		$kodebooking 		= $this->input->post('kodebooking');

		if(empty($kodebooking)){
			$pesan = "Kode Booking Belum di isi";
		}

		if(!empty($pesan)){
			header('Content-Type: application/json; charset=utf-8');
			die(json_encode(['metadata'=>['message'=>$pesan,'code'=>201]]));
		}else{
			$data = json_encode($this->input->post());
		}

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

		$data =array("kodebooking" => "$kodebooking",
					"keterangan" => "$keterangan");

		$data = json_encode($data);
		$url = getMethod('batalantrian',$this->basehfis,$this->method);
		// print_r($data);exit();
		return $this->executeHfis($url,$data,"POST");
	}


	public function updateantrian(){
		// print_r($this->input->post());exit();
		$pesan = '';
		$kodebooking 		= $this->input->post('kodebooking');
		$taskid 			= $this->input->post('taskid');
		$waktu 				= $this->input->post('waktu');

		if(empty($kodebooking)){
			$pesan = "Kode Booking Belum di isi";
		}

		if(empty($waktu)){
			$pesan = "Waktu Belum di isi";
		}
		if(empty($taskid)){
			$pesan = "Task Id Belum di isi";
		}
		if(!empty($pesan)){
			header('Content-Type: application/json; charset=utf-8');
			die(json_encode(['metadata'=>['message'=>$pesan,'code'=>201]]));
		}

		$data =array("kodebooking" => "$kodebooking",
					"taskid" => "$taskid",
					"waktu" => "$waktu");

		$data = json_encode($data);
		$url = getMethod('updateantrian',$this->basehfis,$this->method);
		// print_r($data);exit();
		return $this->executeHfis($url,$data,"POST");
	}

	public function tambahantrian(){
		$this->load->model('Antrian_model', 'antrian');
		$datas = $this->db->query("SELECT ap.*,muser.id_extPass as kode_dokter
								   from antrian_jkn ap
								   join mpoli on mpoli.s_name = ap.kodepoli
								   join muser on muser.nik = ap.iddokter
								   join (select rm,dokter,poli,count(*) from mr_karcis_cetak group by rm,dokter,poli) mrk on mrk.rm = ap.norm and mrk.dokter = ap.iddokter and mrk.poli = mpoli.s_name
								   where ap.flag_ws = 'N'
								")->result_array();
		// print_r($datas);
		foreach ($datas as $key => $val) {
			$kuota = $this->antrian->set_kuota($val);
			$data = array(
				"kodebooking" => $val['id'],
				"jenispasien"=> 'JKN',
				"nomorkartu"=> $val['nomorkartu'],
				"nik" => $val['nik'],
				"nohp" => $val['notelp'],
				"kodepoli" => $val['kodepoli'],
				"namapoli" => $val['namapoli'],
				"pasienbaru" => 0,
				"norm" => $val['norm'],
				"tanggalperiksa" => $val['tanggalperiksa'],
				"kodedokter" => $val['kode_dokter'],
				"namadokter" => $val['namadokter'],
				"jampraktek" => $val['jampraktek'],
				"jeniskunjungan" => $val['jeniskunjungan'],
				"nomorreferensi" => $val['nomorreferensi'],
				"nomorantrean" => 'A-'.$val['noantrian'],
				"angkaantrean"  => $val['noantrian'],
				"estimasidilayani" => $val['notelp'],
				"sisakuotajkn" => (int)$kuota['sisanonjkn'],
				"kuotajkn" => (int)$kuota['kuotajkn'],
				"sisakuotanonjkn" => (int)$kuota['sisanonjkn'],
				"kuotanonjkn" => (int)$kuota['kuotanonjkn'],
				"keterangan" => $val['keterangan']
			);
		 $data = json_encode($data);
		//  header('Content-Type: application/json; charset=utf-8');
		//  die(json_encode($data));
		$url = getMethod('tambahantrian',$this->basehfis,$this->method);
		$res = $this->executeHfis($url,$data,"POST");
			if($res){
				$response = json_decode($res);
				if($response->metadata->code == "200"){
					$this->db->update('antrian_jkn', ['flag_ws' => 'Y'], ['id' => $val['id']]);
					return $res;
				}else{
					return $res;
				}
			}
		}
	}

	public function tambahantrian_tunggal(){
		$data = array(
			"kodebooking" => $this->input->post('kodebooking'),
			"jenispasien"=> $this->input->post('jenispasien'),
			"nomorkartu"=> $this->input->post('nomorkartu'),
			"nik" => $this->input->post('nik'),
			"nohp" => $this->input->post('nohp'),
			"kodepoli" => $this->input->post('kodepoli'),
			"namapoli" => $this->input->post('namapoli'),
			"pasienbaru" => $this->input->post('pasienbaru'),
			"norm" => $this->input->post('norm'),
			"tanggalperiksa" => $this->input->post('tanggalperiksa'),
			"kodedokter" => $this->input->post('kodedokter'),
			"namadokter" => $this->input->post('namadokter'),
			"jampraktek" => $this->input->post('jampraktek'),
			"jeniskunjungan" => $this->input->post('jeniskunjungan'),
			"nomorreferensi" => $this->input->post('nomorreferensi'),
			"nomorantrean" => $this->input->post('nomorantrean'),
			"angkaantrean"  => $this->input->post('angkaantrean'),
			"estimasidilayani" => $this->input->post('estimasidilayani'),
			"sisakuotajkn" => $this->input->post('sisakuotajkn'),
			"kuotajkn" => $this->input->post('kuotajkn'),
			"sisakuotanonjkn" => $this->input->post('sisakuotanonjkn'),
			"kuotanonjkn" => $this->input->post('kuotanonjkn'),
			"keterangan" => $this->input->post('keterangan')
		 );
		 $data = json_encode($data);
		//  header('Content-Type: application/json; charset=utf-8');
		//  die(json_encode($data));
		$url = getMethod('tambahantrian',$this->basehfis,$this->method);
		return $this->executeHfis($url,$data,"POST");
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
		return $this->executeHfis($url.'tanggal/'.$tanggal.'/waktu/'.date('Y-m-d'));
		
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
		return $this->executeHfis($url,'bulan/'.$bulan.'/tahun/'.$tahun.'/waktu/'.date('Y-m-d'));
		
	}
}
?>