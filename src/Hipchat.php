<?php

class Hipchat
{
    const MILLISECONDS = 1000;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    public function getMessageObject($messageText)
    {
        $message = new \GorkaLaucirica\HipchatAPIv2Client\Model\Message();
        $message->setMessage($messageText);
        $message->setColor($this->channel['hipchattextcolor']);
        $message->setMessageFormat("html");
        return $message;
    }

    function postOutput($outputString)
    {
        if (strcmp($this->channel['output'], "console") == 0) {
            echo $outputString;
        } else {
            $outputString = $this->convertNewLineToBr($outputString);
            $this->postInHipChat($outputString);
        }
    }

    function postInHipChat($message)
    {
        $auth = new OAuth2($this->channel['hipchatapitoken']);
        $client = new Client($auth);
        $hipchatRoomAPI = new \GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI($client);
        $hipchatRoomId = $this->getHipchatRoomId($hipchatRoomAPI);
        $hipchatRoomAPI->sendRoomNotification($hipchatRoomId, $this->getMessageObject($message));
    }

    function getHipchatRoomId($hipchatRoomAPI)
    {
        $rooms = $hipchatRoomAPI->getRooms();
        foreach ($rooms as $room) {
            if (strcmp($room->getName(), $this->channel['hipchatroom']) == 0) {
                return $room->getId();
            }
        }
        return -1;
    }

    function convertNewLineToBr($in)
    {
        return str_replace("\n", "<br/>", $in);
    }

}

function titleCompare($a, $b)
{
    return strcmp($a->title, $b->title);
}

