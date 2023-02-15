<?php

namespace App\Controller;

use App\Entity\Publications;
use App\Entity\Likes;
use App\Entity\Following;
use App\Form\PublicationsType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Repository\PublicationsRepository;



class PublicationsController extends AbstractController
{
    private $session;

    function __construct()
    {
        //Adding Session to user
        $this->session = new Session();
    }

    //Landing page - List of publications and publication comments
    #[Route('/home', name: 'home')]
    public function Publications(Request $request, ManagerRegistry $doctrine, PaginatorInterface $paginator): Response
    {

        $publication = new Publications();

        //Creating publication form 
        $form = $this->createForm(PublicationsType::class, $publication);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            //Setting image or document in the publication if exists
            $file = $form['image']->getData();
            if (!empty($file) && $file != null) {
                $extension = $file->guessExtension();
                if ($extension == 'jpg' || $extension == 'png' || $extension == 'gif' || $extension == 'jpeg') {
                    $name_img = $this->getUser()->getId() . time() . '.' . $extension;
                    $file->move("img", $name_img);
                    $publication->setImage($name_img);
                }
            } else {
                $publication->setImage("empty");
            }

            $file = $form['document']->getData();
            if (!empty($file) && $file != null) {
                $extension = $file->guessExtension();
                if ($extension == 'pdf') {
                    $name_document = $this->getUser()->getId() . time() . '.' . $extension;
                    $file->move("document", $name_document);
                    $publication->setDocument($name_document);
                }
            } else {
                $publication->setDocument(null);
            }

            //Setting date of publication
            $publication->setCreated(new \DateTime("now"));
            $publication->setUsers($this->getUser());
            $publication->setStatus("public");

            //Saving in database
            $entityManager = $doctrine->getManager();
            $entityManager->persist($publication);
            $flush = $entityManager->flush();

            if ($flush == null) {
                $msg = 'Publication has been succesful.';
            } else {
                $msg = 'Publication failed, please try later.';
            }
            $this->session->getFlashBag()->add('msg', $msg);
            return $this->redirectToRoute('home');
        }


        //Getting list of publications 
        $pagination = $this->getPublicationPag($paginator, $request, $doctrine);

        //Getting publication by users
        $publication_repository = $doctrine->getRepository(Publications::class);
        $publications = $publication_repository->findByUsers($this->getUser()->getId());

        //Showing in homepage number of people the user is following       
        $following_repository = $doctrine->getRepository(Following::class);
        $following = $following_repository->findByUsers($this->getUser()->getId());


        //Showing in homepage number of people followers by user      
        $followers_repository = $doctrine->getRepository(Following::class);
        $followers = $followers_repository->findByFollowed($this->getUser()->getId());

        //Showing in homepage people liking the publication 
        $likes_repository = $doctrine->getRepository(Likes::class);
        $likes = $likes_repository->findByUsers($this->getUser()->getId());

        //todos los datos los voy a poner en un array
        $dataInformation = array(
            'following' => count($following),
            'pub' => count($publications),
            'followers' => count($followers),
            'likes' => count($likes),
        );



        //Rendering data information
        return $this->render('publications/home.html.twig', [
            'title' => 'Publications',
            'form' => $form->createView(),
            'pagination' => $pagination, // de aqui vienen los datos del pagination que utilizo en el twig
            'data' => $dataInformation,
        ]);
    }

    //Paginating publications in homepage
    public function getPublicationPag(PaginatorInterface $paginator, $request, $doctrine)
    {

        $repository = $doctrine->getRepository(Publications::class);

        //findAllPublications is a function created in the repository
        $publications = $repository->findAllPublications($this->getUser()->getId());

        $pagination = $paginator->paginate(
            $publications,
            $request->query->getInt('page', 1),
            5

        );
        return $pagination;
    }
}
