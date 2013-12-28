<?php
namespace Dellaert\DCIMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\Company;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{
    public function listAction()
    {
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"));
        return $this->render('DellaertDCIMBundle:Company:list.html.twig');
    }
    
    public function listDataAction()
    {
    	$request = $this->getRequest();
    	
    	$page = 1;
    	if( $request->request->get('page') != null && $request->request->get('page') != '' ) {
    		$page = $request->request->get('page');
    	}
    	
    	$sortname = 'companyName';
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
    	
    	$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Company');
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
		$qstring = 'SELECT COUNT(c.id) FROM DellaertDCIMBundle:Company c';
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
				'id' => $entity->getSlug(),
				'cell' => array($entity->getCompanyName(), $entity->getCity(), $entity->getCountry(), $entity->getVatNumber())
			);
		}

    	
    	$response = new Response(json_encode($data));
    	$response->headers->set('Content-Type', 'application/json');
    	
        return $response;
    }
    
    public function viewAction($slug)
    {
    	$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Company');
    	$entity = $repository->findOneBySlug($slug);
    	
	   	$this->get("white_october_breadcrumbs")
	   		->addItem("Home", $this->get("router")->generate("homepage"))
    			->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	if( $entity ) {
	    	$this->get("white_october_breadcrumbs")->addItem($entity->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$slug)));
    	} else {
	    	$this->get("white_october_breadcrumbs")->addItem("Unkown company", '');
    	}
    	
        return $this->render('DellaertDCIMBundle:Company:view.html.twig',array('entity'=>$entity));
    }
    
    public function addAction()
    {
    	$entity = new Company();
    	$form = $this->createAddEditForm($entity);
    	$request = $this->getRequest();
    	
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	
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
    				->addItem($entity->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getSlug())))
    				->addItem("Save",'');
    			return $this->render('DellaertDCIMBundle:Company:add.html.twig',array('entity'=>$entity));
    		}
    	}
    	$this->get("white_october_breadcrumbs")->addItem("Add company", '');
        return $this->render('DellaertDCIMBundle:Company:add.html.twig',array('form'=>$form->createView()));
    }
    
    public function editAction($id)
    {
    	$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Company')->find($id);
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	if( $entity ) {
    		$this->get("white_october_breadcrumbs")->addItem($entity->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getSlug())));
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
	    			return $this->render('DellaertDCIMBundle:Company:edit.html.twig',array('entity'=>$entity));
	    		}
	    	}
	    	$this->get("white_october_breadcrumbs")->addItem("Edit",'');
	        return $this->render('DellaertDCIMBundle:Company:edit.html.twig',array('form'=>$form->createView(),'entity'=>$entity));
    	}
    	$this->get("white_october_breadcrumbs")->addItem("Unkown company", '');
    	return $this->render('DellaertDCIMBundle:Company:edit.html.twig');
    }
    
    public function deleteAction($id)
    {
    	$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Company')->find($id);
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Companies", $this->get("router")->generate("CompanyList"));
    	if( $entity ) {
    		$this->get("white_october_breadcrumbs")
    			->addItem($entity->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getSlug())))
    			->addItem("Delete",'');
    		$em = $this->getDoctrine()->getManager();
    		$em->remove($entity);
    		$em->flush();
    		return $this->render('DellaertDCIMBundle:Company:delete.html.twig',array('entity'=>$entity));
    	}
    	$this->get("white_october_breadcrumbs")->addItem("Unkown company", '');
    	return $this->render('DellaertDCIMBundle:Company:delete.html.twig');
    }
    
    public function createAddEditForm($entity)
    {
    	$fb = $this->createFormBuilder($entity);
    	$fb->add('companyName','text',array('max_length'=>255,'required'=>true,'label'=>'Name'));
    	$fb->add('street','text',array('max_length'=>255,'required'=>false,'label'=>'Street'));
    	$fb->add('streetnumber','text',array('max_length'=>255,'required'=>false,'label'=>'Street number'));
    	$fb->add('postalcode','text',array('max_length'=>255,'required'=>false,'label'=>'Postal code'));
    	$fb->add('city','text',array('max_length'=>255,'required'=>false,'label'=>'City'));
    	$fb->add('country','country',array('preferred_choices'=>array('BE')));
    	$fb->add('centralTelephone','text',array('max_length'=>255,'required'=>false,'label'=>'Telephone'));
    	$fb->add('centralEmail','email',array('max_length'=>255,'required'=>false,'label'=>'E-mail'));
    	$fb->add('vatNumber','text',array('max_length'=>255,'required'=>false,'label'=>'VAT'));
    	return $fb->getForm();
    }
}
