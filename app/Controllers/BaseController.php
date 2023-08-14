<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 */
abstract class BaseController extends Controller
{
    use ResponseTrait;

    /**
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * @var array
     */
    protected $helpers = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    protected function respondWith($success = 1, $message = null, $data = null, $status = 200)
    {
        $finalResponse = ['success' => $success];

        if ($message != null)
            $finalResponse['message'] = $message;

        if ($data != null)
            $finalResponse['data'] = $data;

        return $this->respond($finalResponse, $status);
    }
}
