<?php namespace NetForceWS\Http;

use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait Error
{
    public function error(\Exception $exception, $json = false)
    {
        // Preparar retorno
        $error          = new \stdClass();
        $error->error   = true;
        $error->code    = $exception->getCode();
        $error->message = $exception->getMessage();
        $error->attrs   = [];

        // Verificar se eh NotFoundHttpException
        if (is_a($exception, '\Symfony\Component\HttpKernel\Exception\NotFoundHttpException'))
        {
            $error->code    = ($error->code <= 0) ? 404 : $error->code;
            $error->message = ($error->message == '') ? 'Not Found' : $error->message;
        }

        // Verificar se eh MethodNotAllowedHttpException
        if (is_a($exception, '\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException'))
        {
            $error->code    = ($error->code <= 0) ? 405 : $error->code;
            $error->message = ($error->message == '') ? 'Method Not Allowed' : $error->message;
        }

        // Verificar se já eh um HttpResponseException
        if (is_a($exception, '\Illuminate\Http\Exception\HttpResponseException'))
        {
            if ($json != true)
                throw $exception;
            else
                $error->attrs = json_decode($exception->getResponse()->getContent(), true);
        }

        // Verificar se um erro de validacao
        if (is_a($exception, '\Illuminate\Validation\ValidationException'))
        {
            $msgs = $exception->validator->messages()->messages();
            $error->attrs = [];
            foreach ($msgs as $key => $lines)
            {
                $error->attrs[$key] = '';
                foreach ($lines as $line)
                {
                    $error->attrs[$key] .= sprintf("%s\n", $line);
                }
                $error->attrs[$key] = trim($error->attrs[$key]);
            }
        }

        // Verificar se um erro com atributos
        if (is_a($exception, '\NetForceWS\Support\ExceptionAttributes'))
        {
            $error->attrs = $exception->getAttrs();
        }

        // Verificar se deve retornar em formato JSON
        if ($json)
            return response()->json($error, 200, [], JSON_UNESCAPED_UNICODE);

        // Gerar erro
        $request = app('request');
        throw new HttpResponseException($this->buildErrorResponse($request, $error));
    }

    protected function buildErrorResponse(Request $request, $error)
    {
        if (($request->ajax() && ! $request->pjax()) || $request->wantsJson()) {
            return new JsonResponse($error, 422);
        }

        return redirect()->to($this->getErrorRedirectUrl())
            ->withInput($request->input());
            //->withErrors($errors, $this->errorBag());
    }

    protected function getErrorRedirectUrl()
    {
        return app('Illuminate\Contracts\Routing\UrlGenerator')->previous();
    }
}