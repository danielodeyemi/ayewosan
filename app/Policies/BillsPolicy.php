<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class BillsPolicy extends BasePolicy
{
    public $key = 'Bills';

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
