<?php

namespace Every8d\Message;

class SMS implements MessageInterface
{
    /**
     * @var string Message title (Optional)
     *             Empty titles are accepted
     *             The title will not be sent with the SMS; it is just a note
     */
    public $SB;

    /**
     * @var string Message content
     */
    public $MSG;

    /**
     * @var string Receiver's mobile number
     */
    public $DEST;

    /**
     * @var string Reservation time (Optional)
     *             Format: yyyyMMddHHmnss, e.g. 20090131153000
     */
    public $ST;

    /**
     * @var int SMS validity period of unit: minutes (Optional)
     *          If not specified, then the platform default validity period is 1440 minutes
     */
    public $RETRYTIME;

    /**
     * @var string Message record no (Optional)
     */
    public $MR;

    public function __construct(array $data = [])
    {
        foreach ($data as $name => $value) {
            $this->$name = $value;
        }
    }

    public function buildFormData(): array
    {
        $data = [];
        $vars = get_class_vars(get_class($this));

        foreach ($vars as $name => $value) {
            if ($value !== null) {
                $data[$name] = $value;
            }
        }

        return $data;
    }
}
