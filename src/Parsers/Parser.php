<?php

declare(strict_types=1);

namespace UnicoETL\Parsers;

/**
 * Class Parser
 * Implements the abstract foundation for all parsers that 
 * may be created.
 * 
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
interface Parser
{
    /** Function for parsing and evaluating a content */
    public function parse(mixed $content): void;

    /** Function for outputting or transfering the parser content */
    public function output(): void;
}
