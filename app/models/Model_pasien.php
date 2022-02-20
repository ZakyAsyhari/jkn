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
		$date_now = date('Y-m-d H:i:s');

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
			'rm'		=> $rmMasuk,
			'nama'		=> $input['nama'],
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
		]);

		$id_mrk = $this->db->select('max(id) as id')
			->get('mr_ktp')
			->first_row();
		$new_id_mrk = $id_mrk->id + 1;


		$this->db->insert('mr_ktp', [
			'id'		=> $new_id_mrk,
			'rm'		=> $rmMasuk,
			'ktp'		=> $input['nik'],
			'alamat'	=> $input['alamat'],
			'jkn'		=> $input['nomorkartu'],
			'tglsave'	=> $date_now
		]);

		



					// // 'nik' 		=> $input['nik'],
					// 'nobpjs'	=> $input['nomorkartu'],
					// // 'nokk'		=> $input['nomorkk'],


		return $rmMasuk;
	}

	public function cek_pasien($nomorkartu,$noktp)
	{
		// $pasien = $this->db->get_where('mmr', ['nobpjs' => $nomorkartu])->first_row();
		$pasien = $this->db->query("SELECT mr_ktp.jkn,mr_ktp.ktp from mr_ktp join mmr on mmr.rm = mr_ktp.rm where mr_ktp.jkn ='$nomorkartu' or mr_ktp.ktp = '$noktp'")->first_row();
		return $pasien;
	}
}