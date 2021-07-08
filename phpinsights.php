<?php

declare(strict_types=1);

return [
    'preset' => 'default',
    'config' => [
        \SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff::class => [
            'maxLinesLength' => 30,
        ],
    ],
    'remove' => [
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff::class,
        \SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff::class,
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class,
    ],
];
