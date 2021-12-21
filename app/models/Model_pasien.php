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

		$new_rm = $this->db->select_max('rm')
			->get('mmr')
			->row();
		
		$id_per = $this->db->select('max(id) as id')
			->get('mmr')
			->first_row();
		$new_id = $id_per->id + 1;
		
		$rm = (int)$new_rm->rm + 1;
		$rmMasuk = norm($rm);

		$this->db->insert('mmr', [
			'id'		=> $new_id,
			'rm'		=> norm($rm),
			'nama'		=> $input['nama'],
			// 'nik' 		=> $input['nik'],
			// 'nobpjs'	=> $input['nomorkartu'],
			// 'nokk'		=> $input['nomorkk'],
			'alamat'	=> $input['alamat'],
			'hp'		=> $input['nohp'],
			'jk'		=> $input['jeniskelamin'],
			'tgllahir'	=> $input['tanggallahir'],
			'propinsi'	=> $input['namaprop'],
			'kabupaten'	=> $input['namadati2'],
			'kecamatan'	=> $input['namakec'],
			'kelurahan'	=> $input['namakel'],
			'rt'		=> $input['rt'],
			'rw'		=> $input['rw']
			// 'nokk'  => $input['nomorkk']
		]);


		return $rmMasuk;
	}

	public function cek_pasien($nomorkartu)
	{
		$pasien = $this->db->get_where('mmr', ['nobpjs' => $nomorkartu])->first_row();
		return $pasien;
	}
}