<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Likes;
use App\Entity\Publications;
use App\Entity\Following;
use App\Form\RegisterType;
use App\Form\EditUserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Knp\Component\Pager\PaginatorInterface;

class UserController extends AbstractController
{
    private $session;
    function __construct()
    {
        //Initiliazation of session
        $this->session = new Session();
    }

    //Sending to login the visitors of the website
    #[Route('/', name: '')]
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //If there is not an user redirect to home
        if (is_object($this->getUser())) {
            return $this->redirectToRoute('home');
        }


        //to see the error of authentification
        $error = $authenticationUtils->getLastAuthenticationError();
        // this is the function wich verifies the user 
        $last_username = $authenticationUtils->getLastUsername();

        //then we pass the last_username as key
        return $this->render('user/login.html.twig', [
            'title' => 'Login',
            'error' => $error,
            'last_username' => $last_username,
        ],);
    }

    //Logout
    #[Route('/logout', name: 'logout')]
    public function logout()
    {
        //to do a logout just is necessary this and determinated in security.yaml
    }

    //User account
    #[Route('/account', name: 'account')]
    public function editUser(Request $request, ManagerRegistry $doctrine)
    {
        //Getting current user and image
        $user_image = $this->getUser()->getImage();

        //Creating form to modify data of user
        $form = $this->createForm(EditUserType::class, $this->getUser());
        $form->handleRequest($request);

        //Getting user by email
        $repository = $doctrine->getRepository(User::class);
        $user_bbdd = $repository->findOneBy(['email' => $this->getUser()->getEmail()]);

        //Form to modify data of user
        if ($form->isSubmitted() && $form->isValid()) {

            if (
                $user_bbdd != null && $user_bbdd->getEmail() == $this->getUser()->getEmail() &&
                $user_bbdd->getNick() == $this->getUser()->getNick() || $user_bbdd == null
            ) {
                $file = $form['image']->getData();
                if (!empty($file) && $file != null) {
                    $extension = $file->guessExtension();
                    if ($extension == 'jpg' || $extension == 'png' || $extension == 'gif' || $extension == 'jpeg') {
                        $name_img = $this->getUser()->getId() . time() . '.' . $extension;
                        $file->move("img", $name_img);
                        $this->getUser()->setImage($name_img);
                    }
                } else {
                    $this->getUser()->setImage($user_image);
                }

                $entityManager = $doctrine->getManager();
                $entityManager->persist($this->getUser());
                $flush = $entityManager->flush();
            }

            //Flushing mofication data
            if ($flush == null) {
                $msg = 'Data modification has been succesful.';
            } else {
                $msg = 'Data modification failed, please try later.';
            }

            //Saving message of modification in FlashBag()
            $this->session->getFlashBag()->add('msg', $msg);
            return $this->redirectToRoute('account');
        }

        return $this->render('user/editUser.html.twig', [
            'title'    => 'Account',
            'form' => $form->createView(),
        ]);
    }


    //Register user
    #[Route('/register', name: 'register')]
    public function register(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        //If user is register redirect to home in tis route
        if (is_object($this->getUser())) {
            return $this->redirectToRoute('home');
        }


        $user = new User();

        //Creating form for new user
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        //Handle request form 
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setActive(false);

            //asigning the Role user
            $user->setRoles(['ROLE_USER']);
            //here we encode ou password 
            $encoded = $passwordHasher->hashPassword($user, $user->getPassword());
            //we set here the password hashed we pass the $econde to password
            $user->setPassword($encoded);

            $entityManager = $doctrine->getManager();
            //to the function persit we pass the object the last one with user
            $entityManager->persist($user);
            $flush = $entityManager->flush();

            if ($flush == null) {
                //here we are gonna send a message to the twig.html when get session registerd
                $msg = 'Resgistration has been successful.';
                //here we declare the key msg wich we use in the html message
                $this->session->getFlashBag()->add('msg', $msg);
                // After this we redirect to people to the 'login'
                return $this->redirectToRoute('login');
            } else {
                $msg = 'Resgistration failed, please try later.';
            }
        }


        return $this->render('user/register.html.twig', [
            'title' => 'Register',
            'form' => $form->createView()
        ]);
    }

    //List of Users 
    #[Route('/people', name: 'people')]
    public function users(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $repository = $em->getRepository(User::class);
        $query = $repository->createQueryBuilder('p')->orderBy('p.name', 'ASC')->getQuery();
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5

        );
        return $this->render('user/users.html.twig', [
            'title' => 'People',
            'users' => $users,
        ]);
    }

    //Search by users
    #[Route('/search', name: 'search')]
    public function usersSearch(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine)
    {
        $search = $request->query->get('search', null);
        if ($search == null) {
            return $this->redirectToRoute('home');
        }

        $em = $doctrine->getManager();
        $repository = $em->getRepository(User::class);
        $query = $repository->createQueryBuilder('p')->orderBy('p.id', 'DESC')
            ->where('p.name LIKE :searchTerm OR p.surname LIKE :searchTerm OR p.nick LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $search . '%')->getQuery();
        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5

        );
        return $this->render('user/users.html.twig', [
            'title' => 'People',
            'users' => $users,
        ]);
    }

    //Verifies if the user nick is already registered
    #[Route('/nickCheck', name: 'nickCheck', methods: ['POST'])]
    public function nickCheck(Request $request, ManagerRegistry $doctrine)
    {
        $nick = $request->get('nick');

        // $product = $doctrine->getRepository(User::class)->find($nick);
        $repository = $doctrine->getRepository(User::class);
        $user_nick = $repository->findOneBy(['nick' => $nick]);
        $result = "used";

        if ($user_nick != null) {
            $result = "used";
        } else {
            $result = "unsed";
        }
        return new Response($result);
    }

    //Page User profile and User publications
    #[Route('/profile/{nick}', name: 'profile', defaults: ['nick' => null])]
    public function profile(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine, $nick)
    {

        $em = $doctrine->getManager();
        $user_repository = $em->getRepository(User::class);
        if ($nick != null) {
            $user = $user_repository->findOneByNick($nick);
        } else {
            $user = $this->getUser();
        }
        if (!is_object($user)) {
            return $this->redirectToRoute('home');
        }

        //List of publications 
        $publication_repository = $doctrine->getRepository(Publications::class);
        $publications = $publication_repository->findByUsers($user->getId(), array('id' => 'DESC'));

        //List of followings
        $following_repository = $doctrine->getRepository(Following::class);
        $following = $following_repository->findByUsers($user->getId());

        //list of followers 
        $followers_repository = $doctrine->getRepository(Following::class);
        $followers = $followers_repository->findByFollowed($user->getId());

        //list of likes
        $likes_repository = $doctrine->getRepository(Likes::class);
        $likes = $likes_repository->findByUsers($user->getId());

        //Paginating information
        $pagination = $paginator->paginate(
            $publications,
            $request->query->getInt('page', 1),
            5

        );

        //saving information in array
        $dataInformation = array(
            "following" => count($following),
            "followers" => count($followers),
            "pub" => count($publications),
            "likes" => count($likes),
        );


        return $this->render('user/profile.html.twig', [
            'pagination' => $pagination,
            'info' => $dataInformation,
            'publications' => $publications,
            'user' => $user,
        ]);
    }
}
