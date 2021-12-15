<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Model_pasien extends CI_Model
{

	public function insert_pasien($input)
	{

		$lahir = $input['tanggallahir'];
		$lahir = strtoupper($lahir);
		$sex = $input['jeniskelamin'];
		$nama_pengguna = str_replace("'", "", $input['nama']);
		$no_kk = $input['nomorkk'];
		$no_identitas = $input['nik'];

		$new_rm = $this->db->select('max(lokal_id) as id')
			->where('lokal_id > 0')
			->get('mr_periksa')
			->first_row();

		$id_per = $this->db->select('max(id) as id')
			->get('mr_periksa')
			->first_row();
		// return $new_rm;
		// print_r($new_rm);
		// exit();
		$new_id = $id_per->id + 1;

		$rm = $new_rm->id +1;

		$this->db->insert('mr_periksa', [
			'id'		=> $new_id,
			'nik' 		=> $input['nik'],
			'lokal_id' 	=> $rm,
			'id_user'	=> $rm,
			'nm_user'	=> $input['nama'],
			'jk' => $input['jeniskelamin'],
			// 'GOL_DARAH'     => '',
			'tgllahir' => $lahir
			// 'nokk'  => $input['nomorkk']
		]);


		return $rm;
	}

	public function cek_pasien($nomorkartu)
	{
		$pasien = $this->db->get_where('mr_periksa', ['nobpjs' => $nomorkartu])->first_row();
		return $pasien;
	}
}