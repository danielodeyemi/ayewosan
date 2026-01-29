<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class LabTestsGroupPolicy extends BasePolicy
{
    public $key = 'LabTestsGroup';

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
