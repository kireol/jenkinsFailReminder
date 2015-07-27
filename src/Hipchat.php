<?php
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;

class Hipchat
{
    private $color = null;
    const MILLISECONDS = 1000;

    public function __construct($channel)
    {
        $this->channel = $channel;
    }

    public function getMessageObject($messageText)
    {
        $message = new \GorkaLaucirica\HipchatAPIv2Client\Model\Message();
        $message->setMessage($messageText);
        if ($this->color == null) {
            $message->setColor($this->channel['hipchattextcolor']);
        }else{
            $message->setColor($this->color);
            $this->color = null;
        }
        $message->setMessageFormat("html");
        return $message;
    }

    function postOutputWithColor($outputString, $color)
    {
        $this->color = $color;
        if (strcmp($this->channel['output'], "console") == 0) {
            echo $outputString;
        } else {
            $outputString = $this->convertNewLineToBr($outputString);
            $this->postInHipChat($outputString);
        }
    }

    function postOutput($outputString)
    {
        if (strcmp($this->channel['output'], "console") == 0) {
            echo $outputString;
        } else {
            $outputString = $this->convertNewLineToBr($outputString);
            if (!$this->wasLastMessageTheSameAsThisMessage($outputString))
            {
              $this->postInHipChat($outputString);
            }
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
        return str_replace("\n", "<br />", $in);
    }

    function wasLastMessageTheSameAsThisMessage($message)
    {
      $auth = new OAuth2($this->channel['hipchatapitoken']);
      $client = new Client($auth);
      $hipchatRoomAPI = new \GorkaLaucirica\HipchatAPIv2Client\API\RoomAPI($client);
      $hipchatRoomId = $this->getHipchatRoomId($hipchatRoomAPI);
      $lastMessages = $hipchatRoomAPI->getRecentHistory($hipchatRoomId);
      $lastMessage = end($lastMessages);
      return $lastMessage->getMessage() === $message;
    }
}

function titleCompare($a, $b)
{
    return strcmp($a->title, $b->title);
}

