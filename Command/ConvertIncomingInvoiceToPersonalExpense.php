<?php
namespace Dellaert\DCIMBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\DCIMBundle\Entity\IncommingInvoice;
use Dellaert\DCIMBundle\Entity\PersonalExpense;

class ConvertIncomingInvoiceToPersonalExpense extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('dcim:convert-ii-to-pe')
			->setDescription('Convert incomming invoices to personal expenses for certain types of numbers.')
			->addOption(
				'number-start',
				null,
				InputOption::VALUE_REQUIRED,
				'The value with which the Incoming invoice number has to start.'
			)
			->addOption(
				'purge',
				null,
				InputOption::VALUE_NONE,
				'Purge the converted Incoming invoices.'
			)
			->addOption(
				'test',
				null,
				InputOption::VALUE_NONE,
				'Only run a verbose test, displaying the conversions that would be made without testing.'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// Handling options
		$numberStart = $input->getOption('number-start');
		$purge = $input->getOption('purge');
		$test = $input->getOption('test');

		$db = $this->getContainer()->get('doctrine');

		$repository = $db->getRepository('DellaertDCIMBundle:IncomingInvoice');
		$qb = $repository->createQueryBuilder('c');
		$qb->add('where',$qb->expr()->like('c.invoiceNumber', $qb->expr()->literal($numberStart.'%')));
		$query = $qb->getQuery();
		$results = $query->getResult();

		$em = $db->getManager();
		foreach( $results as $ii ) {
			if($test) {
				echo('Found Incoming Invoice: '.$ii->getInvoiceNumber().' - '.$ii->getTitle()."\n");
			}

			$pe = new PersonalExpense();
			$pe->setCreatedBy($ii->getCreatedBy());
			$pe->setPayed($ii->getPayed());
			$pe->setEnabled(true);
			$pe->setCategory($ii->getCategory());
			$pe->setExpenseNumber($ii->getInvoiceNumber());
			$pe->setTitle($ii->getTitle());
			$pe->setSlug($ii->getSlug());
			$pe->setDate($ii->getDate());
			$pe->setDueDate($ii->getDueDate());
			$pe->setAmount($ii->getAmount()+$ii->getVat());
			$pe->setFilePath($ii->getFilePath());
			$pe->preInsert();

			if($test) {
				echo('Created Personal Expense: '.$pe->getExpenseNumber().' - '.$pe->getTitle()."\n");
			} else {
				$em->persist($pe);
				$em->flush();
			}

			if($test) {
				echo('Copying file '.$ii->getFileDir().$ii->getFilePath().' to '.$pe->getFileDir().$pe->getFilePath()."\n");
			} else {
				if( !is_dir($pe->getFileDir()) ) {
					mkdir($pe->getFileDir());
				}
				copy($ii->getFileDir().$ii->getFilePath(),$pe->getFileDir().$pe->getFilePath());
			}

			if($purge) {
				if($test) {
					echo('Purging Incoming Invoice: '.$ii->getInvoiceNumber().' - '.$ii->getTitle()."\n");
				} else {
					$em->remove($entity);
					$em->flush();
				}

				if($test) {
					echo('Deleting file '.$ii->getFileDir().$ii->getFilePath()."\n");
				} else {
					$em->postRemove();
				}
			}

			if($test) {
				echo("--------------------------------------------------------------------------------\n");
			}

		}
	}
}
