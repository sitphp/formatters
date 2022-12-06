<?php

namespace SitPHP\Formatters\Formatters;


class RawFormatter extends Formatter
{

    function format(string $message, int $width = null): string
    {
        if (null === $width) {
            return $message;
        }
        return $this->mb_chunk_split($message, $width);
    }

    function unFormat(string $message): string
    {
        return $message;
    }

    /**
     * @param string $string
     * @param int $chunklen
     * @param string $end
     * @return string
     */
    protected function mb_chunk_split(string $string, int $chunklen = 76, string $end = PHP_EOL): string
    {
        if ($chunklen <= 0) {
            return $string;
        }
        $chars = preg_split("//u", $string, null, PREG_SPLIT_NO_EMPTY);
        $array = array_chunk($chars, $chunklen);
        $string_chunks = [];
        foreach ($array as $item) {
            $string_chunks[] = implode('', $item);
        }
        return implode($end, $string_chunks);
    }
}