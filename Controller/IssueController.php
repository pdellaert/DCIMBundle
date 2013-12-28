<?php
namespace Dellaert\DCIMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\Issue;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;

class IssueController extends Controller
{
    public function listAction()
    {
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"))
    		->addItem("Projects", $this->get("router")->generate("ProjectList"))
    		->addItem("Issues", $this->get("router")->generate("IssueList"));
        return $this->render('DellaertDCIMBundle:Issue:list.html.twig');
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
    	
    	$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Issue');
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
		$qstring = 'SELECT COUNT(c.id) FROM DellaertDCIMBundle:Issue c';
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
				'cell' => array($entity->getId(), $entity->getTitle(), $entity->getProject()->getTitle(), $entity->getRate())
			);
		}

    	
    	$response = new Response(json_encode($data));
    	$response->headers->set('Content-Type', 'application/json');
    	
        return $response;
    }
    
    public function viewAction($id)
    {
    	$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Issue');
    	$entity = $repository->find($id);
    	
    	$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
		->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	if( $entity ) {
	    	$this->get("white_october_breadcrumbs")
	    		->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
	    		->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
	    		->addItem($entity->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$id)));
    	} else {
	    	$this->get("white_october_breadcrumbs")
    			->addItem("Projects", $this->get("router")->generate("ProjectList"))
    			->addItem("Issues", $this->get("router")->generate("IssueList"))
	    		->addItem("Unkown issue", '');
    	}
    	
        return $this->render('DellaertDCIMBundle:Issue:view.html.twig',array('entity'=>$entity));
    }
    
    public function addAction($id)
    {
    	$entity = new Issue();
    	$request = $this->getRequest();
    	$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	
    	if( $id > 0 ) {
    		$project = $this->getDoctrine()
    			->getRepository('DellaertDCIMBundle:Project')
    			->find($id);
    		if( $project ) {
    			$entity->setProject($project);
    			$entity->setRate($project->getRate());
    			$this->get('session')->set('return_url',$this->get('router')->generate('ProjectViewSlug', array('slug'=>$project->getSlug())));
		    	$this->get("white_october_breadcrumbs")
		    		->addItem($project->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$project->getCompany()->getSlug())))
		    		->addItem($project->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$project->getSlug())));
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
    			if( $entity->getRate() == null || $entity->getRate() == '' ) {
    				$entity->setRate($entity->getProject()->getRate());
    			}
    			$em = $this->getDoctrine()->getManager();
    			$em->persist($entity);
    			$em->flush();
    			$this->get("white_october_breadcrumbs")
    				->addItem($entity->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$entity->getId())))
    				->addItem("Save",'');
    			return $this->render('DellaertDCIMBundle:Issue:add.html.twig',array('entity'=>$entity));
    		}
    	}
    	$this->get("white_october_breadcrumbs")
   		->addItem("Projects", $this->get("router")->generate("ProjectList"))
   		->addItem("Issues", $this->get("router")->generate("IssueList"))
    		->addItem("Add issue", '');
        return $this->render('DellaertDCIMBundle:Issue:add.html.twig',array('form'=>$form->createView(),'id'=>$id));
    }
    
    public function editAction($id)
    {
    	$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Issue')->find($id);
    	$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	if( $entity ) {
	    	$this->get("white_october_breadcrumbs")
	    		->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
	    		->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
	    		->addItem($entity->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$id)));
	    	$form = $this->createAddEditForm($entity);
	    	$request = $this->getRequest();
	    	if( $request->getMethod() == 'POST' ) {
	    		$form->handleRequest($request);	
	    		if( $form->isValid() ) {
	    			$entity->preUpdate();
	    			if( $entity->getRate() == null || $entity->getRate() == '' ) {
	    				$entity->setRate($entity->getProject()->getRate());
	    			}
	    			$em = $this->getDoctrine()->getManager();
	    			$em->persist($entity);
	    			$em->flush();
	    			$this->get("white_october_breadcrumbs")->addItem("Save",'');
	    			return $this->render('DellaertDCIMBundle:Issue:edit.html.twig',array('entity'=>$entity));
	    		}
	    	}
	    	$this->get("white_october_breadcrumbs")->addItem("Edit",'');
	        return $this->render('DellaertDCIMBundle:Issue:edit.html.twig',array('form'=>$form->createView(),'entity'=>$entity));
    	}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
   		->addItem("Issues", $this->get("router")->generate("IssueList"))
    		->addItem("Unkown issue", '');
    	return $this->render('DellaertDCIMBundle:Issue:edit.html.twig');
    }
    
    public function deleteAction($id)
    {
    	$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Issue')->find($id);
    	$this->get("white_october_breadcrumbs")
	   	->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	if( $entity ) {
	    	$this->get("white_october_breadcrumbs")
	    		->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
	    		->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
	    		->addItem($entity->getTitle(), $this->get("router")->generate("IssueViewId",array('id'=>$id)))
    			->addItem("Delete",'');
    		$this->get('session')->set('return_url',$this->get('router')->generate('ProjectViewSlug', array('slug'=>$entity->getProject()->getSlug())));
    		$em = $this->getDoctrine()->getManager();
    		$em->remove($entity);
    		$em->flush();
    		return $this->render('DellaertDCIMBundle:Issue:delete.html.twig',array('entity'=>$entity));
    	}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Issues", $this->get("router")->generate("IssueList"))
    		->addItem("Unkown issue", '');
    	return $this->render('DellaertDCIMBundle:Issue:delete.html.twig');
    }
    
    public function createAddEditForm($entity)
    {
    	$fb = $this->createFormBuilder($entity);
    	$fb->add('title','text',array('max_length'=>255,'required'=>true,'label'=>'Title'));
    	$fb->add('description','textarea',array('required'=>false,'label'=>'Description'));
    	$fb->add('rate','number',array('precision'=>2,'required'=>false,'label'=>'Rate'));
    	$fb->add('project','entity',array(
    		'class' => 'Dellaert\\DCIMBundle\\Entity\\Project',
    		'query_builder' => function(EntityRepository $er) {
    			return $er->createQueryBuilder('c')->orderBy('c.title','ASC');
    		},
    		'property' => 'title',
    		'required' => true,
    		'label' => 'Project'
    	));
    	$fb->add('statsShowListOnly','checkbox', array('required'=>false,'label'=>'Show only in list at stats'));
    	return $fb->getForm();
    }
}
