<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\Currency;
use App\Exception\CurrencyRaterException;
use App\Service\CurrencyRater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRatesCommand extends ContainerAwareCommand
{
    private $rater;
    private $em;
    private $logger;

    public function __construct(CurrencyRater $rater, EntityManagerInterface $em, LoggerInterface $logger)
    {
        parent::__construct();

        $this->rater = $rater;
        $this->em = $em;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('app:update-rates')
            ->setDescription('Update currency rates');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $success = $fail = [];
        foreach ($this->em->getRepository(Currency::class)->findAll() as $currency) {
            /* @var $currency \App\Entity\Currency */
            try {
                $currency->setRate(floatval($this->rater->getRate($currency->getId())));
                $this->em->persist($currency);
                $success[] = $currency->getId();
            } catch (CurrencyRaterException $e) {
                $this->logger->warning('Could not retrieve rate for currency '.$currency->getId());
                $fail[] = $currency->getId();
            }
        }
        $this->em->flush();
        $this->logger->info('Currencies updated');
        $output->writeln(sprintf('Updated: %s', join(', ', $success)));
        $output->writeln(sprintf('Not updated: %s', join(', ', $fail)));
    }
}
