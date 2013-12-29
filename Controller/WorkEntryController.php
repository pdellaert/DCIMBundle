<?php
namespace Dellaert\DCIMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\WorkEntry;
use Dellaert\DCIMBundle\Entity\Issue;
use Dellaert\DCIMBundle\Entity\Project;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;

class WorkEntryController extends Controller
{
	public function listAction()
	{
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"))
			->addItem("Projects", $this->get("router")->generate("ProjectList"))
			->addItem("Issues", $this->get("router")->generate("IssueList"))
			->addItem("Work entries", $this->get("router")->generate("WorkEntryList"));
		return $this->render('DellaertDCIMBundle:WorkEntry:list.html.twig');
	}
	
	public function listDataAction()
	{
		$request = $this->getRequest();
		
		$page = 1;
		if( $request->request->get('page') != null && $request->request->get('page') != '' ) {
			$page = $request->request->get('page');
		}
		
		$sortname = 'id';
		if( $request->request->get('sortname') != null && $request->request->get('sortname') != '' ) {
			$sortname = $request->request->get('sortname');
		}
		
		$sortorder = 'asc';
		if( $request->request->get('sortorder') != null && $request->request->get('sortorder') != '' ) {
			$sortorder = $request->request->get('sortorder');
		}
		
		$searchtype = '';
		if( $request->request->get('qtype') != null && $request->request->get('qtype') != '' ) {
			$searchtype = $request->request->get('qtype');
		}
		
		$searchquery = '';
		if( $request->request->get('query') != null && $request->request->get('query') != '' ) {
			$searchquery = $request->request->get('query');
		}
		
		$rp = 20;
		if( $request->request->get('rp') != null && $request->request->get('rp') != '' ) {
			$rp = $request->request->get('rp');
		}
		
		$pageStart = ($page-1)*$rp;
		
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:WorkEntry');
		$qb = $repository->createQueryBuilder('c');
		if( $searchquery != '' && $searchtype != '' ) {
			$qb->add('where',$qb->expr()->like('c.'.$searchtype, $qb->expr()->literal('%'.$searchquery.'%')));
		}
		$qb->orderBy('c.'.$sortname,$sortorder);
		$qb->setFirstResult($pageStart);
		$qb->setMaxResults($rp);
		$query = $qb->getQuery();
		$results = $query->getResult();
		
		$em = $this->getDoctrine()->getManager();
		$qstring = 'SELECT COUNT(c.id) FROM DellaertDCIMBundle:WorkEntry c';
		if( $searchquery != '' && $searchtype != '' ) {
			$qstring .= ' where '.$qb->expr()->like('c.'.$searchtype, $qb->expr()->literal('%'.$searchquery.'%'));
		}
		$query = $em->createQuery($qstring);
		$total = $query->getSingleScalarResult();
		
		$data['page'] = $page;
		$data['total'] = $total;
		$data['rows'] = array();
		foreach($results as $entity) {
			$data['rows'][] = array(
				'id' => $entity->getId(),
				'cell' => array($entity->getId(), $entity->getDate()->format('Y-m-d'), $entity->getAmount(), '#'.$entity->getIssue()->getId().' '.$entity->getIssue()->getTitle())
			);
		}

		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	public function viewAction($id)
	{
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:WorkEntry');
		$entity = $repository->find($id);
		
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->add("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getIssue()->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getIssue()->getProject()->getCompany()->getSlug())))
				->addItem($entity->getIssue()->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getIssue()->getProject()->getSlug())))
				->addItem($entity->getIssue()->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$entity->getIssue()->getId())))
				->addItem($entity->getDate()->format('Y-m-d').' - '.$entity->getAmount(), $this->get('router')->generate("WorkEntryViewId",array('id'=>$id)));
		} else {
			$this->get("white_october_breadcrumbs")
				->addItem("Projects", $this->get("router")->generate("ProjectList"))
				->addItem("Issues", $this->get("router")->generate("IssueList"))
				->addItem("Work entries", $this->get("router")->generate("WorkEntryList"))
				->addItem("Unkown issue", '');
		}
		
		return $this->render('DellaertDCIMBundle:WorkEntry:view.html.twig',array('entity'=>$entity));
	}
	
	public function addAction($id)
	{
		$entity = new WorkEntry();
		$entity->setDate(new \DateTime());
		$request = $this->getRequest();
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		
		if( $id > 0 ) {
			$issue = $this->getDoctrine()
				->getRepository('DellaertDCIMBundle:Issue')
				->find($id);
			if( $issue ) {
				$entity->setIssue($issue);
				$this->get('session')->set('return_url',$this->get('router')->generate('IssueViewId', array('id'=>$issue->getId())));
				$this->get("white_october_breadcrumbs")
					->addItem($issue->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$issue->getProject()->getCompany()->getSlug())))
					->addItem($issue->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$issue->getProject()->getSlug())))
					->addItem($issue->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$issue->getId())));
			} else {
				$this->get("white_october_breadcrumbs")
					->addItem("Projects", $this->get("router")->generate("ProjectList"))
					->addItem("Issues", $this->get("router")->generate("IssueList"));
			}
		} elseif( $request->getMethod() != 'POST' ) {
			$this->get('session')->remove('return_url');
		}
		if( $this->get('session')->get('return_url') == null || $this->get('session')->get('return_url') == '' ) {
			$this->get('session')->set('return_url',$this->get('router')->generate('IssueList'));
		}
		
		$form = $this->createAddEditForm($entity);
		if( $request->getMethod() == 'POST' ) {
			$form->handleRequest($request);	
			if( $form->isValid() ) {
				$user = $this->get('security.context')->getToken()->getUser();
				$entity->setCreatedBy($user);
				$entity->setEnabled(true);
				$entity->preInsert();
				$em = $this->getDoctrine()->getManager();
				$em->persist($entity);
				$em->flush();
				$this->get("white_october_breadcrumbs")
					->addItem($entity->getDate()->format('Y-m-d').' - '.$entity->getAmount(), $this->get("router")->generate("WorkEntryViewId",array('id'=>$entity->getId())))
					->addItem("Save",'');
				return $this->render('DellaertDCIMBundle:WorkEntry:add.html.twig',array('entity'=>$entity));
			}
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Projects", $this->get("router")->generate("ProjectList"))
			->addItem("Issues", $this->get("router")->generate("IssueList"))
			->addItem("Work entries", $this->get("router")->generate("WorkEntryList"))
			->addItem("Add work entry", '');
		return $this->render('DellaertDCIMBundle:WorkEntry:add.html.twig',array('form'=>$form->createView(),'id'=>$id));
	}
	
	public function editAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:WorkEntry')->find($id);
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getIssue()->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getIssue()->getProject()->getCompany()->getSlug())))
				->addItem($entity->getIssue()->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getIssue()->getProject()->getSlug())))
				->addItem($entity->getIssue()->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$entity->getIssue()->getId())))
				->addItem($entity->getDate()->format('Y-m-d').' - '.$entity->getAmount(), $this->get("router")->generate("WorkEntryViewId",array('id'=>$id)));
			$form = $this->createAddEditForm($entity);
			$request = $this->getRequest();
			if( $request->getMethod() == 'POST' ) {
				$form->handleRequest($request);	
				if( $form->isValid() ) {
					$entity->preUpdate();
					$em = $this->getDoctrine()->getManager();
					$em->persist($entity);
					$em->flush();
					$this->get("white_october_breadcrumbs")->addItem("Save",'');
					return $this->render('DellaertDCIMBundle:WorkEntry:edit.html.twig',array('entity'=>$entity));
				}
			}
			$this->get("white_october_breadcrumbs")->addItem("Edit",'');
			return $this->render('DellaertDCIMBundle:WorkEntry:edit.html.twig',array('form'=>$form->createView(),'entity'=>$entity));
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Projects", $this->get("router")->generate("ProjectList"))
			->addItem("Issues", $this->get("router")->generate("IssueList"))
			->addItem("Work entries", $this->get("router")->generate("WorkEntryList"))
			->addItem("Unkown issue", '');
		return $this->render('DellaertDCIMBundle:WorkEntry:edit.html.twig');
	}
	
	public function deleteAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:WorkEntry')->find($id);
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getIssue()->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getIssue()->getProject()->getCompany()->getSlug())))
				->addItem($entity->getIssue()->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getIssue()->getProject()->getSlug())))
				->addItem($entity->getIssue()->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$entity->getIssue()->getId())))
				->addItem($entity->getDate()->format('Y-m-d').' - '.$entity->getAmount(), $this->get("router")->generate("WorkEntryViewId",array('id'=>$id)))
				->addItem("Delete",'');
			$this->get('session')->set('return_url',$this->get('router')->generate('IssueViewId', array('id'=>$entity->getIssue()->getId())));
			$em = $this->getDoctrine()->getManager();
			$em->remove($entity);
			$em->flush();
			return $this->render('DellaertDCIMBundle:WorkEntry:delete.html.twig',array('entity'=>$entity));
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Projects", $this->get("router")->generate("ProjectList"))
			->addItem("Issues", $this->get("router")->generate("IssueList"))
			->addItem("Work entries", $this->get("router")->generate("WorkEntryList"))
			->addItem("Unkown issue", '');
		return $this->render('DellaertDCIMBundle:WorkEntry:delete.html.twig');
	}
	
	public function importAction()
	{
		// FORMAT CSV: Date, Project, Issue, Comment, Time
	$file = $this->getRequest()->server->get('DOCUMENT_ROOT').'/../data/import/timesheet.csv';
		if( ($fh = fopen($file,"r")) !== FALSE ) {
			$em = $this->getDoctrine()->getManager();
			while( ($data = fgetcsv($fh)) !== FALSE ) {
				$project = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Project')->find($data[1]);
				if( $project ) {
					$issues = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Issue')->findBy(array('title'=>$data[2],'project'=>$data[1]));
					if( empty($issues) ) {
						$issue = new Issue();
						$issue->setProject($project);
						$issue->setTitle($data[2]);
						$issue->setRate($project->getRate());
						$issue->setEnabled(true);
					$issue->setStatsShowListOnly(false);
						$issue->preInsert();
						$em->persist($issue);
					} else {
						$issue = $issues[0];
					}
					$workentry = new WorkEntry();
					$workentry->setIssue($issue);
					$workentry->setDate(new \DateTime($data[0]));
					$workentry->setAmount($data[4]);
					$workentry->setDescription($data[3]);
					$workentry->setEnabled(true);
					$workentry->preInsert();
					$em->persist($workentry);
					$em->flush();
				}
			}
		}
		return $this->render('DellaertDCIMBundle:WorkEntry:import.html.twig');
	}
	
	public function createAddEditForm($entity)
	{
		$fb = $this->createFormBuilder($entity);
		$fb->add('date','date',array('required'=>true,'label'=>'Date'));
		$fb->add('amount','number',array('precision'=>2,'required'=>true,'label'=>'Amount'));
		$fb->add('description','textarea',array('required'=>false,'label'=>'Description'));
		$fb->add('issue','entity',array(
			'class' => 'Dellaert\\DCIMBundle\\Entity\\Issue',
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('c')->orderBy('c.title','ASC');
			},
			'property' => 'title',
			'required' => true,
			'label' => 'Issue'
		));
		return $fb->getForm();
	}
}
