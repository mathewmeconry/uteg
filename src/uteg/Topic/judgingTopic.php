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
        $competitionPlace = $request->getAttributes()->get('competitionPlace');
        if ($this->isAnyStarted($comp, $competitionPlace)) {
            $this->initializeIfNot($comp, $competitionPlace);
            
            switch ($event['method']) {
                case 'changeState':
                    $this->changeState($comp, $competitionPlace, $event['device'], $event['state']);
                    $connection->event($topic->getId(), ['method' => 'changeState', 'msg' => 'ok', 'competitionPlace' => $competitionPlace]);
                    var_dump($this->states);
                    break;
                case 'amIfinished':
                    $connection->event($topic->getId(), ['method' => 'amIfinished', 'msg' => $this->getState($comp, $competitionPlace, $event['device']), 'competitionPlace' => $competitionPlace]);
                    break;
                case 'reloadStarters':
                    $topic->broadcast([
                        'method' => 'reloadStarters',
                        'competitionPlace' => $competitionPlace
                    ]);
                    break;
                case 'startDepartment':
                    $topic->broadcast([
                        'method' => 'startDepartment',
                        'competitionPlace' => $competitionPlace
                    ]);
                    break;
            }

            if ($this->allFinished($comp, $competitionPlace)) {
                $this->turn($comp, $competitionPlace, $topic);
            }


        } else {
            $connection->event($topic->getId(), ['msg' => 'noneStarted', 'competitionPlace' => $competitionPlace]);
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

    private function turn($comp, $competitionPlace, $topic)
    {
        $this->resetStates($comp, $competitionPlace);
        $this->initializeStates($comp, $competitionPlace);
        $ended = true;

        $departments = $this->getDepartments($comp, $competitionPlace);

        foreach ($departments as $department) {
            $dep = $this->em->getRepository('uteg:Department')->findOneBy(array("id" => $department['id']));
            $dep->increaseRound();

            if ($this->isEnded($dep->getRound($comp, $competitionPlace), $dep->getGender($comp, $competitionPlace))) {
                $dep->setEnded(1);
            } else {
                $ended = false;
            }

            $this->em->persist($dep);
        }

        $this->em->flush();

        if ($ended) {
            $this->ended($comp, $competitionPlace, $topic);
        } else {
            var_dump($this->states);
            $topic->broadcast([
                'method' => 'turn',
                'round' => $this->getRound($comp, $competitionPlace),
                'competitionPlace' => $competitionPlace
            ]);
        }
    }

    private function allFinished($comp, $competitionPlace)
    {
        $finished = true;
        foreach ($this->states[$comp][$competitionPlace] as $device) {
            if ($device === 0) {
                $finished = false;
            }
        }

        return $finished;
    }

    private function changeState($comp, $competitionPlace, $device, $state)
    {
        $this->states[$comp][$competitionPlace][$device] = $state;
    }

    private function getState($comp, $competitionPlace, $device)
    {
        if (isset($this->states[$comp][$competitionPlace][$device])) {
            return $this->states[$comp][$competitionPlace][$device];
        } else {
            return 0;
        }
    }

    private function initializeIfNot($comp, $competitionPlace)
    {
        if (!array_key_exists($comp, $this->states)) {
            $this->initializeStates($comp, $competitionPlace);
        } else {
            if (!array_key_exists($competitionPlace, $this->states[$comp])) {
                $this->initializeStates($comp, $competitionPlace);
            }
        }
    }

    private function initializeStates($comp, $competitionPlace)
    {
        if ($this->isAnyStarted($comp, $competitionPlace)) {
            if (!array_key_exists($comp, $this->states)) {
                $this->states[$comp] = array();
            }

            if (!array_key_exists($competitionPlace, $this->states[$comp])) {
                $this->states[$comp][$competitionPlace] = array(1 => 0, 2 => 0, 3 => 0, 4 => 0);

                if ($this->getGender($comp, $competitionPlace) === "male") {
                    $this->states[$comp][$competitionPlace][5] = 0;
                }
            }
        }
    }

    private function resetStates($comp, $competitionPlace) {
        unset($this->states[$comp][$competitionPlace]);
    }

    private function isAnyStarted($comp, $competitionPlace)
    {
        $departments = $this->getDepartments($comp, $competitionPlace);
        if (count($departments) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function getGender($comp, $competitionPlace)
    {
        $gender = "female";

        $departments = $this->getDepartments($comp, $competitionPlace);

        foreach ($departments as $department) {
            if ($department['gender'] === "male") {
                $gender = "male";
            }
        }

        return $gender;
    }

    private function getRound($comp, $competitionPlace)
    {
        $round = 0;

        $departments = $this->getDepartments($comp, $competitionPlace);

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

    private function ended($comp, $competitionPlace, $topic)
    {
        $topic->broadcast([
            'method' => 'ended',
            'competitionPlace' => $competitionPlace
        ]);

        unset($this->states[$comp][$competitionPlace]);
    }

    private function getDepartments($comp, $competitionPlace)
    {
        return $this->em
            ->getRepository('uteg:Department')
            ->createQueryBuilder('d')
            ->select('d.id as id, d.round as round, d.gender as gender')
            ->where('d.started = 1')
            ->andWhere('d.ended = 0')
            ->andWhere('d.competition = :competition')
            ->andWhere('d.competitionPlace = :competitionPlace')
            ->setParameters(array('competition' => $comp, 'competitionPlace' => $competitionPlace))
            ->getQuery()->getResult();
    }
}