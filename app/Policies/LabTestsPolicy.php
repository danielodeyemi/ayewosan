<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class LabTestsPolicy extends BasePolicy
{
    public $key = 'LabTests';

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
