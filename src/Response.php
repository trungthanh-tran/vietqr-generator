<?php


namespace tttran\viet_qr_generator;


class Response
{
    private $code;
    private $desc;
    private $data;

    const SUCCESSFUL_CODE = 200;
    const INVALID_PARAMETERS = 400;
    const SERVER_ERROR = 500;

    public function __construct ($code, $desc, $data) {
        $this->code = $code;
        $this->desc = $desc;
        $this->data = $data;
        return $this;
    }

    function toString() {
        return json_encode($this);
    }
}