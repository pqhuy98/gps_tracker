<?php

require APPPATH . 'libraries/REST_Controller.php';

class Point extends REST_Controller {

    // Construction method
    // to load database and model
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('user_model');
        $this->load->model('point_model');
    }

    // Get the BASIC username and password of the request, then check if they are correct.
    // If yes, return the username. If not, return NULL.
    private function checkCredential() {
        // Get header "Authorization"
        if ($this->input->get_request_header('Authorization') === NULL) {
            return NULL;
        }
        // Header content is in the form "Basic dTE6cDI=".
        // Value "dTE6cDI=" is base64 encoded of "username:password".
        // Now decode and explode the string to get username and password.
        $authHeader = explode(':' , base64_decode(substr($this->input->get_request_header('Authorization'), 6)));

        // The array $authHeader must contain 2 elements for username and password.
        if (count($authHeader) != 2) {
            return NULL;
        }
        $username = $authHeader[0];
        $password = $authHeader[1];

        // Check if $config["authentication"] is true. If not, no authentication check is needed, always allow access.
        if (getenv("AUTHENTICATION") === "false") {
            return $username;
        }

        // Validating
        if ($this->user_model->validate_password($username, $password)) {
            return $username;
        } else {
            return NULL;
        }
    }

    // Controller method for "GET /$username".
    // Get all points belong to a user with a specific username.
    public function index_get($username = NULL) {
        // Authentication check
        if ($this->checkCredential() === NULL) {
            $this->response(array("status" => "Unauthorized"), REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        // Real work

        $data = $this->point_model->read($username);

        if ($data === NULL) { // no data
            $this->response(array(), REST_Controller::HTTP_OK);
        } else { // some data
            $this->response($data, REST_Controller::HTTP_OK);
        }
    }

    // Controller method for "POST /".
    // Create a new user, given the username and password.
    public function index_post() {
        // Authentication check
        $username = $this->checkCredential();
        if ($username === NULL) {
            $this->response(array("status" => "Unauthorized"), REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        // Real work

        // Data from request's body.
        $data = $this->input->post();
        // Set key "username" to current authorized user.
        $data["username"] = $username;

        // Insert the point.
        $this->point_model->create($data);

        $this->response(['Point added successfully.'], REST_Controller::HTTP_OK);
    }
}