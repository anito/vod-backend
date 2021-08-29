<?php
namespace App\Error;

use Cake\Http\Response;
use Crud\Error\ExceptionRenderer as ErrorExceptionRenderer;
use Crud\Error\Exception\ValidationException;
use Exception;

/**
 * Exception renderer for ApiListener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AppExceptionRenderer extends ErrorExceptionRenderer
{

    /**
     * Renders validation errors and sends a 422 error code
     *
     * @param \Crud\Error\Exception\ValidationException $error Exception instance
     * @return \Cake\Http\Response
     */
    public function validation(ValidationException $error): Response
    {
        $url = $this->controller->request->getRequestTarget();
        $status = $code = $error->getCode();
        try {
            $this->controller->response = $this->controller->response->withStatus($status);
        } catch (Exception $e) {
            $status = 422;
            $this->controller->response = $this->controller->response->withStatus($status);
        }

        $sets = [
            'code' => $code,
            'url' => h($url),
            'message' => $error->getMessage(),
            'error' => $error,
            'errorCount' => $error->getValidationErrorCount(),
            'errors' => $error->getValidationErrors(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption('serialize', ['code', 'url', 'message', 'errorCount', 'errors']);

        return $this->_outputMessage('error400');
    }

    public function expired($error)
    {
        $status = $code = $error->getCode();
        try {
            $this->controller->response = $this->controller->response->withStatus($status);
        } catch (Exception $e) {
            $status = 401;
            $this->controller->response = $this->controller->response->withStatus($status);
        }

        $sets = [
            'code' => $code,
            'message' => $error->getMessage(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption('serialize', ['code', 'message']);

        return $this->_outputMessage('error400');

    }

    public function domain($error)
    {
        $status = $code = $error->getCode();
        try {
            $this->controller->response = $this->controller->response->withStatus($status);
        } catch (Exception $e) {
            $status = 401;
            $this->controller->response = $this->controller->response->withStatus($status);
        }

        $sets = [
            'code' => $code,
            'message' => $error->getMessage(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption('serialize', ['code', 'message']);

        return $this->_outputMessage('error400');

    }

    public function beforeValid($error)
    {
        $status = $code = $error->getCode();
        try {
            $this->controller->response = $this->controller->response->withStatus($status);
        } catch (Exception $e) {
            $status = 401;
            $this->controller->response = $this->controller->response->withStatus($status);
        }

        $sets = [
            'code' => $code,
            'message' => $error->getMessage(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption('serialize', ['code', 'message']);

        return $this->_outputMessage('error400');
    }

    public function unexpectedValue($error)
    {
        $status = $code = $error->getCode();
        try {
            $this->controller->response = $this->controller->response->withStatus($status);
        } catch (Exception $e) {
            $status = 401;
            $this->controller->response = $this->controller->response->withStatus($status);
        }

        $sets = [
            'code' => $code,
            'message' => $error->getMessage(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption('serialize', ['code', 'message']);

        return $this->_outputMessage('error400');
    }

    public function signatureInvalid($error)
    {
        $status = $code = $error->getCode();
        try {
            $this->controller->response = $this->controller->response->withStatus($status);
        } catch (Exception $e) {
            $status = 401;
            $this->controller->response = $this->controller->response->withStatus($status);
        }

        $sets = [
            'code' => $code,
            'message' => $error->getMessage(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption('serialize', ['code', 'message']);

        return $this->_outputMessage('error400');
    }

    public function invalidArgument($error)
    {
        $status = $code = $error->getCode();
        try {
            $this->controller->response = $this->controller->response->withStatus($status);
        } catch (Exception $e) {
            $status = 401;
            $this->controller->response = $this->controller->response->withStatus($status);
        }

        $sets = [
            'code' => $code,
            'message' => $error->getMessage(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption('serialize', ['code', 'message']);

        return $this->_outputMessage('error400');
    }
}
