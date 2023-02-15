<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Notifications;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\Notification;



class NotificationController extends AbstractController
{

    //Sending likes and follows notification to users
    #[Route('/notification', name: 'notification')]
    public function index(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine, Notification $notification): Response
    {
        $user = $this->getUser();
        $entityManager = $doctrine->getManager();

        //Getting list of notifications by user
        $notification_repository = $entityManager->getRepository(Notifications::class);
        $notifications = $notification_repository->findByUsers($user);
        $notification->readNotifications($user->getId());

        //Pagination of notifications
        $pagination = $paginator->paginate(
            $notifications,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('notification/index.html.twig', [
            'notifications' => $pagination,
            'title' => 'Notification',
        ]);
    }


    //Counting notifications
    #[Route('/countNotifications', name: 'countNotifications')]
    public function CountNotifications(ManagerRegistry $doctrine)
    {

        $user = $this->getUser();

        $entityManager = $doctrine->getManager();
        $notification_repository = $entityManager->getRepository(Notifications::class);

        //findCountNotifications is a function created in NotificationsRepository 
        $notifications = $notification_repository->findCountNotifications($user->getId());

        return new Response($notifications[0]['p']);
    }

    //Reading notification and removing from notification list
    #[Route('/notificationDelete', name: 'notificationDelete')]
    public function notificationDelete(Request $request, ManagerRegistry $doctrine)
    {
        $id = $request->get('id');
        $entityManager = $doctrine->getManager();

        //Getting list of notification by user
        $notification_repository = $entityManager->getRepository(Notifications::class);
        $notification = $notification_repository->find($id);

        //Removing from notification list
        $entityManager->remove($notification);
        $flush = $entityManager->flush();
        if ($flush == null) {
            $msg = 'Delete notification success';
        } else {
            $msg = 'Delete notification failed, please try later';
        }
        return new Response($msg);
    }
}
