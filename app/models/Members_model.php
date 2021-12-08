<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Members_model extends CI_Model
{
    private $table = 'akses_jkn';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function members_get($id = NULL)
    {
        $this->db->select('*');
        if($id != null) $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function members_insert($data)
    {
        if ($this->db->insert($this->table,$data)) return TRUE;
    }

    public function members_update($id, $data)
    {
        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->db->update($this->table, $data)) return TRUE;
    }

    public function members_delete($id = NULL)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        if ($this->db->affected_rows() > 0) return TRUE;
    }
}
