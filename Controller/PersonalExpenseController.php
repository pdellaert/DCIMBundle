<?php
namespace Dellaert\DCIMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\PersonalExpense;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;

class PersonalExpenseController extends Controller
{
	public function listAction()
	{
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Personal expense", $this->get("router")->generate("PersonalExpenseList"));
		return $this->render('DellaertDCIMBundle:PersonalExpense:list.html.twig');
	}
	
	public function listDataAction()
	{
		$request = $this->getRequest();
		
		$page = 1;
		if( $request->request->get('page') != null && $request->request->get('page') != '' ) {
			$page = $request->request->get('page');
		}
		
		$sortname = 'expenseNumber';
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
		
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:PersonalExpense');
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
		$qstring = 'SELECT COUNT(c.id) FROM DellaertDCIMBundle:PersonalExpense c';
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
				'cell' => array($entity->getExpenseNumber(),$entity->getTitle(),$entity->getCategory()->getTitle(),$entity->getDate()->format('Y-m-d'))
			);
		}

		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	public function viewAction($slug)
	{
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:PersonalExpense');
		$entity = $repository->findOneBySlug($slug);
		
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getExpenseNumber().' - '.$entity->getTitle(), $this->get("router")->generate("PersonalExpenseViewSlug",array('slug'=>$slug)));
		} else {
			$this->get("white_october_breadcrumbs")->addItem("Unkown personal expense", '');
		}
		
		return $this->render('DellaertDCIMBundle:PersonalExpense:view.html.twig',array('entity'=>$entity));
	}
	
	public function addAction()
	{
		$user = $this->get('security.context')->getToken()->getUser();
		$entity = new PersonalExpense();
		$today = new \DateTime();
		$entity->setDate($today);
		$due = new \DateTime();
		$due->add(new \DateInterval('P30D'));
		$entity->setDueDate($due);
		$request = $this->getRequest();
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"));
		
		$form = $this->createAddEditForm($entity);
		if( $request->getMethod() == 'POST' ) {
			$form->handleRequest($request);	
			if( $form->isValid() ) {
				$entity->setCreatedBy($user);
				$entity->setEnabled(true);
				$entity->preInsert();
				
				$file = $entity->getFile();
				$fileDir = $entity->getFileDir();
				
				$em = $this->getDoctrine()->getManager();
				$em->persist($entity);
				$em->flush();
				
				if (null !== $file) {
					if( !is_dir($fileDir) ) {
						mkdir($fileDir);
					}
					$file->move($fileDir, $file->getClientOriginalName());
					unset($file);
				}
				
				$this->get("white_october_breadcrumbs")
					->addItem($entity->getExpenseNumber().' - '.$entity->getTitle(), $this->get("router")->generate("PersonalExpenseViewSlug",array('slug'=>$entity->getSlug())))
					->addItem("Save",'');
				return $this->render('DellaertDCIMBundle:PersonalExpense:add.html.twig',array('entity'=>$entity));
			}
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Personal expense", $this->get("router")->generate("PersonalExpenseList"))
			->addItem("Add personal expense", '');
		return $this->render('DellaertDCIMBundle:PersonalExpense:add.html.twig',array('form'=>$form->createView()));
	}
	
	public function editAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:PersonalExpense')->find($id);
		$user = $this->get('security.context')->getToken()->getUser();
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getExpenseNumber().' - '.$entity->getTitle(), $this->get("router")->generate("PersonalExpenseViewSlug",array('slug'=>$entity->getSlug())));
			$form = $this->createAddEditForm($entity);
			$request = $this->getRequest();
			if( $request->getMethod() == 'POST' ) {
				$form->handleRequest($request);	
				if( $form->isValid() ) {
					$bcentity = $entity;
					
					$em = $this->getDoctrine()->getManager();    			
					$entity->preUpdate();
				
					$file = $entity->getFile();
					$fileDir = $entity->getFileDir();
				
					$em->persist($entity);
					$em->flush();
				
					if (null !== $file) {
						if( !is_dir($fileDir) ) {
							mkdir($fileDir);
						}
						$file->move($fileDir, $file->getClientOriginalName());
						unset($file);
					}
					
					$this->get("white_october_breadcrumbs")->addItem("Save",'');
					return $this->render('DellaertDCIMBundle:PersonalExpense:edit.html.twig',array('entity'=>$entity));
				}
			}
			$this->get("white_october_breadcrumbs")->addItem("Edit",'');
			return $this->render('DellaertDCIMBundle:PersonalExpense:edit.html.twig',array('form'=>$form->createView(),'entity'=>$entity));
		}
		$this->get("white_october_breadcrumbs")
			->addItem("Personal expense", $this->get("router")->generate("PersonalExpenseList"))
			->add("Unkown personal expense", '');
		return $this->render('DellaertDCIMBundle:PersonalExpense:edit.html.twig');
	}
	
	public function deleteAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:PersonalExpense')->find($id);
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->add($entity->getExpenseNumber().' - '.$entity->getTitle(), $this->get("router")->generate("PersonalExpenseViewSlug",array('slug'=>$entity->getSlug())))
				->add("Delete",'');
			$em = $this->getDoctrine()->getManager();
			$em->remove($entity);
			$em->flush();
			$entity->postRemove();
			return $this->render('DellaertDCIMBundle:PersonalExpense:delete.html.twig',array('entity'=>$entity));
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Personal expense", $this->get("router")->generate("PersonalExpenseList"))
			->addItem("Unkown personal expense", '');
		return $this->render('DellaertDCIMBundle:PersonalExpense:delete.html.twig');
	}
	
	public function payedAction($id,$status) {
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:PersonalExpense')->find($id);
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getExpenseNumber().' - '.$entity->getTitle(), $this->get("router")->generate("PersonalExpenseViewSlug",array('slug'=>$entity->getSlug())))
				->addItem("Set $status",'');
			if( $status == 'payed' ) {
				$entity->setPayed(true);
			} elseif( $status == 'unpayed' ) {
				$entity->setPayed(false);
			}
			$em = $this->getDoctrine()->getManager();
			$entity->preUpdate();
			$em->persist($entity);
			$em->flush();
			return $this->render('DellaertDCIMBundle:PersonalExpense:payed.html.twig',array('entity'=>$entity));
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Personal expense", $this->get("router")->generate("PersonalExpenseList"))
			->addItem("Unkown personal expense", '');
		return $this->render('DellaertDCIMBundle:PersonalExpense:payed.html.twig');
	}
	
	public function downloadAction($id) {
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:PersonalExpense')->find($id);
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$fileLocation= $entity->getAbsolutePath();
			$response = new Response(file_get_contents($fileLocation));
			$response->headers->set('Content-Type', 'application/pdf');
			$response->headers->set('Content-Disposition','attachement; filename="'.$entity->getFilePath().'"');
			return $response;
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Personal expense", $this->get("router")->generate("PersonalExpenseList"))
			->addItem("Unkown personal expense", '');
		return $this->render('DellaertDCIMBundle:PersonalExpense:download.html.twig');
	}
	
	public function createAddEditForm($entity)
	{
		$fb = $this->createFormBuilder($entity);
		$fb->add('title','text',array('max_length'=>255,'required'=>true,'label'=>'Title'));
		$fb->add('expenseNumber','text',array('max_length'=>255,'required'=>true,'label'=>'Expense number'));
		$fb->add('category','entity',array(
			'class' => 'Dellaert\\DCIMBundle\\Entity\\Category',
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('c')->orderBy('c.title','ASC');
			},
			'property' => 'title',
			'required' => true,
			'label' => 'Category'
		));
		$fb->add('date','date',array('required'=>true,'label'=>'Expense date'));
		$fb->add('dueDate','date',array('required'=>true,'label'=>'Due Date'));
		$fb->add('amount','number',array('precision'=>2,'required'=>true,'label'=>'Amount'));
		$fb->add('file','file', array('required'=>false,'label'=>'Scan'));
		$fb->add('payed','checkbox', array('required'=>false,'label'=>'Payed'));
		return $fb->getForm();
	}
}
