<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\CustomRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\User;

class CustomExtension extends AbstractExtension
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
            new TwigFilter('userById', [$this, 'userByIdFilter']),
            new TwigFilter('timeAgo', [$this, 'timeAgoFilter']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [CustomRuntime::class, 'doSomething']),
            new TwigFunction('function_name', [CustomRuntime::class, 'doSomething']),
        ];
    }


    public function userByIdFilter($user_id)
    {
        $entityManager = $this->doctrine->getManager();
        $user_repository = $entityManager->getRepository(User::class);
        $user = $user_repository->find($user_id);
        return $user;
    }

    //Setting a time to notification by seconds, minutes, hours, days, weeks, months and years
    public function timeAgoFilter($timestamp)
    {
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);
        $weeks = round($seconds / 604800);
        $months = round($seconds / 2629440);
        $years = round($seconds / 31553280);

        if ($seconds <= 60) {
            return "just now";
        } else if ($minutes <= 60) {
            if ($minutes == 1) {
                return "one minute ago";
            } else {
                return "$minutes minutes ago";
            }
        } else if ($hours <= 24) {
            if ($hours == 1) {
                return "an hour ago";
            } else {
                return "$hours hours ago";
            }
        } else if ($days <= 7) {
            if ($days == 1) {
                return "yesterday";
            } else {
                return " $days days ago";
            }
        } else if ($weeks <= 4.3) {
            if ($weeks == 1) {
                return "a week ago";
            } else {
                return "$weeks weeks ago";
            }
        } else if ($months <= 12) {
            if ($months == 1) {
                return "a month ago";
            } else {
                return "$months months ago";
            }
        } else {
            if ($years == 1) {
                return "a year ago";
            } else {
                return "$years years ago";
            }
        }
    }
}
