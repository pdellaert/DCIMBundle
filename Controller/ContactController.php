<?php
namespace Dellaert\DCIMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\Contact;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;

class ContactController extends Controller
{
	public function listAction()
	{
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"))
			->addItem("Contacts", $this->get("router")->generate("ContactList"));
		return $this->render('DellaertDCIMBundle:Contact:list.html.twig');
	}
	
	public function listDataAction()
	{
		$request = $this->getRequest();
		
		$page = 1;
		if( $request->request->get('page') != null && $request->request->get('page') != '' ) {
			$page = $request->request->get('page');
		}
		
		$sortname = 'lastname';
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
		
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Contact');
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
		$qstring = 'SELECT COUNT(c.id) FROM DellaertDCIMBundle:Contact c';
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
				'cell' => array($entity->getLastname(), $entity->getFirstname(), $entity->getCompany()->getCompanyName(), $entity->getEmail(), $entity->getTelephone())
			);
		}

		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	public function viewAction($slug)
	{
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Contact');
		$entity = $repository->findOneBySlug($slug);
		
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getCompany()->getSlug())))
				->addItem($entity->getLastname().', '.$entity->getFirstname(), $this->get("router")->generate("ContactViewSlug",array('slug'=>$slug)));
		} else {
			$this->get("white_october_breadcrumbs")
				->addItem("Contacts", $this->get("router")->generate("ContactList"))
				->addItem("Unkown contact", '');
		}
		
		return $this->render('DellaertDCIMBundle:Contact:view.html.twig',array('entity'=>$entity));
	}
	
	public function addAction($id)
	{
		$entity = new Contact();
		$request = $this->getRequest();
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		
		if( $id > 0 ) {
			$company = $this->getDoctrine()
				->getRepository('DellaertDCIMBundle:Company')
				->find($id);
			if( $company ) {
				$entity->setCompany($company);
				$this->get('session')->set('return_url',$this->get('router')->generate('CompanyViewSlug', array('slug'=>$company->getSlug())));
				$this->get("white_october_breadcrumbs")
					->addItem($entity->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getCompany()->getSlug())));
			} else {
				$this->get("white_october_breadcrumbs")
					->addItem("Contacts", $this->get("router")->generate("ContactList"));
			}
		} elseif( $request->getMethod() != 'POST' ) {
			$this->get('session')->remove('return_url');
		}
		if( $this->get('session')->get('return_url') == null || $this->get('session')->get('return_url') == '' ) {
			$this->get('session')->set('return_url',$this->get('router')->generate('ContactList'));
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
					->addItem($entity->getLastname().', '.$entity->getFirstname(), $this->get("router")->generate("ContactViewSlug",array('slug'=>$entity->getSlug())))
					->addItem("Save",'');
				return $this->render('DellaertDCIMBundle:Contact:add.html.twig',array('entity'=>$entity));
			}
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Contacts", $this->get("router")->generate("ContactList"))
			->addItem("Add contact", '');
		return $this->render('DellaertDCIMBundle:Contact:add.html.twig',array('form'=>$form->createView(),'id'=>$id));
	}
	
	public function editAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Contact')->find($id);
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getCompany()->getSlug())))
				->addItem($entity->getLastname().', '.$entity->getFirstname(), $this->get("router")->generate("ContactViewSlug",array('slug'=>$entity->getSlug())));
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
					return $this->render('DellaertDCIMBundle:Contact:edit.html.twig',array('entity'=>$entity));
				}
			}
			$this->get("white_october_breadcrumbs")->addItem("Edit",'');
			return $this->render('DellaertDCIMBundle:Contact:edit.html.twig',array('form'=>$form->createView(),'entity'=>$entity));
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Contacts", $this->get("router")->generate("ContactList"))
			->add("Unkown contact", '');
		return $this->render('DellaertDCIMBundle:Contact:edit.html.twig');
	}
	
	public function deleteAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Contact')->find($id);
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->add("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getCompany()->getSlug())))
				->add($entity->getLastname().', '.$entity->getFirstname(), $this->get("router")->generate("ContactViewSlug",array('slug'=>$entity->getSlug())))
				->add("Delete",'');
			$em = $this->getDoctrine()->getManager();
			$em->remove($entity);
			$em->flush();
			return $this->render('DellaertDCIMBundle:Contact:delete.html.twig',array('entity'=>$entity));
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Contacts", $this->get("router")->generate("ContactList"))
			->add("Unkown contact", '');
		return $this->render('DellaertDCIMBundle:Contact:delete.html.twig');
	}
	
	public function createAddEditForm($entity)
	{
		$fb = $this->createFormBuilder($entity);
		$fb->add('firstname','text',array('max_length'=>255,'required'=>true,'label'=>'First name'));
		$fb->add('lastname','text',array('max_length'=>255,'required'=>true,'label'=>'Last name'));
		$fb->add('company','entity',array(
			'class' => 'Dellaert\\DCIMBundle\\Entity\\Company',
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('c')->orderBy('c.companyName','ASC');
			},
			'property' => 'companyName',
			'required' => true,
			'label' => 'Company'
		));
		$fb->add('street','text',array('max_length'=>255,'required'=>false,'label'=>'Street'));
		$fb->add('streetnumber','text',array('max_length'=>255,'required'=>false,'label'=>'Street number'));
		$fb->add('postalcode','text',array('max_length'=>255,'required'=>false,'label'=>'Postal code'));
		$fb->add('city','text',array('max_length'=>255,'required'=>false,'label'=>'City'));
		$fb->add('country','country',array('preferred_choices'=>array('BE')));
		$fb->add('telephone','text',array('max_length'=>255,'required'=>false,'label'=>'Telephone'));
		$fb->add('email','email',array('max_length'=>255,'required'=>false,'label'=>'E-mail'));
		return $fb->getForm();
	}
}
	
