<?php

namespace App\Core\Abstract;
use  App\Core\Session;

abstract class AbstractController extends Singleton
{

    // protected Session $session;

    protected function __construct()
    {
        parent::__construct();
        // $this->session = Session::getInstance();
    }

    abstract public function index();

    abstract public function store();

    abstract public function create();


    abstract public function destroy();

    abstract public function show();

    abstract public function edit();



    protected function renderJson(array $data = null, string $status = "success", int $code = 200, string $message = ""): void
    {
        header('Content-Type: application/json');
        http_response_code($code);
        
        $response = [
            'data' => $data,
            'statut' => $status,
            'code' => $code,
            'message' => $message
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }









}
