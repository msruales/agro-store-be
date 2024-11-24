<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Services\SRIService;
use Illuminate\Console\Command;

class VerifyIvoiceAuthorization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Invoice:verify-authorization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la autorizacion de las facturas emitidas';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $invoices = Bill::where('status', 'Recibida')->get();

        foreach ($invoices as $invoice) {
            try {
                $status = SRIService::checkStatus($invoice->accessKey);

                if ($status === 'Autorizada') {
                    $invoice->status_voucher = 'Autorizada';
                    // Guardar el comprobante autorizado
                } elseif ($status === 'No Autorizada') {
                    $invoice->status_voucher = 'No Autorizada';
                    // Manejar errores y notificar si es necesario
                }
                $invoice->save();
                return Command::SUCCESS;
            } catch (\Exception $e) {
                // Manejar excepciones y registrar logs
                \Log::error('Error al verificar la factura ' . $invoice->id . ': ' . $e->getMessage());
                return Command::FAILURE;
            }
        }




    }
}
