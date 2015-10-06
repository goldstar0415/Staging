<?php

namespace App\Contracts;

use App\User;

/**
 * Interface CalendarExportable
 * @package App\Contracts
 *
 * Contains methods for ics export
 */
interface CalendarExportable
{
    /**
     * Retrieve all calendar exportable items for the user
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function exportableEvents(User $user);

    /**
     * Conditions for retrieve exportable models
     *
     * @return mixed
     */
    public static function exportableConditions();

    /**
     * Get exportable items
     *
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
