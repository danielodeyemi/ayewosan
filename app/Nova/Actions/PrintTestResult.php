<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Dompdf\Dompdf;
use Dompdf\Options;

class PrintTestResult extends Action
{
    use InteractsWithQueue, Queueable;

   /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Initialize an empty string to hold the test results HTML
        $testResultsHtml = '';

        foreach ($models as $model) {
            // Generate test results for $model and append it to $testResultsHtml
            $testResultsHtml .= view('test_results', ['labTestsResults' => $model])->render();
    
            // Add a page break after each test result except the last one
            if ($model !== $models->last()) {
                $testResultsHtml .= '<div style="page-break-after: always;"></div>';
            }
        }
    
        // Setup Dompdf options
        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
    
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($options);
    
        // Load the HTML to Dompdf
        $dompdf->loadHtml($testResultsHtml);
    
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
    
        // Render the HTML as PDF
        $dompdf->render();
    
        // Save the PDF to a non-public directory
        $filename = 'test_results.pdf';
        $directory = storage_path('app/test_results');
        
        // Create directory if it doesn't exist
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $filepath = $directory . '/' . $filename;
        file_put_contents($filepath, $dompdf->output());
    
        // Generate a URL for the file
        $fileUrl = route('nova.download', ['filename' => $filename]);
    
        // Return a download response
        return Action::download($fileUrl, $filename);
    }

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
