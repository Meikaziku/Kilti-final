<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Commande Symfony pour créer un compte modérateur (ou administrateur).
 *
 * Exemples d'utilisation :
 *   php bin/console app:create-moderator moderator@kilti.com motdepasse
 *   php bin/console app:create-moderator admin@kilti.com motdepasse --admin
 */
#[AsCommand(
    name: 'app:create-moderator',
    description: 'Crée un compte modérateur (ou administrateur avec --admin).',
)]
class CreateModeratorCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail du compte')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe en clair (sera haché)')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Crée un administrateur (ROLE_ADMIN) au lieu d\'un modérateur')
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Nom d\'utilisateur (par défaut : partie locale de l\'e-mail)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $isAdmin = $input->getOption('admin');
        $username = $input->getOption('username') ?: explode('@', $email)[0];

        // Validation basique
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error(sprintf('"%s" n\'est pas une adresse e-mail valide.', $email));
            return Command::FAILURE;
        }

        if (strlen($password) < 6) {
            $io->error('Le mot de passe doit faire au moins 6 caractères.');
            return Command::FAILURE;
        }

        // Vérifier que le compte n'existe pas déjà
        $existing = $this->em->getRepository(Users::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->error(sprintf('Un compte existe déjà avec l\'adresse "%s".', $email));
            return Command::FAILURE;
        }

        $user = new Users();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setIsVerified(true); // pas besoin de vérification email pour un compte créé en CLI
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setRoles([$isAdmin ? 'ROLE_ADMIN' : 'ROLE_MODERATOR']);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf(
            'Compte %s créé : %s (username: %s, rôle: %s).',
            $isAdmin ? 'administrateur' : 'modérateur',
            $email,
            $username,
            $isAdmin ? 'ROLE_ADMIN' : 'ROLE_MODERATOR',
        ));

        $io->note('Tu peux maintenant te connecter avec ce compte et accéder à /admin pour modérer les reportages.');

        return Command::SUCCESS;
    }
}
