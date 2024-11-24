<?php

namespace App\Http\Controllers\Api\v1\Dashboard;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Setting\StoreUpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class SettingController extends ApiController
{
    public function index()
    {
        $data = Setting::whereIn('key', $this->validKeys())->get();
        return $this->successResponse($data);
    }


    public function validKeys(): array
    {
        return [
            'razonSocial',
            'nombreComercial',
            'ruc',
            'codEstablecimiento',
            'codPtoEmision',
            'dirMatriz',
            'dirEstablecimiento',
            'obligadoContabilidad',
            'passwordFirma',
            'nameFirma'
        ];
    }

    public function isValidKey($key): bool
    {
        return in_array($key, $this->validKeys(), true);
    }

    public function canCreateInvoice()
    {
        $settingsArray = Setting::whereIn('key', $this->validKeys())->get()->toArray();

        $settings = array_reduce($settingsArray, function ($carry, $item) {
            return [
                ...$carry,
                $item['key'] => $item['value']
            ];
        }, []);

        if (
            (isset($settings['razonSocial']) && $settings['razonSocial'] != '')
            && (isset($settings['nombreComercial']) && $settings['nombreComercial'] != '')
            && (isset($settings['ruc']) && $settings['ruc'] != '')
            && (isset($settings['codEstablecimiento']) && $settings['codEstablecimiento'] != '')
            && (isset($settings['codPtoEmision']) && $settings['codPtoEmision'] != '')
            && (isset($settings['dirMatriz']) && $settings['dirMatriz'] != '')
            && (isset($settings['dirEstablecimiento']) && $settings['dirEstablecimiento'] != '')
            && (isset($settings['obligadoContabilidad']) && $settings['obligadoContabilidad'] != '')
            && (isset($settings['passwordFirma']) && $settings['passwordFirma'] != '')
            && (isset($settings['nameFirma']) && $settings['nameFirma'] != '')
        ) {
            return $this->successResponse(true);
        }

        return $this->successResponse(false);

    }

    public function storeOrUpdate(StoreUpdateSettingRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $arrayData = $data['data'];

        foreach ($arrayData as $item) {
            $res = $this->isValidKey($item['key']);
            if (!$res) {
                return $this->errorResponse($item['key'] . " is not valid");
            }
        }

        foreach ($arrayData as $item) {
            $setting = Setting::where('key', $item['key'])->first();
            if ($setting) {
                $setting->value = $item['key'] === 'passwordFirma' ? Crypt::encryptString($item['value']) : $item['value'];
                $setting->update();
            } else {
                $newSetting = new Setting();
                $newSetting->key = $item['key'];
                $newSetting->value = $item['key'] === 'passwordFirma' ? Crypt::encryptString($item['value']) : $item['value'];;
                $newSetting->save();
            }
        }

        return $this->successResponse();

    }

    public function uploadSignature(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|max:2048', // Solo archivos .p12 de hasta 2 MB
            ]);

            $file = $request->file('file');

            $filename = uniqid() . '_' . $file->getClientOriginalName();

            $file->storeAs('private/sign', $filename);

            $existNameFirma = Setting::where('key', 'nameFirma')->first();


            if ($existNameFirma) {
                if (Storage::disk('local')->exists('private/sign/' . $existNameFirma->value)) {
                    Storage::disk('local')->delete('private/sign/' . $existNameFirma->value);
                }
                $existNameFirma->value = $filename;
                $existNameFirma->update();
            } else {
                $nameFirma = new Setting();
                $nameFirma->key = 'nameFirma';
                $nameFirma->value = $filename;
                $nameFirma->save();
            }

            return $this->successResponse([
                'ok' => true,
                'filename' => explode("_", $filename)[1]
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'ok' => false
            ]);
        }

    }

    public function checkSign(): \Illuminate\Http\JsonResponse
    {
        $existNameFirma = Setting::where('key', 'nameFirma')->first();

        if (isset($existNameFirma) && $existNameFirma->value != '') {

            return $this->successResponse([
                'ok' => true,
                'filename' => explode("_", $existNameFirma->value)[1]
            ]);
        } else {
            return $this->successResponse([
                'ok' => false
            ]);

        }
    }

    function deleteSignature(): \Illuminate\Http\JsonResponse
    {
        try {
            $existNameFirma = Setting::where('key', 'nameFirma')->first();

            if($existNameFirma){
                if (Storage::disk('local')->exists('private/sign/' . $existNameFirma->value)) {
                    Storage::disk('local')->delete('private/sign/' . $existNameFirma->value);
                }
            }

            if ($existNameFirma) $existNameFirma->delete();
            if ($existNameFirma) $existNameFirma->delete();


            return $this->successResponse();
        } catch (Exception $e) {
            return $this->errorResponse();
        }
    }


}
