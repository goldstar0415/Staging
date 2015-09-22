<?php

namespace App\Contracts;

use App\User;

interface CalendarExportable
{
    /**
     * Retrieve all calendar exportable items
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function exportableEvents(User $user);

    /**
     * @return mixed
     */
    public static function exportableConditions();

    /**
     * @param User $user
     * @return \Generator
     */
    public static function exportable(User $user);

    /**
     * Exports current model
     * @return mixed
     */
    public function export();
}