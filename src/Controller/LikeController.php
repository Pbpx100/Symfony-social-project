<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Publications;
use App\Entity\Likes;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Notification;
use Symfony\Component\HttpFoundation\JsonResponse;

class LikeController extends AbstractController
{
    // Setting likes in publications 
    #[Route('/like', name: 'like', methods: ['POST'])]
    public function like(Request $request, ManagerRegistry $doctrine, Notification $notification): Response
    {
        $user = $this->getUser();

        //Getting publications id
        $publication_id = $request->get('publication');
        $entityManager = $doctrine->getManager();
        $publication_repository = $entityManager->getRepository(Publications::class);

        //Finding publication by id
        $publication = $publication_repository->find($publication_id);

        //Liking a publication
        $like = new Likes();
        $like->setUsers($user);
        $like->setPublication($publication);

        //Persisting and saving in database
        $entityManager->persist($like);
        $flush = $entityManager->flush();


        if ($flush == null) {
            $msg = 'You like this publication';

            //Sending to user a like notification
            $notification->setNotification($publication->getUsers(), 'like', $user->getId(), $publication->getId());
        } else {

            $msg = 'Like action failed, please try later';
        }

        return new Response($msg);
    }

    // Removing likes from a publication 
    #[Route('/unlike', name: 'unlike',  methods: ['POST'])]
    public function unlike(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        //Getting publications and likes by id
        $publication_id = $request->get('publication');
        $entityManager = $doctrine->getManager();
        $like_repository = $entityManager->getRepository(Likes::class);
        $like = $like_repository->findOneBy([
            'users' => $user,
            'publication' => $publication_id,
        ]);

        //Removing likes from database
        $entityManager->remove($like);
        $flush = $entityManager->flush();

        if ($flush == null) {
            $msg = 'Now  you do not like this user';
        } else {

            $msg = 'unlike  failed, please try again';
        }



        return new Response($msg);
    }


    //Likes page - List of likes by user
    #[Route('/likes/{nick}', name: 'likes', defaults: ['nick' => null])]
    public function likes(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine, $nick)
    {
        //Finding users by id
        $em = $doctrine->getManager();
        $user_repository = $em->getRepository(User::class);
        if ($nick != null) {
            $user = $user_repository->findOneByNick($nick);
        } else {
            $user = $this->getUser();
        }

        //If not user redirect to home
        if (!is_object($user)) {
            return $this->redirectToRoute('home');
        }

        //Getting likes list by user
        $entityManager = $doctrine->getManager();
        $like_repository = $entityManager->getRepository(Likes::class);
        $likes_users = $like_repository->findAllLikes($user->getId());

        //Paginating likes list
        $pagination = $paginator->paginate(
            $likes_users,
            $request->query->getInt('page', 1),
            5

        );

        return $this->render('user/likes.html.twig', [
            'likes' => $pagination,
            'title' => 'Likes'
        ]);
    }

    //Counting likes an user has in publications
    #[Route('/countLikes', name: 'countLikes')]
    public function countLikes(ManagerRegistry $doctrine)
    {
        //Getting user Publications
        $entityManager = $doctrine->getManager();
        $publications_repository = $entityManager->getRepository(Publications::class);
        $publications = $publications_repository->findAll();

        //Getting all likes
        $likes_repository = $entityManager->getRepository(Likes::class);
        $jsonData = array();
        $idx = 0;
        foreach ($publications as $publication) {

            //FindCountLikes is a function created in LikesRepository

            $likes = $likes_repository->findCountLikes($publication->getId());
            $temp = array(
                'id' => $publication->getId(),
                'likes' => $likes
            );
            $jsonData[$idx++] = $temp;
        }
        return new JsonResponse($jsonData);
    }
}
