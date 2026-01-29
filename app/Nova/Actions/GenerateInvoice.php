<?php

namespace App\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Illuminate\Support\Facades\App;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Collection;
use Dompdf\Dompdf;
use Dompdf\Options;

class GenerateInvoice extends Action
{
    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Initialize an empty string to hold the invoice HTML
        $invoiceHtml = '';

        foreach ($models as $model) {
            // Generate invoice for $model and append it to $invoiceHtml
            $invoiceHtml .= view('invoice', ['bills' => $model])->render();
    
            // Add a page break after each invoice except the last one
            if ($model !== $models->last()) {
                $invoiceHtml .= '<div style="page-break-after: always;"></div>';
            }
        }
    
        // Setup Dompdf options
        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
    
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($options);
    
        // Load the HTML to Dompdf
        $dompdf->loadHtml($invoiceHtml);
    
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
    
        // Render the HTML as PDF
        $dompdf->render();
    
        // Save the PDF to a non-public directory
        $filename = 'invoices.pdf';
        $filepath = storage_path('app/invoices/' . $filename);
        file_put_contents($filepath, $dompdf->output());
    
        // Generate a URL for the file
        $fileUrl = route('nova.download', ['filename' => $filename]);
    
        // Return a download response
        return Action::download($fileUrl, $filename);
    }

    //     return Action::message('Invoice generated successfully!');
    // }

    //     /**
    //  * Perform the action on the given models.
    //  *
    //  * @param  \Laravel\Nova\Fields\ActionFields  $fields
    //  * @param  \Illuminate\Support\Collection  $models
    //  * @return mixed
    //  */
    // public function handle(ActionFields $fields, Collection $models)
    // {
    //     // Here you can generate the invoice for each model in $models
    //     foreach ($models as $model) {
    //         // Generate invoice for $model
    //         $invoiceHtml = view('invoice', ['bills' => $model])->render();

    //         // Setup Dompdf options
    //         $options = new Options();
    //         $options->set('isRemoteEnabled', TRUE);

    //         // Instantiate Dompdf with our options
    //         $dompdf = new Dompdf($options);

    //         // Load the HTML to Dompdf
    //         $dompdf->loadHtml($invoiceHtml);

    //         // (Optional) Setup the paper size and orientation
    //         $dompdf->setPaper('A4', 'portrait');

    //         // Render the HTML as PDF
    //         $dompdf->render();

    //         // Save the PDF to a non-public directory
    //         $filename = 'invoice_' . $model->id . '.pdf';
    //         $filepath = storage_path('app/invoices/' . $filename);
    //         file_put_contents($filepath, $dompdf->output());

    //         // Generate a URL for the file
    //         $fileUrl = route('nova.download', ['filename' => $filename]);

    //         // Return a download response
    //         return Action::download($fileUrl, $filename);
    //     }

    //     return Action::message('Invoice generated successfully!');
    // }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}