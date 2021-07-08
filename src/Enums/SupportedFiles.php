<?php

declare(strict_types=1);

namespace UnicoETL\Enums;

/**
 * Enum SupportedFiles
 * Describes the available files for the ETL process.
 *
 * Since enums are only available on PHP 8.1, I'm still using classes for this.
 *
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
final class SupportedFiles extends Enum
{
    public const FAIRS_2014 = 'DEINFO_AB_FEIRASLIVRES_2014.csv';
}
