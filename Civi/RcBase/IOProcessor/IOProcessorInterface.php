<?php

namespace Civi\RcBase\IOProcessor;

/**
 * General input/output processor interface
 */
interface IOProcessorInterface
{
    /**
     * Decode input
     *
     * @param string $input String to parse
     *
     * @return mixed
     */
    public function decode(string $input);

    /**
     * Decode input stream
     *
     * @param string $stream Name of input stream
     *
     * @return mixed
     */
    public function decodeStream(string $stream);

    /**
     * Decode POST request body
     *
     * @return mixed
     */
    public function decodePost();
}
