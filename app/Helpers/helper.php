<?php

function responseJson($msg, $code, $error, $errors = [], $key = 'fail')
{
    return response()->json([
        'key'             => $key,
        'msg'             => $msg ?? __('apis.data_retrieved_successfully'),
        'code'            => $code,
        'response_status' => [
            'error'             => $error,
            'validation_errors' => $errors
        ],
        'data'            => null,
    ], $code);
}
