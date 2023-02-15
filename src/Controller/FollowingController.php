<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Following;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Notification;


class FollowingController extends AbstractController
{
    // Adding followers to users with post method
    #[Route('/follow', name: 'follow', methods: ['POST'])]
    public function follow(Request $request, ManagerRegistry $doctrine, Notification $notification): Response
    {
        $user = $this->getUser();

        //Getting key 'followed' from users.js. Using ajax
        $followed_id = $request->get('followed');
        $entityManager = $doctrine->getManager();
        $user_repository = $entityManager->getRepository(User::class);

        //Finding user followed
        $followed = $user_repository->find($followed_id);

        //Adding new user following to column user followed.
        $following = new Following;
        $following->setUsers($user);
        $following->setFollowed($followed);

        //persiting the user following
        $entityManager->persist($following);
        $flush = $entityManager->flush();

        if ($flush == null) {
            $msg = 'Now you are following this user';

            //Sending notification to user followed
            $notification->setNotification($following->getFollowed(), 'follow', $user->getId());
        } else {

            $msg = 'Request failed, please try again';
        }

        return new Response($msg);
    }


    //Removing followers to users from database
    #[Route('/unfollow', name: 'unfollow',  methods: ['POST'])]
    public function unfollow(Request $request, ManagerRegistry $doctrine): Response
    {

        $user = $this->getUser();

        //Getting users with followers from users.js. Using ajax
        $followed_id = $request->get('followed');
        $entityManager = $doctrine->getManager();

        //Finding followers in a user 
        $user_repository = $entityManager->getRepository(Following::class);
        $followed = $user_repository->findOneBy([
            'users' => $user,
            'followed' => $followed_id,
        ]);

        //Removing followers from user followed
        $entityManager->remove($followed);
        $flush = $entityManager->flush();

        if ($flush == null) {
            $msg = 'Now you are not following this user';
        } else {

            $msg = 'Request failed, please try again';
        }

        return new Response($msg);
    }

    //Page following - List of users follow by an user
    #[Route('/following/{nick}', name: 'following', defaults: ['nick' => null])]
    public function following(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine, $nick)
    {
        //Getting users by nick
        $em = $doctrine->getManager();
        $user_repository = $em->getRepository(User::class);
        if ($nick != null) {
            $user = $user_repository->findOneByNick($nick);
        } else {
            $user = $this->getUser();
        }

        //Returning to home if there is no user
        if (!is_object($user)) {
            return $this->redirectToRoute('home');
        }

        //Finding users follow by an user 
        $following_repository = $em->getRepository(Following::class);
        $following_users = $following_repository->findAllFollowings($user->getId());

        //Putting in an array the users
        $following_user = null;
        foreach ($following_users as $row) {
            if ($row['id'] != $this->getUser()->getId()) {
                $following_user[] = $row;
            }
        }

        //Paginating list of users follow by an user
        $pagination = $paginator->paginate(
            $following_user,
            $request->query->getInt('page', 1),
            5

        );


        return $this->render('user/following.html.twig', [
            'following' => $pagination,
            'user' => $user,
            'title' => 'Following'
        ]);
    }

    //Page followers - List of users following an user
    #[Route('/followers/{nick}', name: 'followers', defaults: ['nick' => null])]
    public function followers(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine, $nick)
    {
        //Getting users by nick
        $em = $doctrine->getManager();
        $user_repository = $em->getRepository(User::class);
        if ($nick != null) {
            $user = $user_repository->findOneByNick($nick);
        } else {
            $user = $this->getUser();
        }

        //Returning to home if there is no user
        if (!is_object($user)) {
            return $this->redirectToRoute('home');
        }

        //Finding followers
        $followers_repository = $em->getRepository(Following::class);
        $followers_users = $followers_repository->findAllFollowers($user->getId());

        foreach ($followers_users as $row) {
            if ($row['id'] != $this->getUser()->getId()) {
                $followers_user[] = $row;
            }
        }

        //Paginating followers
        $pagination = $paginator->paginate(
            $followers_user,
            $request->query->getInt('page', 1),
            5

        );

        return $this->render('user/followers.html.twig', [
            'followers' => $pagination,
            'title' => 'Followers'
        ]);
    }
}
