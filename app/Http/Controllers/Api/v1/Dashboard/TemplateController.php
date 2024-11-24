<?php
namespace App\Http\Controllers\Api\v1\Dashboard;
use App\Http\Controllers\Api\ApiController;
use App\Models\Person;
use App\Models\Setting;
use App\Models\TemplateVariable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

class TemplateController extends ApiController
{
    public function uploadDocx(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:docx|max:2048', // Máximo 2MB
            ]);

            $file = $request->file('file');
            $name = $file->getClientOriginalName();
            $filePath = $file->store('uploads/template-certificate', 'public');

            $this->CreateOrReplace($filePath, $name);

            $variables = $this->extractVariablesFromDocx($filePath);

            $this->saveVariables($filePath, $name, $variables);


            return $this->successResponse([
                'message' => 'File uploaded successfully!',
                'path' => $filePath,
                'name' => $name,
                'variables' => $variables,
                'ok' => true
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse([
                'ok' => false
            ]);
        }

    }

    function CreateOrReplace($path, $name): void
    {
        $configPath = Setting::where('key', 'template_certificate_url')->first();

        if (!$configPath) {
            $newSetting = new Setting();
            $newSetting->key = 'template_certificate_url';
            $newSetting->value = $path;
            $newSetting->save();
        } else {
            $configPath->value = $path;
            $configPath->update();
        }

        $configName = Setting::where('key', 'template_certificate_name')->first();

        if (!$configName) {
            $newSetting = new Setting();
            $newSetting->key = 'template_certificate_name';
            $newSetting->value = $name;
            $newSetting->save();
        } else {
            $configName->value = $name;
            $configName->update();
        }
    }

    public function extractVariablesFromDocx(string $relativePath): array
    {
        $filePath = storage_path('app/public/' . $relativePath);
        // Asegúrate de usar la excepción genérica
        if (!file_exists($filePath)) {
            throw new \Exception("El archivo no existe: $filePath");
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception("No se pudo abrir el archivo DOCX");
        }

        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$content) {
            throw new \Exception("No se pudo leer el contenido del archivo DOCX");
        }

        // Buscar las variables en el contenido XML
        preg_match_all('/\$\{(.*?)\}/', $content, $matches);
        return array_unique($matches[1]);
    }

    function getVariablesOfTemplate(): \Illuminate\Http\JsonResponse
    {
        try {
            $path = Setting::where('key', 'template_certificate_url')->first();

            if (!$path || !$path->value) {
                return $this->errorResponse([
                    'ok' => false,
                    'message' => 'No existe un template'
                ]);
            }

            $pathValue = $path->value;

            $variables = $this->extractVariablesFromDocx($pathValue);

            return $this->successResponse([
                'ok' => true,
                'variables' => $variables
            ]);

        } catch (\Exception $e) {
            return $this->errorResponse([
                'ok' => false,
            ]);
        }

    }

    public function saveVariables($key, $name, $variables): void
    {
        foreach ($variables as $variable) {
            $new = new TemplateVariable();
            $new->template_id = $key;
            $new->template_name = $name;
            $new->variable_name = $variable;
            $new->field_name = '';
            $new->save();
        }
    }

    function getInfoTemplate(): \Illuminate\Http\JsonResponse
    {
        $path = Setting::where('key', 'template_certificate_url')->first();
        $name = Setting::where('key', 'template_certificate_name')->first();

        if (!$path || !$path->value || !$name || !$name->value) {
            return $this->errorResponse([
                'ok' => false,
                'message' => 'No existe un template'
            ], 200);
        }

        return $this->successResponse([
            'ok' => true,
            'name' => $name->value,
            'path' => $path->value
        ]);
//        return $this->successResponse($setting);
    }

    function deleteTemplate(): \Illuminate\Http\JsonResponse
    {
        try {
            $path = Setting::where('key', 'template_certificate_url')->first();
            $name = Setting::where('key', 'template_certificate_name')->first();
            TemplateVariable::where('template_id', $path->value)->delete();

            if (Storage::disk('public')->exists($path->value)) {
                Storage::disk('public')->delete($path->value);
            }

            if ($path) $path->delete();
            if ($name) $name->delete();


            return $this->successResponse();
        } catch (\Exception $e) {
            return $this->errorResponse();
        }
    }

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     * @throws \Exception
     */
    function generateCertificate(Request $request, $id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
//        try {
            $amount = $request->get('amount') ?? '';

            $client = Person::where('id', $id)->first();

            $path = Setting::where('key', 'template_certificate_url')->first();

            $templateProcessor = new TemplateProcessor(storage_path('app/public/' . $path->value));

            $variables = $this->extractVariablesFromDocx($path->value);

            foreach ($variables as $variable) {

                $templateProcessor->setValue($variable, $this->getValByVariable($variable, $client, $amount));
            }

            $nameClient = mb_convert_case($client->full_name, MB_CASE_TITLE, "UTF-8");

            $nameCertificate = "Certificado $nameClient.docx";

            $outputPath = storage_path("app/private/certificates/$nameCertificate");

            $templateProcessor->saveAs($outputPath);

            return response()->download($outputPath, $nameCertificate,[
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
            ])->deleteFileAfterSend(true);
//        } catch (Exception $e) {
//            return $this->errorResponse();
//        }
    }

    function getValByVariable($variable, $client, $amount)
    {
        return match ($variable) {
            'nombre' => mb_convert_case($client->full_name, MB_CASE_TITLE, "UTF-8"),
            'numero_identificacion' => $client->document_number,
            'fecha' => Carbon::now()->format('d/m/Y'),
            'monto' => $amount,
            default => 'No-data'
        };
    }
}
