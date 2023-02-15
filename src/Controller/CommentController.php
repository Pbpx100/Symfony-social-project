<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comment;
use App\Entity\Publications;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PublicationsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CommentController extends AbstractController
{
    //Adding comments to each publication

    #[Route('/addComment', name: 'addComment')]
    public function addComment(Request $request, ManagerRegistry $doctrine): Response
    {
        //Here we get the publication Id from database and the comment field
        $publication_id = $request->get('publication_id');
        $comment = $request->get('comment');
        if ($comment != null) {

            // If the comment is not null we are gonna recuperate the user_id and the publication_id
            $user_repository = $doctrine->getRepository(User::class);
            $user = $user_repository->find($this->getUser()->getId());
            $publication_repository = $doctrine->getRepository(Publications::class);
            $publication = $publication_repository->find($publication_id);

            //Setting information comment to database  
            $commentEntity = new Comment();
            $commentEntity->setUsers($user);
            $commentEntity->setPublication($publication);
            $commentEntity->setText($comment);
            $commentEntity->setActive(true);
            $commentEntity->setDate(new \DateTime('now'));

            //Saving information to database
            $entityManager = $doctrine->getManager();
            $entityManager->persist($commentEntity);
            $flush = $entityManager->flush();

            if ($flush == null) {
                $msg = 'Comment sent succesfully.';
            } else {
                $msg = 'Comment sent failed, please try later.';
            }
        } else {
            $msg = 'Comment Empty write a comment please.';
        }
        return new Response($msg);
    }


    //Reading comments of the publications from database

    #[Route('/readComments', name: 'readComments')]
    public function readComments(PublicationsRepository $publication_repository)
    {
        //Find the publication comments
        $allpublications = $publication_repository->findAll();
        $publications = array();

        //Looping publications 
        foreach ($allpublications as $publication) {
            //Getting comments from publication loop
            foreach ($publication->getComments() as $comment) {
                $publications[] = array(
                    'comment_p_id' => $comment->getPublication()->getId(),
                    'comment_text' => $comment->getText(),
                    'comment_active' => $comment->isActive(),
                    'comment_u_name' => $comment->getUsers()->getName(),
                    'comment_u_surname' => $comment->getUsers()->getSurname(),
                    'comment_u_nick' => $comment->getUsers()->getNick(),
                    'comment_u_image' => $comment->getUsers()->getImage(),
                    'comment_date' => date_format($comment->getDate(), "d-m-Y H:i:s"),




                );
            }
        }
        return new JsonResponse($publications);
    }
}
