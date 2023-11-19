<?php

namespace App\Helper;

use App\Entity\ActivityLogs;
use App\Repository\ActivityLogsRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ActivityLogger
{
    public function __construct(private ActivityLogsRepository $activityLogsRepository,
                                private TokenStorageInterface $tokenStorage)
    {}

    public function create($object, $message = null){
        try {
            $this->save($object, $message, "create");
        }catch(\Exception $e ){
            return;
        }
    }

    public function update($object, $message = null){
        try {
            $this->save($object, $message, "update");
        }catch(\Exception $e ){
            return;
        }
    }

    public function delete($object, $message = null){
        try {
            $this->save($object, $message, "delete");
        }catch(\Exception $e ){
            return;
        }
    }

    /**
     * @param $object
     * @param mixed $message
     * @return void
     */
    public function save($object, $message, $type): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        $activityLogs = new ActivityLogs();
        $activityLogs->setType($type);
        $activityLogs->setEntity($object->getId());
        $activityLogs->setSource($object::class);
        $activityLogs->setUser($user);
        $activityLogs->setMessage($message);
        $activityLogs->setCreatedAt(new \DateTime('now'));
        $this->activityLogsRepository->save($activityLogs, true);
    }

}
