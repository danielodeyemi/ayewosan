<?php

namespace App\Policies;

use App\Models\User;
use Sereny\NovaPermissions\Policies\BasePolicy;

class ReferralTransactionsPolicy extends BasePolicy
{
    public $key = 'ReferralTransactions';

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
