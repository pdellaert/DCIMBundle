<?php
namespace Dellaert\DCIMBundle\Command;

Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dellaert\DCIMBundle\Entity\IncommingInvoice;
use Dellaert\DCIMBundle\Entity\PersonalExpense;

class ConvertIncommingInvoiceToPersonalExpense extends ContainerAwareCommand
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
				'The value with which the Incomming invoice number has to start.'
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
		$test = $input->getOption('test');

		$db = $this->getContainer('doctrine');

		$repository = $db->getRepository('DCIM:IncommingInvoice');
		$qb = $repository->createQueryBuilder('c');
		if( $searchquery != '' && $searchtype != '' ) {
			$qb->add('where',$qb->expr()->like('c.invoiceNumber', $qb->expr()->literal($numberStart.'%')));
		}
		$query = $qb->getQuery();
		$results = $query->getResult();

		foreach( $results as $ii ) {
			if($test) {
				echo('Found Incomming Invoice: '.$ii->getInvoiceNumber().' - '.$ii->getTitle()."\n");
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
				$em = $db->getManager();
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

			if($test) {
				echo("--------------------------------------------------------------------------------\n");
			}

		}
	}
}
