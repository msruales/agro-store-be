<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConvertResponseFieldsToCamelCase
{

    public function handle(Request $request, Closure $next)
    {
//        TODO NO ESTA IMPLEMENTADO
        $response = $next($request);
        $content = $response->getContent();

        try {
            $json = json_decode($content, true);
            $replaced = [];
            foreach ($json as $key => $value) {
                $replaced[Str::camel($key)] = $value;
            }
            $response->setContent($replaced);
        } catch (\Exception $e) {
            // you can log an error here if you want
        }

        return $response;
    }
}
