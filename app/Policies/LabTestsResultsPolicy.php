<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class LabTestsResultsPolicy extends BasePolicy
{
    public $key = 'LabTestsResults';
    
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
