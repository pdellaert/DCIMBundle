<?php
namespace Dellaert\DCIMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\Category;
use Dellaert\DCIMBundle\Entity\RecursiveCategoryIterator;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use \RecursiveIteratorIterator;

class CategoryController extends Controller
{
    public function listAction()
    {
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Categories", $this->get("router")->generate("CategoryList"));
        return $this->render('DellaertDCIMBundle:Category:list.html.twig');
    }
    
    public function listDataAction()
    {
    	$request = $this->getRequest();
    	
    	$page = 1;
    	if( $request->request->get('page') != null && $request->request->get('page') != '' ) {
    		$page = $request->request->get('page');
    	}
    	
    	$sortname = 'title';
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
    	
    	$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Category');
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
		$qstring = 'SELECT COUNT(c.id) FROM DellaertDCIMBundle:Category c';
    	if( $searchquery != '' && $searchtype != '' ) {
    		$qstring .= ' where '.$qb->expr()->like('c.'.$searchtype, $qb->expr()->literal('%'.$searchquery.'%'));
    	}
		$query = $em->createQuery($qstring);
		$total = $query->getSingleScalarResult();
		
		$data['page'] = $page;
		$data['total'] = $total;
		$data['rows'] = array();
		foreach($results as $entity) {
            $parent = $entity->getParent();
            $parentTitle = 'None';
            if( !empty($parent) ) {
                $parentTitle = $entity->getParent()->getTitle();
            }
			$data['rows'][] = array(
				'id' => $entity->getSlug(),
				'cell' => array($entity->getTitle(),$parentTitle)
			);
		}

    	
    	$response = new Response(json_encode($data));
    	$response->headers->set('Content-Type', 'application/json');
    	
        return $response;
    }
    
    public function viewAction($slug)
    {
    	$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Category');
    	$entity = $repository->findOneBySlug($slug);
    	
	   	$this->get("white_october_breadcrumbs")
	   		->addItem("Home", $this->get("router")->generate("homepage"))
    			->addItem("Categories", $this->get("router")->generate("CategoryList"));
    	if( $entity ) {
	    	$this->get("white_october_breadcrumbs")->addItem($entity->getTitle(), $this->get("router")->generate("CategoryViewSlug",array('slug'=>$slug)));
    	} else {
	    	$this->get("white_october_breadcrumbs")->addItem("Unkown category", '');
    	}
    	
        return $this->render('DellaertDCIMBundle:Category:view.html.twig',array('entity'=>$entity));
    }
    
    public function addAction()
    {
    	$entity = new Category();
    	$form = $this->createAddEditForm($entity);
    	$request = $this->getRequest();
    	
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Categories", $this->get("router")->generate("CategoryList"));
    	
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
    				->addItem($entity->getTitle(), $this->get("router")->generate("CategoryViewSlug",array('slug'=>$entity->getSlug())))
    				->addItem("Save",'');
    			return $this->render('DellaertDCIMBundle:Category:add.html.twig',array('entity'=>$entity));
    		}
    	}
    	$this->get("white_october_breadcrumbs")->addItem("Add Category", '');
        return $this->render('DellaertDCIMBundle:Category:add.html.twig',array('form'=>$form->createView()));
    }
    
    public function editAction($id)
    {
    	$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Category')->find($id);
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Categories", $this->get("router")->generate("CategoryList"));
    	if( $entity ) {
    		$this->get("white_october_breadcrumbs")->addItem($entity->getTitle(), $this->get("router")->generate("CategoryViewSlug",array('slug'=>$entity->getSlug())));
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
	    			return $this->render('DellaertDCIMBundle:Category:edit.html.twig',array('entity'=>$entity));
	    		}
	    	}
	    	$this->get("white_october_breadcrumbs")->addItem("Edit",'');
	        return $this->render('DellaertDCIMBundle:Category:edit.html.twig',array('form'=>$form->createView(),'entity'=>$entity));
    	}
    	$this->get("white_october_breadcrumbs")->addItem("Unkown category", '');
    	return $this->render('DellaertDCIMBundle:Category:edit.html.twig');
    }
    
    public function deleteAction($id)
    {
    	$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:Category')->find($id);
    	$this->get("white_october_breadcrumbs")
    		->addItem("Home", $this->get("router")->generate("homepage"))
    		->addItem("Categories", $this->get("router")->generate("CategoryList"));
    	if( $entity ) {
    		$this->get("white_october_breadcrumbs")
    			->addItem($entity->getTitle(), $this->get("router")->generate("CategoryViewSlug",array('slug'=>$entity->getSlug())))
    			->addItem("Delete",'');
    		$em = $this->getDoctrine()->getManager();
    		$em->remove($entity);
    		$em->flush();
    		return $this->render('DellaertDCIMBundle:Category:delete.html.twig',array('entity'=>$entity));
    	}
    	$this->get("white_october_breadcrumbs")->addItem("Unkown Category", '');
    	return $this->render('DellaertDCIMBundle:Category:delete.html.twig');
    }
    
    public function createAddEditForm($entity)
    {
    	$fb = $this->createFormBuilder($entity);
    	$fb->add('title','text',array('max_length'=>255,'required'=>true,'label'=>'Title'));
        $fb->add('parent','choice',array(
            'choices' => $this->getCategoryLevelList(),
            'empty_value' => 'None',
            'required' => false,
            'label' => 'Parent category'
            ));
    	return $fb->getForm();
    }

    public function getCategoryLevelList($catId=0)
    {
        $em = $this->getDoctrine()->getManager();
        $root_categories = $em->getRepository('DellaertDCIMBundle:Category')->findBy(array('parent' => null));

        $collection = new ArrayCollection($root_categories);
        $category_iterator = new RecursiveCategoryIterator($collection);
        $recursive_iterator = new RecursiveIteratorIterator($category_iterator, RecursiveIteratorIterator::SELF_FIRST);

        $list = array();

        foreach ($recursive_iterator as $index => $child_category)
        {
            $list[$child_category->getId()] = str_repeat('-', $recursive_iterator->getDepth()) . $child_category->getTitle();
        }

        return $list;
    }
}
