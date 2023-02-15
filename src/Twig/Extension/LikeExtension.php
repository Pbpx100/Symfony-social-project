<?php

namespace App\Twig\Extension;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Likes;

class LikeExtension extends AbstractExtension
{
    protected $doctrine;
    function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('likeStatus', [$this, 'likeStatusFunction']),
        ];
    }

    //Setting a status to database of user
    public function likeStatusFunction($user, $publication)
    {
        $entityManager = $this->doctrine->getManager();
        $like_repository = $entityManager->getRepository(Likes::class);
        $user_like = $like_repository->findOneBy([
            'users' => $user,
            'publication' => $publication,
        ]);
        if ($user_like != null && !empty($user_like) && is_object($user_like)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}
