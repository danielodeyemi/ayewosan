<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Bills;
use Laravel\Nova\Notifications\NovaNotification;

class BillsObserver
{
    /**
     * Handle the Bills "created" event.
     */
    public function created(Bills $bills): void
    {
        $this->getNovaNotification($bills, 'New Bill Created for Patient: ', 'success');

    }

    /**
     * Handle the Bills "updated" event.
     */
    public function updated(Bills $bills): void
    {
        $this->getNovaNotification($bills, 'Bill Updated for Patient: ', 'info');
    }

    /**
     * Handle the Bills "deleted" event.
     */
    public function deleted(Bills $bills): void
    {
        $this->getNovaNotification($bills, 'Bill Updated for Patient: ', 'danger');
    }

    /**
     * Handle the Bills "restored" event.
     */
    public function restored(Bills $bills): void
    {
        $this->getNovaNotification($bills, 'Bill Updated for Patient: ', 'info');
    }

    /**
     * Handle the Bills "force deleted" event.
     */
    public function forceDeleted(Bills $bills): void
    {
        $this->getNovaNotification($bills, 'Bill Updated for Patient: ', 'danger');
    }

    private function getNovaNotification($bills, $message, $type): void
    {
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('id', [1, 4]);
        })->get();

        foreach ($users as $user) {
            $user->notify(
                NovaNotification::make()
                    // ->message('New Bill: ' . $bills->id . ' Created for Patient' . $bills->patient->name)
                    ->message($message . ' ' . $bills->patient->name . ' by ' . $bills->processedBy->name . '. Bill Number is: ' . $bills->id)
                    ->icon('document-text')
                    ->type('success')
            );
        }
    }
}
