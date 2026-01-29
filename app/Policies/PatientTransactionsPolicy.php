<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class PatientTransactionsPolicy extends BasePolicy
{
    public $key = 'PatientTransactions';
    
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
