<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Model_pasien extends CI_Model
{

	public function insert_pasien($input)
	{

		$lahir = date('d-M-Y', strtotime($input['tanggallahir']));
		$lahir = strtoupper($lahir);
		$sex = $input['jeniskelamin'];
		$nama_pengguna = str_replace("'", "", $input['nama']);
		$no_kk = $input['nomorkk'];
		$no_identitas = $input['nik'];

		$new_rm = $this->db->select('max(lokal_id) as id')
			->get('mr_periksa')
			->first_row();
		return $new_rm;
		print_r($new_rm);
		exit();

		$rm = $new_rm +1;

		$this->db->insert('mr_periksa', [
			'nik' 		=> $input['nik'],
			'lokal_id' 	=> $rm,
			'id_user'	=> $rm,
			'nm_user'	=> $input['nama'],
			'jk' => $input['jeniskelamin'],
			// 'GOL_DARAH'     => '',
			'tgllahir' => $lahir
			// 'nokk'  => $input['nomorkk']
		]);

		$penduduk = $this->db->select('max(id) as id')
			->get('mr_periksa')
			->first_row();
		return $rm;
	}

	public function cek_pasien($nomorkartu)
	{
		$pasien = $this->db->get_where('mr_periksa', ['nobpjs' => $nomorkartu])->first_row();
		return $pasien;
	}
}