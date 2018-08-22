<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 22-08-18
 * Time: 15:09
 */

namespace edwrodrig\google_utils;


class Util
{
    /**
     * Get a range in A1 notation
     *
     * This does not includes the sheet name
     * @param string $startColumnIndex
     * @param int $startRowIndex
     * @param string $endColumnIndex
     * @param int|null $endRowIndex
     * @return string
     */
    public static function range(string $startColumnIndex, int $startRowIndex, string $endColumnIndex, ?int $endRowIndex = null) : string {
        $endRowIndex = $endRowIndex ?? '';

        return sprintf("%s%s:%s%s",
            $startColumnIndex,
            strval($startRowIndex),
            $endColumnIndex,
            $endRowIndex ?? '');
    }
}