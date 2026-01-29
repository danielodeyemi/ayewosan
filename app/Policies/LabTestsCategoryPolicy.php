<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class LabTestsCategoryPolicy extends BasePolicy
{
        public $key = 'LabTestsCategory';

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
