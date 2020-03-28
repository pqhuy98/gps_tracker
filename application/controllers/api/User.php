<?php
   
require APPPATH . 'libraries/REST_Controller.php';
     
class User extends REST_Controller {

    // Construction method
    // to load database and model
    public function __construct() {
       parent::__construct();
       $this->load->database();
       $this->load->model('user_model');
    }

    // Controller method for "GET api/user" and "GET api/user/$username".
    // Get all users, or one user with a specific username.
	public function index_get($username = NULL)	{
        $data = $this->user_model->read($username);

        if ($data === NULL) {
            // no user found
            $this->response(array("status" => "No user found."), REST_Controller::HTTP_NOT_FOUND);
        } else {
            // found !
            $this->response($data, REST_Controller::HTTP_OK);
        }
	}

    // Controller method for "POST api/user".
    // Create a new user, given the username and password.
    public function index_post() {
        // Get the request's body.
        $data = $this->input->post();

        // Create user
        $this->user_model->create($data);
     
        $this->response(['User created successfully.'], REST_Controller::HTTP_OK);
    } 

    // // Controller method for "DELETE api/user/$username"
    // // Delete a user given the username.
    // public function index_delete($username) {
    //     $this->user_model->delete($username);
       
    //     $this->response(['User deleted successfully.'], REST_Controller::HTTP_OK);
    // }
}