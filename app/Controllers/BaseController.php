<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use Psr\Log\LoggerInterface;

use App\Models\UsersModel;
use App\Models\LoginsModel;

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
     * @var UsersModel
     */
    protected $usersModel;

    /**
     * @var LoginsModel
     */
    protected $loginsModel;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->usersModel = new UsersModel;
        $this->loginsModel = new LoginsModel;
    }

    protected function isDBConnected()
    {
        try {
            $db = \Config\Database::connect();
            $db->initialize();
            return true;
        } catch (DatabaseException) {
            return false;
        }
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
