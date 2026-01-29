<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class LabTestsResultsTemplatePolicy extends BasePolicy
{
    public $key = 'LabTestsResultsTemplate';
    
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
