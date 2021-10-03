<?php

namespace PN\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PN\Bundle\TaskBundle\Entity\Order,
    PN\Bundle\UserBundle\Entity\RefreshToken;
use PN\Utils\PushNotification;

class RemoveRefreshTokenCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('remove-refresh-token')
                ->setDescription('Remove expired refresh tokens')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $refreshTokens = $em->getRepository('UserBundle:RefreshToken')->checkTokenValidity();
        foreach ($refreshTokens as $refreshToken) {
            $isExpired = PushNotification::checkExpiredRefreshToken($refreshToken->getToken());
            if ($isExpired) {
                $em->remove($refreshToken);
            } else {
                $refreshToken->setLastCheckValidity(new \DateTime());
                $em->persist($refreshToken);
            }
        }
        $em->flush();
    }

}
