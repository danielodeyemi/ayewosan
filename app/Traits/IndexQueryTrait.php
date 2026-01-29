<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Laravel\Nova\Http\Requests\NovaRequest;

trait IndexQueryTrait
{
    public static function indexQuery(NovaRequest $request, $query)
    {
        $modelName = class_basename(self::class); // Get the class name without namespace

        // If user can view any record, show all
        // If user can only view own records, filter by the foreign key
        if (!$request->user()->can('viewAny' . $modelName) && $request->user()->can('viewOwn' . $modelName)) {
            $query = $query->where(self::$userForeignKey, $request->user()->id);
        }

        return $query;
    }
}