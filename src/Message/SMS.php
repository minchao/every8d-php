<?php

namespace Every8d\Message;

class SMS implements MessageInterface
{
    /**
     * @var string Message subject (Optional)
     *             The subject will not be sent with the SMS; it is just a note
     */
    public $subject;

    /**
     * @var string Message content
     */
    public $content;

    /**
     * @var string The destination phone number
     */
    public $to;

    /**
     * @var string Reservation time (Optional)
     *             Format: yyyyMMddHHmnss, e.g. 20090131153000
     */
    public $reservationTime;

    /**
     * @var int SMS validity period of unit: minutes (Optional)
     *          If not specified, then the platform default validity period is 1440 minutes
     */
    public $retryTime;

    /**
     * @var string Message record ID (Optional)
     */
    public $id;

    protected $map = [
        'to' => 'DEST',
        'subject' => 'SB',
        'content' => 'MSG',
        'reservationTime' => 'ST',
        'retryTime' => 'RETRYTIME',
        'id' => 'MR',
    ];

    public function __construct(string $to, string $content)
    {
        $this->to = $to;
        $this->content = $content;
    }

    public function buildFormData(): array
    {
        $data = [];
        foreach ($this->map as $property => $formKey) {
            if ($this->$property !== null) {
                $data[$formKey] = $this->$property;
            }
        }

        return $data;
    }
}
