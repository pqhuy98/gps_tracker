<?php
// User model
// Provide database CRUD methods.
class User_model extends CI_Model {
    public function __construct() {
		$this->load->database();
   	}

    // Read all users from database, or one specific user.
	public function read($username = NULL) {
        if ($username === NULL) { // No specific useranme given, retrieve all users.
            // SELECT * FROM user;
            $query = $this->db->get('user');
            $data = $query->result_array();

            // Hide password fields so it won't leak password hashes.
            foreach ($data as $i => $d) {
                unset($data[$i]["password"]);
            }

            return $data;
        } else {
            // SELECT * FROM user WHERE username = $username;
            $query = $this->db->get_where('user', array('username' => $username));
            $data = $query->row_array();

            // Hide password
            unset($data["password"]);

            return $data;
        }
	}

    // Create a user given the username and password.
    public function create($data) {
        // Hash password using PHP's standard password_hash().
        $password = password_hash($data['password'], PASSWORD_BCRYPT);

        // Row's values to insert.
        $data = array(
            'username' => $data['username'],
            'password' => $password,
        );

        // INSERT INTO user("username", "password") VALUES($username, $password);
        return $this->db->insert('user', $data);
    }

    // Validate password using username and password.
    // Return true if credential is valid, false otherwise.
    public function validate_password($username, $password) {
        // SELECT * FROM user WHERE username = $username;
        $query = $this->db->get_where('user', array('username' => $username));
        $user = $query->row_array();

        // No user found !
        if ($user === NULL) {
            return false;
        }

        // Use PHP's standard password_verify();
        // Verify entered password with the hash created from password_hash().
        return password_verify($password, $user["password"]);
    }

    // public function delete($username) {
    //     // DELETE FROM user WHERE username = $username
    //     return $this->db->delete('user', array('username'=>$username));
    // }
}