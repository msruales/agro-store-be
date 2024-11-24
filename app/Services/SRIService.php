<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class SRIService
{
    public static function checkStatus($accessKey)
    {
        $response = Http::get(env('API_FAC_URL', '') . "/autorizacion/$accessKey");
        Log::info('Respuesta de chekStatus', ['response' => $response]);

        if ($response->successful()) {
            $data = $response->json();

            $numeroComprobantes = $data['RespuestaAutorizacionComprobante']['numeroComprobantes'];

            if ($numeroComprobantes > 0) {
                $status = $data['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['estado'];
                $fechaAutorizacion = $data['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['fechaAutorizacion'];
                $ambiente = $data['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['ambiente'];
                $comprobante = $data['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['comprobante'];

                $mensaje = null;
                if($data['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes']){
                    $mensajes =$data['RespuestaAutorizacionComprobante']['autorizaciones']['autorizacion']['mensajes'];
                    $mensaje = $mensajes['mensaje']['informacionAdicional'];
                }
                return [
                    'ok' => true,
                    'data' => [
                        'status' => $status,
                        'date' => $fechaAutorizacion,
                        'env' => $ambiente,
                        'voucher' => $comprobante,
                    ],
                    'messages' => $mensaje
                ];
            }
        } else {
            return [
                'ok' => false,
                'messages' => 'Error del servidor'
            ];
        }

    }

    public static function sendInvoice($factura): array
    {
        $response = Http::post(env('API_FAC_URL', '') . "/factura", $factura);
        Log::info('Respuesta de sendInvoice', ['response' => $response]);

        if ($response->successful()) {

            Log::info('INGRESO AL SUCCESS');

            $data = $response->json();

            $messages =null;

            $signedInvoiceXml = $data['signedInvoiceXml'];
            $accessKey = $data['accessKey'];

            $status = $data['response']['RespuestaRecepcionComprobante']['estado'];

            if(isset($data['response']['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje'])){
                $mensaje = $data['response']['RespuestaRecepcionComprobante']['comprobantes']['comprobante']['mensajes']['mensaje'];

//                $identificador = $mensaje['identificador'] ?? null; // Usar null si no existe
                $messages = $mensaje['mensaje'] ?? null; // Usar null si no existe
//                $tipo = $mensaje['tipo'] ?? null; // Usar null si no existe

            }

            $dateRes = [
                'data' => [
                    'signedInvoiceXml' => $signedInvoiceXml,
                    'accessKey' => $accessKey,
                    'status' => $status,
                    'messages' => $messages
                ],
                'ok' => true
            ];

            Log::info('RESPUESTA SENDINVOICE MODIFICADA', ['DATA'=>$dateRes]);


            return $dateRes;
        } else {
            return [
                'ok' => false
            ];
        }

    }

    public static function storeXml(string $accessKey, string $xml, string $status): bool
    {
        if (!simplexml_load_string($xml)) {
            throw new \Exception('No es un XML válido');
        }

        try {
            Storage::disk('local')->put("private/certificates/$status/" . $accessKey . '.xml', $xml);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
