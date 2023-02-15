<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Following;
use App\Entity\PrivateMessages;
use App\Form\PrivateMessageType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

class PrivateMessageController extends AbstractController
{
    private $session;
    function __construct()
    {
        //Initiliazing user session
        $this->session = new Session();
    }

    //Sending private messages to users following
    #[Route('/privateMessage', name: 'private_message')]
    public function addMessage(Request $request, ManagerRegistry $doctrine): Response
    {
        $private_msg = new PrivateMessages();
        $user = $this->getUser()->getId();

        //Finding users and users following
        $user_repository = $doctrine->getRepository(User::class);
        $following_repository = $doctrine->getRepository(Following::class);

        $following = $following_repository->findByUsers($user);

        //Users followed 
        foreach ($following as $follow) {
            $array_following[] = $follow->getFollowed();
        }

        //Creating Query Folloing User
        $users = $user_repository->createQueryBuilder('u')
            ->andWhere('u.id != :user AND u.id IN (:following)')
            ->setParameter('user', $user)
            ->setParameter('following', $array_following)
            ->orderBy('u.id', 'DESC');

        //Creating form of private message
        $form = $this->createForm(PrivateMessageType::class, $private_msg, array(
            'empty_data' => $users
        ));

        //Getting the message form and image or document the user sends
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['image']->getData();
            if (!empty($file) && $file != null) {
                $extension = $file->guessExtension();
                if ($extension == 'jpg' || $extension == 'png' || $extension == 'gif' || $extension == 'jpeg') {
                    $name_img = $this->getUser()->getId() . time() . '.' . $extension;
                    $file->move("img", $name_img);
                    $private_msg->setImage($name_img);
                }
            } else {
                $private_msg->setImage("empty");
            }

            //Adding a date time to image or document
            $file = $form['file']->getData();
            if (!empty($file) && $file != null) {
                $extension = $file->guessExtension();
                if ($extension == 'pdf') {
                    $name_document = $this->getUser()->getId() . time() . '.' . $extension;
                    $file->move("file", $name_document);
                    $private_msg->setFile($name_document);
                }
            } else {
                $private_msg->setFile(null);
            }
            $private_msg->setCreated(new \DateTime("now"));
            $private_msg->setSender($this->getUser());
            $private_msg->setReaded(0);

            //Saving the private message with documents
            $entityManager = $doctrine->getManager();
            $entityManager->persist($private_msg);
            $flush = $entityManager->flush();

            if ($flush == null) {
                $msg = 'Message has been succesful.';
            } else {
                $msg = 'Message failed, please try later.';
            }
            $this->session->getFlashBag()->add('msg', $msg);
            return $this->redirectToRoute('private_message');
        }

        return $this->render('private_message/index.html.twig', [
            'controller_name' => 'PrivateMessageController',
            'title' => 'Private Message',
            'form' => $form->createView(),
        ]);
    }

    //Page messages sent - List of messages sent by user to another users
    #[Route('/sentMessages', name: 'sentMessages')]
    function sentMessage(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
    {
        $user = $this->getUser()->getId();
        $privateMessage_repo = $doctrine->getRepository(PrivateMessages::class);

        //Finding the private message by user
        $sent_msg =  $privateMessage_repo->findBySender(
            $user,
            array('id' => 'DESC')
        );

        //Paginating messages sent
        $pagination = $paginator->paginate(
            $sent_msg,
            $request->query->getInt('page', 1),
            5

        );

        return $this->render('private_message/sentMessages.html.twig', [
            'title' => 'Sent Messages',
            'pagination' => $pagination,
        ]);
    }

    //Page list of messages received - Messages received by user 
    #[Route('/receiverMessages', name: 'receiverMessages')]
    function receiverMessages(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
    {
        $user = $this->getUser()->getId();
        $privateMessage_repo = $doctrine->getRepository(PrivateMessages::class);

        //Finding messages received
        $receiver_msgs =  $privateMessage_repo->findByReceiver(
            $user,
            array('id' => 'DESC')
        );

        foreach ($receiver_msgs  as  $receiver_msg) {
            $receiver_msg->setReaded(1);
            //Here I add (1) to messages readed - messages readed by default is (0) in Database 
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($receiver_msg);
        $flush = $entityManager->flush();

        //Paginating messages
        $pagination = $paginator->paginate(
            $receiver_msgs,
            $request->query->getInt('page', 1),
            5

        );

        return $this->render('private_message/receiverMessages.html.twig', [
            'title' => 'Received Messages',
            'pagination' => $pagination,
        ]);
    }

    //Messages received and not readed by default (0) in database
    #[Route('/countReceiverMessages', name: 'countReceiverMessages')]
    public function countReceiverMessages(ManagerRegistry $doctrine)
    {
        $user = $this->getUser()->getId();
        $privateMessage_repo = $doctrine->getRepository(PrivateMessages::class);

        //Getting messages and adding 0
        $receiver_msgs =  $privateMessage_repo->findBy(
            array(
                'receiver' => $user,
                'readed' => 0
            )
        );
        return new Response(count($receiver_msgs));
    }
}
