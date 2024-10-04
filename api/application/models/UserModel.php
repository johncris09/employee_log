<?php

defined('BASEPATH') or exit('No direct script access allowed');

class UserModel extends CI_Model
{

	public $table = 'user';

	public function __construct()
	{
		parent::__construct();
	}

	public function get()
	{
		$query = $this->db->get($this->table);

		return $query->result();

	}

	public function login($data)
	{
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where('username', $data['username']); // Fixed array key
		$query = $this->db->get();
		$result = $query->row();


		if ($result) {

			if (md5($data['password']) == $result->password) {
				return $result; // Passwords match
			}
		}

		return false;

	}


	public function find($id)
	{
		$this->db->where('id', $id);
		$query = $this->db->get($this->table);
		return $query->row();
	}

	public function insert($data)
	{
		return $this->db->insert($this->table, $data);

		// insert duplicate entry
		// $columns = implode(", ", array_keys($data));
		// $values = implode(", ", array_map(function ($value) {
		// 	return "'" . $value . "'";
		// }, array_values($data)));


		// $sql = "INSERT IGNORE INTO $this->table ($columns) VALUES ($values)";
		// return $this->db->query($sql);

	}


	public function update($id, $data)
	{
		$this->db->where('id', $id);
		return $this->db->update($this->table, $data);
	}

	public function delete($id)
	{
		return $this->db->delete($this->table, ['id' => $id]);
	}


	public function get_empployee_data($data)
	{
		// Load the second database
		$second_db = $this->load->database('second_db', TRUE);

		$query = $second_db
			->where($data)
			->get('jopdata');
		return $query->row();

	}



	public function get_latest_contract($data)
	{ 
		// Load the second database
		$second_db = $this->load->database('second_db', TRUE);

		$query = $second_db
			->select('DateFrom, DateTo')
			->where($data)
			->order_by('DateTo', 'desc') // Fixed array key
			->limit(1) // Fixed array key
			->get('jocontract');
		return $query->row();




	}

	public function get_schedule($data)
	{
		// Load the second database
		$second_db = $this->load->database('second_db', TRUE);

		$query = $second_db
			->where($data)
			->get('schedules');
		return $query->row();

	}

}
