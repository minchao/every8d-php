<?php

namespace Every8d\Message;

class MMS extends SMS
{
    /**
     * @var string Message title
     */
    public $SB;

    /**
     * @var string Image file, binary base64 encoded
     */
    public $ATTACHMENT;

    /**
     * @var string Image file extension, support jpg/jpeg/png/git
     */
    public $TYPE;
}
