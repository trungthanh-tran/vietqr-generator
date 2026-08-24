<?php

namespace tttran\viet_qr_generator;

/**
 * JSON response envelope returned by the generator.
 */
class Response implements \JsonSerializable
{
    const SUCCESSFUL_CODE = 200;
    const INVALID_PARAMETERS = 400;
    const SERVER_ERROR = 500;

    /** Response code (see class constants). */
    private $code;

    /** Human-readable description. */
    private $desc;

    /** Payload: QR text, base64 image or bank list. */
    private $data;

    public function __construct($code, $desc, $data)
    {
        $this->code = $code;
        $this->desc = $desc;
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
