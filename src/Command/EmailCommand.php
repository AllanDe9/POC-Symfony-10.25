<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\VideoGameRepository;
use App\Service\MailService;
use DateTimeImmutable;
use DateInterval;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:send-newsletter',
    description: 'Envoie la newsletter aux utilisateurs inscrits avec les jeux à venir.',
)]
class EmailCommand extends Command
{
    private UserRepository $userRepository;
    private VideoGameRepository $videoGameRepository;
    private MailService $mailService;

    public function __construct(UserRepository $userRepository, VideoGameRepository $videoGameRepository, MailService $mailService)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->videoGameRepository = $videoGameRepository;
        $this->mailService = $mailService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new DateTimeImmutable();
        $nextWeek = $today->add(new DateInterval('P7D'));

        $games = $this->videoGameRepository->createQueryBuilder('v')
            ->where('v.releaseDate BETWEEN :today AND :nextWeek')
            ->setParameters([
                'today' => $today,  
                'nextWeek' => $nextWeek,
            ])
            ->orderBy('v.releaseDate', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($games)) {
            $output->writeln('Aucun jeu à venir cette semaine.');
            return Command::SUCCESS;
        }

        $users = $this->userRepository->findBy(['newsletter' => true]);

        if (empty($users)) {
            $output->writeln('Aucun utilisateur inscrit à la newsletter.');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('Envoi de la newsletter à %d utilisateurs...', count($users)));

        foreach ($users as $user) {
            $this->mailService->sendEmail(
                $user->getEmail(),
                'Les sorties jeux vidéo de la semaine !',
                'emails/newsletter.html.twig',
                ['user' => $user, 'games' => $games]
            );
        }

        $output->writeln('Newsletter envoyée avec succès !');
        return Command::SUCCESS;
    }
}
