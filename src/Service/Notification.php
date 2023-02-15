<?php

namespace App\Service;

use App\Entity\Notifications;

class Notification
{
    public $doctrine;

    function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    //Setting the notificatons and sending to users with a (0) in database by default
    public function setNotification($user, $typeNot, $typeId, $extra = null)
    {
        $entityManager = $this->doctrine;
        $notification = new Notifications();
        $notification->setUsers($user);
        $notification->setTypeNot($typeNot);
        $notification->setTypeId($typeId);
        $notification->setReaded(0);
        $notification->setCreated(new \DateTime("now"));
        $notification->setExtra($extra);

        $entityManager->persist($notification);
        $flush = $entityManager->flush();

        if ($flush == null) {
            $msg = true;
        } else {
            $msg = false;
        }
        return $msg;
    }

    //After to read notifications the readed changes to (1) in database
    public function readNotifications($user)
    {
        $entityManager = $this->doctrine;
        $notification_repository = $entityManager->getRepository(Notifications::class);
        $notifications = $notification_repository->findByUsers($user);
        foreach ($notifications as $notification) {
            $notification->setReaded(1);
            $entityManager->persist($notification);
        }
        $flush = $entityManager->flush();
        if ($flush == null) {
            $msg = true;
        } else {
            $msg = false;
        }
        return $msg;
    }
}
