<?php

namespace uteg\Topic;

use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;

class judgingTopic implements TopicInterface
{
    protected $states = array();
    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * This will receive any Subscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return void
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {

    }

    /**
     * This will receive any UnSubscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @return voids
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {

    }

    /**
     * This will receive any Publish requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param $Topic topic
     * @param WampRequest $request
     * @param $event
     * @param array $exclude
     * @param array $eligibles
     * @return mixed|void
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        $comp = $request->getAttributes()->get('compid');
        if ($this->isAnyStarted($comp)) {
            $this->initializeIfNot($comp);

            switch ($event['method']) {
                case 'changeState':
                    $this->changeState($comp, $event['device'], $event['state']);
                    $connection->event($topic->getId(), ['method' => 'changeState', 'msg' => 'ok']);
                    break;
                case 'amIfinished':
                    $connection->event($topic->getId(), ['method' => 'amIfinished', 'msg' => $this->getState($comp, $event['device'])]);
                    break;
                case 'reloadStarters':
                    $topic->broadcast([
                        'method' => 'reloadStarters'
                    ]);
                    break;
                case 'startDepartment':
                    $topic->broadcast([
                        'method' => 'startDepartment'
                    ]);
                    break;
            }

            if ($this->allFinished($comp)) {
                $this->turn($comp, $topic);
            }


        } else {
            $connection->event($topic->getId(), ['msg' => 'noneStarted']);
        }
    }

    /**
     * Like RPC is will use to prefix the channel
     * @return string
     */
    public function getName()
    {
        return 'uteg.topic.judging';
    }

    private function turn($comp, $topic)
    {
        $this->initializeStates($comp);
        $ended = true;

        $departments = $this->getDepartments($comp);

        foreach ($departments as $department) {
            $dep = $this->em->getRepository('uteg:Department')->findOneBy(array("id" => $department['id']));
            $dep->increaseRound();

            if ($this->isEnded($dep->getRound($comp), $dep->getGender($comp))) {
                $dep->setEnded(1);
            } else {
                $ended = false;
            }

            $this->em->persist($dep);
        }

        $this->em->flush();

        if ($ended) {
            $this->ended($comp, $topic);
        } else {
            var_dump($this->states);
            $topic->broadcast([
                'method' => 'turn',
                'round' => $this->getRound($comp)
            ]);
        }
    }

    private function allFinished($comp)
    {
        $finished = true;

        foreach ($this->states[$comp] as $device) {
            if ($device === 0) {
                $finished = false;
            }
        }

        return $finished;
    }

    private function changeState($comp, $device, $state)
    {
        $this->states[$comp][$device] = $state;
    }

    private function getState($comp, $device)
    {
        if(isset($this->states[$comp][$device])) {
            return $this->states[$comp][$device];
        } else {
            return 0;
        }
    }

    private function initializeIfNot($comp)
    {
        if (!isset($this->states[$comp])) {
            $this->initializeStates($comp);
        }
    }

    private function initializeStates($comp)
    {
        if($this->getDepartments($comp)) {
            $this->states[$comp] = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);
            if ($this->getGender($comp) === "male") {
                $this->states[$comp][5] = 0;
            }
        }
    }

    private function isAnyStarted($comp)
    {
        $departments = $this->getDepartments($comp);

        if (count($departments) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function getGender($comp)
    {
        $gender = "female";

        $departments = $this->getDepartments($comp);

        foreach ($departments as $department) {
            if ($department['gender'] === "male") {
                $gender = "male";
            }
        }

        return $gender;
    }

    private function getRound($comp)
    {
        $round = 0;

        $departments = $this->getDepartments($comp);

        foreach ($departments as $department) {
            $round = $department['round'];
        }

        return $round + 1;
    }

    private function isEnded($round, $gender)
    {
        $finished = false;

        if ($round === 4 && $gender === "female") {
            $finished = true;
        } elseif ($round === 5 && $gender === "male") {
            $finished = true;
        }

        return $finished;
    }

    private function ended($comp, $topic)
    {
        $topic->broadcast([
            'method' => 'ended'
        ]);

        unset($this->states[$comp]);
    }

    private function getDepartments($comp) {
        return $this->em
            ->getRepository('uteg:Department')
            ->createQueryBuilder('d')
            ->select('d.id as id, d.round as round, d.gender as gender')
            ->where('d.started = 1')
            ->andWhere('d.ended = 0')
            ->andWhere('d.competition = :competition')
            ->setParameters(array('competition' => $comp))
            ->getQuery()->getResult();
    }
}