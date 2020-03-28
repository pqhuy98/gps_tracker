<?php
// Point model. For GPS coordinate.
// Provide database CRUD methods.
class Point_model extends CI_Model {
    public function __construct() {
		$this->load->database();
   	}

    // Read all GPS coordinates for a given user.
    public function read($username = NULL) {
        // SELECT * FROM point WHERE username = $username;
        $query = $this->db->get_where('point', array('username' => $username));
        $data = $query->result_array();

        return $data;
    }

    // Insert a GPS coordinate.
    // Auto set the point's timestamp to current time (see database schema).
    public function create($data) {
        $data = array(
            'username' => $data['username'],
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude']
        );

        // INSERT INTO point("username", "longitude", "latitude") VALUES(?, ?, ?);
        return $this->db->insert('point', $data);
    }
}