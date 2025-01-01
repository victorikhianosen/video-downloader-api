<?php

namespace App\Traits;

trait HttpResponses
{
    public function success($message = 'Request successful.', $data = null, $code = 200, $meta = null)
    {
        $res = [
            'status' => 'success',
            'message' => $message,
        ];

        if ($data) $res['data'] = $data;
        if ($meta) $res['meta'] = $meta;

        return response()->json($res, $code);
    }

    public function error($errors = [], $message = 'Request failed.', $code = 400)
    {
        $res = [
            'status' => 'failed',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $res['errors'] = $errors;
        }

        return response()->json($res, $code);
    }


    
}
