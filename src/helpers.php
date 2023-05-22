<?php

declare(strict_types=1);

if (!\function_exists('camelize')) {
    /**
     * Converts a word like "send_email" to "SendEmail".
     *
     * @param string $value
     *
     * @return string
     */
    function camelize(string $value): string
    {
        return \str_replace(['_', '-', ' '], '', \ucwords($value, '_- '));
    }
}
