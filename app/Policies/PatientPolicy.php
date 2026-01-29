<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Sereny\NovaPermissions\Policies\BasePolicy;

class PatientPolicy extends BasePolicy
{
    public $key = 'Patient';

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
