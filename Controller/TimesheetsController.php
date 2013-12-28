<?php
namespace Dellaert\DCIMBundle\Controller;

use Dellaert\DCIMBundle\Entity\WorkEntry;
use Dellaert\DCIMBundle\Entity\Project;
use Dellaert\DCIMBundle\Entity\Issue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\Company;

class TimesheetsController extends Controller
{
	public function showAction() {
		$user = $this->get('security.context')->getToken()->getUser();
    	$request = $this->getRequest();
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem($user->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$user->getCompany()->getSlug())))
			->addItem("Timesheets", $this->get("router")->generate("TimesheetsShow"));
		
		// Getting projects
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Project');
		$qb = $repository->createQueryBuilder('c');
		$qb->orderBy('c.title','asc');
		$query = $qb->getQuery();
		$results = $query->getResult();
		$choices = array();
		foreach($results as $entity) {
			$choices[$entity->getId()] = $entity->getTitle();
		}
		
		$def = array('startdate'=>(new \DateTime()), 'enddate'=>(new \DateTime()));
		$fb = $this->createFormBuilder($def);
		$fb->add('startdate','date',array('required'=>true,'label'=>'Start date'));
		$fb->add('enddate','date',array('required'=>true,'label'=>'End date'));
		$fb->add('project','choice',array('required'=>true,'label'=>'Project','choices'=>$choices));
		$fb->add('groupbyissue','checkbox',array('required'=>false,'label'=>'Group by issue'));
		$form = $fb->getForm();
		
		$workentries = array();
		$issues = array();
		$project = false;
		$groupByIssue = false;
		$totalAmount = 0;
		$amountPerIssue = array();
		
		if ($request->getMethod() == 'POST') {
			$form->handleRequest($request);
			$data = $form->getData();
			$groupByIssue = $data['groupbyissue'];
			
			$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Project');
			$project = $repository->findOneById($data['project']);
			
			$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:WorkEntry');
			$qb = $repository->createQueryBuilder('c');
			$qb->where('c.date >= :startdate and c.date <= :enddate');
			$qb->setParameter('startdate', $data['startdate']->format('Y-m-d H:i:s'));
			$qb->setParameter('enddate', $data['enddate']->format('Y-m-d').' 23:59:59');
			$qb->orderBy('c.date','ASC');
			$query = $qb->getQuery();
			$results = $query->getResult();
			
			$showResults = true;
			foreach($results as $workentry) {
				$issue = $workentry->getIssue();
				if( $issue->getProject()->getId() == $data['project'] ) {
					$issues[$issue->getId()] = $issue;
					if( $groupByIssue ) {
						$workentries[$issue->getId()][] = $workentry;
					} else {
						$workentries[] = $workentry;
					}
					if( !$issue->getStatsShowListOnly() ) {
						if( array_key_exists($issue->getId(), $amountPerIssue) ) {
							$amountPerIssue[$issue->getId()] += $workentry->getAmount();
						} else {
							$amountPerIssue[$issue->getId()] = $workentry->getAmount();
						}
						$totalAmount += $workentry->getAmount();
					}
				}
			}
			
			$totalAmount = number_format($totalAmount,2,'.','');
			foreach($amountPerIssue as $issueId => $amount) {
				$amountPerIssue[$issueId] = number_format($amount,2,'.','');
			}
			
		}
		
		return $this->render('DellaertDCIMBundle:Timesheets:show.html.twig',array('form'=>$form->createView(),'workentries'=>$workentries,'project'=>$project,'totalAmount'=>$totalAmount,'groupByIssue'=>$groupByIssue,'amountPerIssue'=>$amountPerIssue,'issues'=>$issues));
	}
}
