<?php

namespace Every8d\Message;

class MMS extends SMS
{
    /**
     * @var string Message subject
     */
    public $subject;

    /**
     * @var string Image file, binary base64 encoded
     */
    public $attachment;

    /**
     * @var string Image file extension, support jpg/jpeg/png/git
     */
    public $type;

    protected $map = [
        'to' => 'DEST',
        'subject' => 'SB',
        'content' => 'MSG',
        'attachment' => 'ATTACHMENT',
        'type' => 'TYPE',
        'reservationTime' => 'ST',
        'retryTime' => 'RETRYTIME',
        'id' => 'MR',
    ];

    public function __construct(string $to, string $subject, string $content, string $attachment, string $type)
    {
        parent::__construct($to, $content);
        $this->subject = $subject;
        $this->attachment = $attachment;
        $this->type = $type;
    }
}
