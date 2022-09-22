<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ws_bpjs extends CI_Controller
{
	var $consid 		= '18949';
	var $timestamp 		= '';
	var $signature		= '';
	var $secret			= '5nN61FD7CA';
	var $keys			= 'e78eabacc9a866b3af284be1fe864f76';
	var $kodeppk 		= '';
    var $data_rs        = array('consid'            => '18949',
                                'secret'            => '5nN61FD7CA',
                                'keys'              => 'e78eabacc9a866b3af284be1fe864f76',
                                'signature'         => '',
                                'timestamp'         => '',
                                'kodeppk'           => '0177R010',
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
var $basehfis		= 'https://apijkn.bpjs-kesehatan.go.id/antreanrs/';
	var $debug= false;
	public function construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		

	}

	public function executeHfis($url, $request=null, $method="POST",$tipe="1"){
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
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
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
		if($tipe == "2"){
			header('Content-Type: application/json; charset=utf-8');
			echo $content;
			exit();
		}
		$time['time']=$headers['time'];
		$merger_content=json_encode(array_merge((array)json_decode($content, true),$time));
		$final_decode = consFinalhFis($merger_content);
		return $final_decode;
	}

	public function executeHfislog($url, $request=null, $method="POST",$tipe="1"){
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
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		if($request){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $request );
		}
		$content = curl_exec($ch);
		$arr = (array)json_decode($content, true);
		if($arr == null){
			echo $content;
			// exit();
		}
		if ($content === false) {
			echo 'Curl error: ' . curl_error($ch);
			// exit();
		}else if($content == null){
			// echo $content;
			// exit();
		}else if($content == 'Not Found' || $content == 'Not Found'){
			echo 'Curl error: ' . curl_error($ch);
			// exit();
		} else {
        	// echo 'Operation completed without any errors';
			// exit();
		}

		curl_close($ch);
		if($tipe == "2"){
			header('Content-Type: application/json; charset=utf-8');
			echo $content;
			// exit();
		}
		$time['time']=$headers['time'];
		$merger_content=json_encode(array_merge((array)json_decode($content, true),$time));
		$final_decode = consFinalhFis3($merger_content);
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
		return $this->executeHfis($url,$data);
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
		// $waktu 				= round(microtime(true) * 1000);
		$waktu 			= $this->input->post('waktu');

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
		$tglsekarang = date('Y-m-d');
		// insert data non jkn
		$this->antrian->get_non_jkn();
		// cek data di mr_karcis cetak
		
		$datas = $this->db->query("SELECT ap.*,muser.id_extPass as kode_dokter
								   from antrian_jkn ap
								   join muser on muser.nik = ap.iddokter
								   where (ap.flag_ws = 'N' or ap.flag_ws is null) and ap.tanggalperiksa = '$tglsekarang' order by ap.id asc
								   LIMIT 25
								")->result_array();
		// debug($datas);
		foreach ($datas as $key => $val) {
			$cek_karcis = $this->db->query("SELECT * from mr_karcis_cetak where rm = '$val[norm]' and dokter = '$val[iddokter]' and tanggal = '$val[tanggalperiksa]'")->row();
			// print_r($cek_karcis);
				if($cek_karcis != null){
					$waktu_task = strtotime($cek_karcis->tglcetak) * 1000;
					$kuota = $this->antrian->set_kuota($val);
					$jp = ($val['nomorkartu'] != null and $val['nomorreferensi'] != null) ? 'JKN' : 'NON JKN';
					$data = array(
						"kodebooking" => $val['id'],
						"jenispasien"=> $jp,
						"nomorkartu"=> $val['nomorkartu'],
						"nik" => $val['nik'],
						"nohp" => $val['notelp'],
						"kodepoli" => $val['kodepoli'],
						"namapoli" => $val['namapoli'],
						"pasienbaru" => '1',
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
					// debug($data);
				$data = json_encode($data);
				$url = getMethod('tambahantrian',$this->basehfis,$this->method);
				$res = $this->executeHfislog($url,$data,"POST");
				echo json_encode($res);
				$this->db->update('antrian_jkn', ['respon' => $res,'flag_ws' => 'Y'], ['id' => $val['id']]);
				if($res){
					$response = json_decode($res);
					// debug($response);
						if($response->metadata->code == "200"){
							// add log
							$this->db->insert('log_jkn', [
								'data'		=> $data,
							]);
							// update task id sampai 3
							for ($i=1; $i <=3 ; $i++) { 
								$waktu 				= round(microtime(true) * 1000);
								$taskdata =array("kodebooking" => "$val[id]",
								"taskid" => $i,
								"waktu" => "$waktu_task");
			
								$data = json_encode($taskdata);
								$url = getMethod('updateantrian',$this->basehfis,$this->method);
								$this->executeHfislog($url,$data,"POST");
							}

							$this->db->update('antrian_jkn', ['flag_ws' => 'Y'], ['id' => $val['id']]);
							echo "$val[id] success!!<br>";
						}else if($response->metadata->code == "208"){
							for ($i=1; $i <=3 ; $i++) { 
								$waktu 				= round(microtime(true) * 1000);
							$taskdata =array("kodebooking" => "$val[id]",
								"taskid" => $i,
								"waktu" => "$waktu_task");
			
								$data = json_encode($taskdata);
								$url = getMethod('updateantrian',$this->basehfis,$this->method);
								$this->executeHfislog($url,$data,"POST");
							}
								$this->db->update('antrian_jkn', ['flag_ws' => 'Y'], ['id' => $val['id']]);
							echo "$val[id] success!!<br>";
						}else{
							echo $res;
						}
					}
				}else{
					$this->db->update('antrian_jkn', ['respon' => 'Data tidak ditemukan di karcis cetak','flag_ws' => 'P'], ['id' => $val['id']]);
				}
			// }
		}
	}


	public function pushantrianpending(){
		$this->load->model('Antrian_model', 'antrian');
		$tglsekarang = date('Y-m-d');
		// cek data di mr_karcis cetak
		
		$datas = $this->db->query("SELECT ap.*,muser.id_extPass as kode_dokter
								   from antrian_jkn ap
								   join muser on muser.nik = ap.iddokter
								   where ap.flag_ws = 'P' and ap.tanggalperiksa = '$tglsekarang' order by ap.id asc
								   LIMIT 25
								")->result_array();
		// debug($datas);
		foreach ($datas as $key => $val) {
			// if($val['kodepoli'] != null and $val['kode_dokter'] != null){
				// print_r($val);
			$cek_karcis = $this->db->query("SELECT * from mr_karcis_cetak where rm = '$val[norm]' and dokter = '$val[iddokter]' and tanggal = '$val[tanggalperiksa]'")->row();
			// print_r($cek_karcis);
				if($cek_karcis != null){
					$waktu_task = strtotime($cek_karcis->tglcetak) * 1000;
					$kuota = $this->antrian->set_kuota($val);
					$jp = ($val['nomorkartu'] != null and $val['nomorreferensi'] != null) ? 'JKN' : 'NON JKN';
					$data = array(
						"kodebooking" => $val['id'],
						"jenispasien"=> $jp,
						"nomorkartu"=> $val['nomorkartu'],
						"nik" => $val['nik'],
						"nohp" => $val['notelp'],
						"kodepoli" => $val['kodepoli'],
						"namapoli" => $val['namapoli'],
						"pasienbaru" => '1',
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
					// debug($data);
				$data = json_encode($data);
				$url = getMethod('tambahantrian',$this->basehfis,$this->method);
				$res = $this->executeHfislog($url,$data,"POST");
				echo json_encode($res);
				$this->db->update('antrian_jkn', ['respon' => $res,'flag_ws' => 'Y'], ['id' => $val['id']]);
				if($res){
					$response = json_decode($res);
					// debug($response);
						if($response->metadata->code == "200"){
							// add log
							$this->db->insert('log_jkn', [
								'data'		=> $data,
							]);
							// update task id sampai 3
							for ($i=1; $i <=3 ; $i++) { 
								$waktu 				= round(microtime(true) * 1000);
								$taskdata =array("kodebooking" => "$val[id]",
								"taskid" => $i,
								"waktu" => "$waktu_task");
			
								$data = json_encode($taskdata);
								$url = getMethod('updateantrian',$this->basehfis,$this->method);
								// print_r($data);exit();
								$this->executeHfislog($url,$data,"POST");
							}

							$this->db->update('antrian_jkn', ['flag_ws' => 'Y'], ['id' => $val['id']]);
							echo "$val[id] success!!<br>";
						}else if($response->metadata->code == "208"){
							for ($i=1; $i <=3 ; $i++) { 
								$waktu 				= round(microtime(true) * 1000);
							$taskdata =array("kodebooking" => "$val[id]",
								"taskid" => $i,
								"waktu" => "$waktu_task");
			
								$data = json_encode($taskdata);
								$url = getMethod('updateantrian',$this->basehfis,$this->method);
								$this->executeHfislog($url,$data,"POST");
							}
								$this->db->update('antrian_jkn', ['flag_ws' => 'Y'], ['id' => $val['id']]);
							echo "$val[id] success!!<br>";
						}else{
							echo $res;
						}
					}
				}
			// }
		}
	}

	public function pushnatrianbulan(){
		// $this->load->model('Antrian_model', 'antrian');
		$tglsekarang = '2022-06';
		// cek data di mr_karcis cetak
		
		$datas = $this->db->query("SELECT ap.*,muser.id_extPass as kode_dokter
								   from antrian_jkn ap
								   join muser on muser.nik = ap.iddokter
								   where ap.flag_ws is null and DATE_FORMAT(ap.tanggalperiksa,'%Y-%m') = '$tglsekarang' order by ap.id asc
								   LIMIT 50
								")->result_array();
		// debug($datas);
		foreach ($datas as $key => $val) {
			
			$cek_karcis = $this->db->query("SELECT * from mr_karcis_cetak where rm = '$val[norm]' and dokter = '$val[iddokter]' and tanggal = '$val[tanggalperiksa]'")->row();
			// print_r($cek_karcis);
				if($cek_karcis != null){
					$waktu_task = strtotime($cek_karcis->tglcetak) * 1000;
					$kuota = $this->antrian->set_kuota($val);
					$jp = ($val['nomorkartu'] != null and $val['nomorreferensi'] != null) ? 'JKN' : 'NON JKN';
					$data = array(
						"kodebooking" => $val['id'],
						"jenispasien"=> $jp,
						"nomorkartu"=> $val['nomorkartu'],
						"nik" => $val['nik'],
						"nohp" => $val['notelp'],
						"kodepoli" => $val['kodepoli'],
						"namapoli" => $val['namapoli'],
						"pasienbaru" => '1',
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
					// debug($data);
				$data = json_encode($data);
				$url = getMethod('tambahantrian',$this->basehfis,$this->method);
				$res = $this->executeHfislog($url,$data,"POST");
				echo json_encode($res).'\n';
				$this->db->update('antrian_jkn', ['respon' => $res,'flag_ws' => 'Y'], ['id' => $val['id']]);
				if($res){
					$response = json_decode($res);
					// debug($response);
						if($response->metadata->code == "200"){
							// add log
							$this->db->insert('log_jkn', [
								'data'		=> $data,
							]);
							// update task id sampai 3
							for ($i=1; $i <=3 ; $i++) { 
								$waktu 				= round(microtime(true) * 1000);
								$taskdata =array("kodebooking" => "$val[id]",
								"taskid" => $i,
								"waktu" => "$waktu_task");
			
								$data = json_encode($taskdata);
								$url = getMethod('updateantrian',$this->basehfis,$this->method);
								// print_r($data);exit();
								$this->executeHfislog($url,$data,"POST");
							}

							$this->db->update('antrian_jkn', ['flag_ws' => 'Y'], ['id' => $val['id']]);
							echo "$val[id] success!!<br>\n";
						}else if($response->metadata->code == "208"){
							for ($i=1; $i <=3 ; $i++) { 
								$waktu 				= round(microtime(true) * 1000);
							$taskdata =array("kodebooking" => "$val[id]",
								"taskid" => $i,
								"waktu" => "$waktu_task");
			
								$data = json_encode($taskdata);
								$url = getMethod('updateantrian',$this->basehfis,$this->method);
								$this->executeHfislog($url,$data,"POST");
							}
								$this->db->update('antrian_jkn', ['flag_ws' => 'Y'], ['id' => $val['id']]);
							echo "$val[id] success!!<br>\n";
						}else{
							echo $res;
						}
					}
				}
			// }
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
		return $this->executeHfis($url.'tanggal/'.$tanggal.'/waktu/server',null,'GET','2');
		
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
		return $this->executeHfis($url.'bulan/'.$bulan.'/tahun/'.$tahun.'/waktu/server',null,'GET','2');
		
	}
}
?>