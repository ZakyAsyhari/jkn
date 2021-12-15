<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @author
 * @version
 * @package
 */

require_once APPPATH . 'controllers/Rest.php';

use \Firebase\JWT\JWT;

class Pasien extends Rest
{

	public function __construct()
	{
		parent::__construct();
		// $this->load->model('model_appointment');
		$this->load->model('model_pasien');
		$this->jwt = $this->validate_token();
	}

	public function baru_post()
	{

		$tanggal_sekarang = date('Y-m-d');
		$input = $this->post();

		$tag = array('nomorkartu', 'nik', 'nomorkk', 'nama', 'jeniskelamin', 'tanggallahir', 'alamat', 'kodeprop', 'namaprop', 'kodedati2', 'namadati2', 'kodekec', 'namakec', 'kodekel', 'namakel', 'rw', 'rt');

		$mess = array('Nomor Kartu', 'NIK', 'Nomor KK', 'Nama', 'Jenis Kelamin', 'Tanggal Lahir', 'Alamat', 'Kode Propinsi', 'Nama Propinsi', 'Kode Dati 2', 'Dati 2', 'Kode Kecamatan', 'Kecamatan', 'Kode Kelurahan', 'Kelurahan', 'RW', 'RT');

		for ($index = 0; $index < sizeof($tag); $index++) {
			if (empty($input[$tag[$index]])) {
				if ($tag[$index] == 'jeniskelamin') {
					$pesan_null = $mess[$index] . ' Belum Dipilih';
				} else {
					$pesan_null = $mess[$index] . ' Belum Diisi';
				}
			}
		}

		if (!empty($pesan_null)) {
			return $this->response([
				'metadata' => [
					'message' => $pesan_null,
					'code' => 201
				]
			], 200);
		}

		if ($this->jwt != 1) {
			$pesan_gagal = "Token Expired.";
		} else if (!empty($this->model_pasien->cek_pasien($input['nomorkartu']))) {
			$pesan_gagal = "Data Peserta Sudah Pernah Dientrikan";
		} else if (!validateFormatDate($input['tanggallahir']) || $input['tanggallahir'] > $tanggal_sekarang) {
			$pesan_gagal = "Format Tanggal Lahir Tidak Sesuai";
		} else if (!is_numeric($input['kodekel'])) {
			$pesan_gagal = "Kode kelurahan hanya berupa angka";
		} else if (!is_numeric($input['nomorkartu']) || strlen($input['nomorkartu']) != 13) {
			$pesan_gagal = "Format Nomor Kartu Tidak Sesuai";
		} else if (!is_numeric($input['nik']) || strlen($input['nik']) != 16) {
			$pesan_gagal = "Format NIK Tidak Sesuai";
		}

		if (!empty($pesan_gagal)) {
			return $this->response([
				'metadata' => [
					'message' => $pesan_gagal,
					'code' => 201
				]
			], 200);
		} else {

			# insert data penduduk
			$penduduk = $this->model_pasien->insert_pasien($input);

			// print_r($penduduk);
			// exit();
			if (!empty($penduduk)) {

				# insert data dinamis penduduk
				

				# insert data pasien
				// $id_pasien = $this->model_appointment->last_id_pasien();
				// $nomor_rm  = $id_pasien[0]['ID_PASIEN'] + 1;

				// $this->db->insert('pasien', [
				// 	'id ' => $nomor_rm,
				// 	'ID_PENDUDUK' => $penduduk->id,
				// 	'NO_BPJS' => $input['nomorkartu']
				// ]);

				$this->response([
					'response' => ['norm' => $penduduk->id],
					'metadata' => [
						'message' => 'Harap datang ke admisi untuk melengkapi data rekam medis',
						'code' 	  => 200
					]
				], 200);
			} else {

				$this->response([
					'response' => ['norm' => null],
					'metadata' => [
						'message' => 'Gagal insert data penduduk',
						'code' 	  => 201
					]
				], 200);
			}
		}
	}
}

/* End of file Pasien.php */
/* Location: .//C/xampp/htdocs/api/rsubk/api-jkn/app/controllers/Pasien.php */