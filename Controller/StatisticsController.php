<?php
namespace Dellaert\DCIMBundle\Controller;

use Dellaert\DCIMBundle\Entity\WorkEntry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\Company;
use Dellaert\DCIMBundle\Entity\OutgoingInvoice;
use Dellaert\DCIMBundle\Entity\IncomingInvoice;
use Dellaert\DCIMBundle\Entity\PersonalExpense;

class StatisticsController extends Controller
{
	public function openInvoicesAction($uid,$cid)
	{
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
		$qb = $repository->createQueryBuilder('c');
		$qb->add('where','c.originCompany = :companyId and c.payed = :payed')
			->add('orderBy','c.invoiceNumber')
			->setParameters(array('companyId'=>$cid,'payed'=>false));
		$query = $qb->getQuery();
		$outgoingInvoices = $query->getResult();
		$outgoingTotal = 0;
		$outgoingTotalWVAT = 0;
		foreach( $outgoingInvoices as $invoice ) {
			$outgoingTotalWVAT += $invoice->getTotalWithVAT();
			$outgoingTotal += $invoice->getTotalWoVAT();
		}
		
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:IncomingInvoice');
		$qb = $repository->createQueryBuilder('c');
				$qb->add('where','c.targetCompany = :companyId and c.payed = :payed')
					->add('orderBy','c.dueDate')
					->setParameters(array('companyId'=>$cid,'payed'=>false));
		$query = $qb->getQuery();
		$incomingInvoices = $query->getResult();
		$incomingTotal = 0;
		$incomingTotalWVAT = 0;
		foreach( $incomingInvoices as $invoice ) {
			$incomingTotalWVAT += $invoice->getTotalWithVAT();
			$incomingTotal += $invoice->getTotalWoVAT();
		}
		
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:PersonalExpense');
		$qb = $repository->createQueryBuilder('c');
				$qb->add('where','c.createdBy = :userId and c.payed = :payed')
					->add('orderBy','c.dueDate')
					->setParameters(array('userId'=>$uid,'payed'=>false));
		$query = $qb->getQuery();
		$personalExpenses = $query->getResult();
		$expenseTotal = 0;
		foreach( $personalExpenses as $expense ) {
			$expenseTotal += $expense->getAmount();
		}
		
		return $this->render('DellaertDCIMBundle:Statistics:openinvoices.html.twig',array('outgoingInvoices'=>$outgoingInvoices,'outgoingTotal'=>number_format($outgoingTotal,'2','.',''),'outgoingTotalWVAT'=>number_format($outgoingTotalWVAT,'2','.',''),'incomingInvoices'=>$incomingInvoices,'incomingTotal'=>number_format($incomingTotal,'2','.',''),'incomingTotalWVAT'=>number_format($incomingTotalWVAT,'2','.',''),'personalExpenses'=>$personalExpenses,'expenseTotal'=>number_format($expenseTotal,'2','.','')));
	}

	public function companyRevenueExpensesAction() {
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
				$qb = $repository->createQueryBuilder('c');
		$qb->add('orderBy','c.date ASC')
					->setFirstResult(0)
		->setMaxResults(1);
		$query = $qb->getQuery();
		$invoice = $query->getSingleResult();
		$years = array();
		if( $invoice ) {
			$now = new \DateTime();
			for( $y = $invoice->getDate()->format('Y'); $y <= $now->format('Y'); $y++ ) {
				$years[] = $y;
			}
		}
		return $this->render('DellaertDCIMBundle:Statistics:companyrevenueexpenses.html.twig',array('years'=>$years));
	}

	public function companyRevenueExpensesByYearAction($id,$year)
	{
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
		$qb = $repository->createQueryBuilder('c');
		$qb->add('where','c.originCompany = :companyId and c.date LIKE :year')
			->add('orderBy','c.invoiceNumber')
			->setParameters(array('companyId'=>$id,'year'=>$year.'%'));
		$query = $qb->getQuery();
		$outInvoices = $query->getResult();
		$outPerCompany = array();
		$outPerMonth = array('01'=>0,'02'=>0,'03'=>0,'04'=>0,'05'=>0,'06'=>0,'07'=>0,'08'=>0,'09'=>0,'10'=>0,'11'=>0,'12'=>0);
		$outTotal = 0;
		foreach( $outInvoices as $invoice ) {
			$c = $invoice->getProject()->getCompany()->getCompanyName();
			$d = $invoice->getDate()->format('m');
			if( array_key_exists($c,$outPerCompany) ) {
				$outPerCompany[$c] += $invoice->getTotalWoVAT();
			} else {
				$outPerCompany[$c] = $invoice->getTotalWoVAT();
			}
			if( array_key_exists($d,$outPerMonth) ) {
				$outPerMonth[$d] += $invoice->getTotalWoVAT();
			} else {
				$outPerMonth[$d] = $invoice->getTotalWoVAT();
			}
			$outTotal += $invoice->getTotalWoVAT();
		}
		foreach( $outPerCompany as $key => $value ) {
			$outPerCompany[$key] = number_format($value,2,'.','');
		}

		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:IncomingInvoice');
		$qb = $repository->createQueryBuilder('c');
		$qb->add('where','c.targetCompany = :companyId and c.date LIKE :year')
			->add('orderBy','c.date')
			->setParameters(array('companyId'=>$id,'year'=>$year.'%'));
		$query = $qb->getQuery();
		$inInvoices = $query->getResult();
		$inPerCategory = array();
		$inPerMonth = array('01'=>0,'02'=>0,'03'=>0,'04'=>0,'05'=>0,'06'=>0,'07'=>0,'08'=>0,'09'=>0,'10'=>0,'11'=>0,'12'=>0);
		$inTotal = 0;
		foreach( $inInvoices as $invoice ) {
			$c = $invoice->getCategory()->getTitle();
			$d = $invoice->getDate()->format('m');
			if( array_key_exists($c,$inPerCategory) ) {
				$inPerCategory[$c] += $invoice->getTotalWoVAT();
			} else {
				$inPerCategory[$c] = $invoice->getTotalWoVAT();
			}
			if( array_key_exists($d,$inPerMonth) ) {
				$inPerMonth[$d] += $invoice->getTotalWoVAT();
			} else {
				$inPerMonth[$d] = $invoice->getTotalWoVAT();
			}
			$inTotal += $invoice->getTotalWoVAT();
		}
		foreach( $inPerCategory as $key => $value ) {
			$inPerCategory[$key] = number_format($value,2,'.','');
		}
		$resultPerMonth = array('01'=>0,'02'=>0,'03'=>0,'04'=>0,'05'=>0,'06'=>0,'07'=>0,'08'=>0,'09'=>0,'10'=>0,'11'=>0,'12'=>0);
		foreach( $inPerMonth as $key => $value ) {
			$inPerMonth[$key] = number_format($inPerMonth[$key],2,'.','');
			$outPerMonth[$key] = number_format($outPerMonth[$key],2,'.','');
		$resultPerMonth[$key] = number_format($outPerMonth[$key]-$inPerMonth[$key],2,'.','');
		}

		arsort($outPerCompany);
		arsort($inPerCategory);

		return $this->render('DellaertDCIMBundle:Statistics:companyrevenueexpensesbyyear.html.twig',array('year'=>$year,'outInvoices'=>$outInvoices,'outTotal'=>number_format($outTotal,'2','.',''),'outPerCompany'=>$outPerCompany,'outPerMonth'=>$outPerMonth,'inInvoices'=>$inInvoices,'inTotal'=>number_format($inTotal,'2','.',''),'inPerCategory'=>$inPerCategory,'inPerMonth'=>$inPerMonth,'resultPerMonth'=>$resultPerMonth));
	}
	
	public function vatAction() {
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
				$qb = $repository->createQueryBuilder('c');
		$qb->add('orderBy','c.date ASC')
					->setFirstResult(0)
		->setMaxResults(1);
		$query = $qb->getQuery();
		$invoice = $query->getSingleResult();
		$years = array();
		if( $invoice ) {
			$now = new \DateTime();
			for( $y = $invoice->getDate()->format('Y'); $y <= $now->format('Y'); $y++ ) {
				$years[] = $y;
			}
		}
		return $this->render('DellaertDCIMBundle:Statistics:vat.html.twig',array('years'=>$years));
	}
	
	public function vatByYearAction($id,$year)
	{
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
		$qb = $repository->createQueryBuilder('c');
		$qb->add('where','c.originCompany = :companyId and c.date LIKE :year')
			->add('orderBy','c.invoiceNumber')
			->setParameters(array('companyId'=>$id,'year'=>$year.'%'));
		$query = $qb->getQuery();
		$invoices = $query->getResult();
		$perCompany = array();
		$perQuarter = array('Q1'=>0,'Q2'=>0,'Q3'=>0,'Q4'=>0);
		$total = 0;
		$totalVat = 0;
		foreach( $invoices as $invoice ) {
			$c = $invoice->getProject()->getCompany()->getCompanyName();
			$d = $invoice->getDate()->format('m');
			if( array_key_exists($c,$perCompany) ) {
				$perCompany[$c] += $invoice->getTotalVAT();
			} else {
				$perCompany[$c] = $invoice->getTotalVAT();
			}
			switch( $d ) {
				case "01":
				case "02":
				case "03":
					$d = "Q1";
					break;
				case "04":
				case "05":
				case "06":
					$d = "Q2";
					break;
				case "07":
				case "08":
				case "09":
					$d = "Q3";
					break;
				case "10":
				case "11":
				case "12":
					$d = "Q4";
					break;
			}
			if( array_key_exists($d,$perQuarter) ) {
				$perQuarter[$d] += $invoice->getTotalVAT();
			} else {
				$perQuarter[$d] = $invoice->getTotalVAT();
			}
			$total += $invoice->getTotalWoVAT();
			$totalVat += $invoice->getTotalVAT();
		}
		foreach( $perCompany as $key => $value ) {
			$perCompany[$key] = number_format($value,2,'.','');
		}
		foreach( $perQuarter as $key => $value ) {
			$perQuarter[$key] = number_format($value,2,'.','');
		}
		return $this->render('DellaertDCIMBundle:Statistics:vatbyyear.html.twig',array('year'=>$year,'invoices'=>$invoices,'total'=>number_format($total,'2','.',''),'totalVat'=>number_format($totalVat,'2','.',''),'perCompany'=>$perCompany,'perQuarter'=>$perQuarter));
	}

	public function activityAction() {
		return $this->render('DellaertDCIMBundle:Statistics:activity.html.twig');
	}
	
	public function lastActivityAction($mode, $back) {
		switch($mode) {
			case "day":
				$start = new \DateTime();
				$start->setTime(0, 0);
				$start->sub(new \DateInterval('P'.$back.'D'));
				$run = new \DateTime();
				$run->setTime(0, 0);
				$run->sub(new \DateInterval('P'.$back.'D'));
				$end = new \DateTime();
				$end->setTime(0, 0);
				if( $back > 0 ) {
					$end->sub(new \DateInterval('P'.($back-1).'D'));
				} else {
					$end->add(new \DateInterval('P1D'));
				}
				break;
			case "week":
				$start = new \DateTime();
				$start->setTime(0, 0);
				$start->sub(new \DateInterval('P'.($start->format('N')-1).'D'));
				$start->sub(new \DateInterval('P'.$back.'W'));
				$run = new \DateTime();
				$run->setTime(0, 0);
				$run->sub(new \DateInterval('P'.($run->format('N')-1).'D'));
				$run->sub(new \DateInterval('P'.$back.'W'));
				$end = new \DateTime();
				$end->setTime(0, 0);
				$end->sub(new \DateInterval('P'.($end->format('N')-1).'D'));
				if( $back > 0 ) {
					$end->sub(new \DateInterval('P'.($back-1).'W'));
				} else {
					$end->add(new \DateInterval('P1W'));
				}
				break;
			case "month":
				$start = new \DateTime();
				$start->setTime(0, 0);
				$start->sub(new \DateInterval('P'.($start->format('j')-1).'D'));
				$start->sub(new \DateInterval('P'.$back.'M'));
				$run = new \DateTime();
				$run->setTime(0, 0);
				$run->sub(new \DateInterval('P'.($run->format('j')-1).'D'));
				$run->sub(new \DateInterval('P'.$back.'M'));
				$end = new \DateTime();
				$end->setTime(0, 0);
				$end->sub(new \DateInterval('P'.($end->format('j')-1).'D'));
				if( $back > 0 ) {
					$end->sub(new \DateInterval('P'.($back-1).'M'));
				} else {
					$end->add(new \DateInterval('P1M'));
				}
				break;
			case "year":
				$start = new \DateTime();
				$start->setTime(0, 0);
				$start->sub(new \DateInterval('P'.($start->format('z')).'D'));
				$start->sub(new \DateInterval('P'.$back.'Y'));
				$run = new \DateTime();
				$run->setTime(0, 0);
				$run->sub(new \DateInterval('P'.($run->format('z')).'D'));
				$run->sub(new \DateInterval('P'.$back.'Y'));
				$end = new \DateTime();
				$end->setTime(0, 0);
				$end->sub(new \DateInterval('P'.($end->format('z')).'D'));
				if( $back > 0 ) {
					$end->sub(new \DateInterval('P'.($back-1).'Y'));
				} else {
					$end->add(new \DateInterval('P1Y'));
				}
				break;
		}
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:WorkEntry');
		$qb = $repository->createQueryBuilder('c');
		$qb->add('where','c.date >= :start and c.date < :end')
			->add('orderBy','c.date asc')
			->setParameters(array('start'=>$start->format('Y-m-d H:i:s'),'end'=>$end->format('Y-m-d H:i:s')));
		$query = $qb->getQuery();
		$workentries = $query->getResult();
		$issues = array();
		$perIssue = array();
		$allIssues = array();
		$allIssuesRevenue = array();
		$perProject = array();
		$perDay = array();
		$perDayAmount = array();
	$totalRevenue = 0;
	// BUG: Seems to have issues in PHP 5.3.2, returns wrong result
	/*
	echo $start->format('Y-m-d H:i:s') . "\n";
	echo $start->getTimezone()->getName() . "\n";
	echo $end->format('Y-m-d H:i:s') . "\n";
		$dt = $start->diff($end);
		$differenceDays = $dt->format('%a');
	echo $start->format('Y-m-d H:i:s') . "\n";
	echo $start->getTimezone()->getName() . "\n";
	echo $end->format('Y-m-d H:i:s') . "\n";
	*/
	// Implementing other solution, not that elegant...
		switch($mode) {
			case "day":
			$differenceDays = 1;
			break;
			case "week":
			$differenceDays = 7;
			break;
			case "month":
			$differenceDays = date('t',$start->getTimestamp());
			break;
			case "year":
			if( date('L',mktime(0,0,0,1,1,$start->format('Y'))) == 0 ) {
				$differenceDays = 365;
			} else {
				$differenceDays = 366;
			}
			break;
	}
	
		for( $i = 1; $i <= $differenceDays; $i ++ ) {
			$perDay[$run->format('D d/m')] = 0;
			$perDayAmount[$run->format('D d/m')] = 0;
			$run->add(new \DateInterval('P1D'));
		}
		foreach( $workentries as $workentry ) {
			$issue = $workentry->getIssue();
			$issues[$issue->getId()] = $issue;
			$project = $issue->getProject();
		$company = $project->getCompany();
			if( !$issue->getStatsShowListOnly() ) {
				if( array_key_exists($company->getCompanyName().' - '.$project->getTitle(), $perProject) ) {
					$perProject[$company->getCompanyName().' - '.$project->getTitle()] += $workentry->getAmount();
				} else {
					$perProject[$company->getCompanyName().' - '.$project->getTitle()] = $workentry->getAmount();
				}
				if( array_key_exists('#'.$issue->getId().' - '.$issue->getTitle(), $perIssue) ) {
					$perIssue['#'.$issue->getId().' - '.$issue->getTitle()] += $workentry->getAmount();
				} else {
					$perIssue['#'.$issue->getId().' - '.$issue->getTitle()] = $workentry->getAmount();
				}
				if( array_key_exists($workentry->getDate()->format('D d/m'), $perDay) ) {
					$perDay[$workentry->getDate()->format('D d/m')] += $workentry->getAmount();
				} else {
					$perDay[$workentry->getDate()->format('D d/m')] = $workentry->getAmount();
				}
			}
			if( array_key_exists($issue->getId(), $allIssues) ) {
				$allIssues[$issue->getId()] += $workentry->getAmount();
				$allIssuesRevenue[$issue->getId()] += $workentry->getAmount()*$workentry->getIssue()->getRate();
			} else {
				$allIssues[$issue->getId()] = $workentry->getAmount();
				$allIssuesRevenue[$issue->getId()] = $workentry->getAmount()*$workentry->getIssue()->getRate();
			}
			if( array_key_exists($workentry->getDate()->format('D d/m'), $perDayAmount) ) {
				$perDayAmount[$workentry->getDate()->format('D d/m')] += $workentry->getIssue()->getRate()*$workentry->getAmount();
			} else {
				$perDayAmount[$workentry->getDate()->format('D d/m')] = $workentry->getIssue()->getRate()*$workentry->getAmount();
			}
		$totalRevenue += $workentry->getIssue()->getRate()*$workentry->getAmount();
		}
		foreach( $perIssue as $key => $value ) {
			$perIssue[$key] = number_format($value,2,'.','');
		}
		foreach( $allIssues as $key => $value ) {
			$allIssues[$key] = number_format($value,2,'.','');
		}
		foreach( $allIssuesRevenue as $key => $value ) {
			$allIssuesRevenue[$key] = number_format($value,2,'.','');
		}
		return $this->render('DellaertDCIMBundle:Statistics:lastactivity.html.twig',array('perIssue'=>$perIssue,'allIssues'=>$allIssues,'allIssuesRevenue'=>$allIssuesRevenue,'perProject'=>$perProject,'perDay'=>$perDay,'perDayAmount'=>$perDayAmount,'issues'=>$issues,'start'=>$start,'end'=>$end->sub(new \DateInterval('P1D')),'mode'=>$mode,'back'=>$back,'totalRevenue'=>number_format($totalRevenue,2,'.','')));
	}
}
