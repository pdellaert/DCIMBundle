<?php
namespace Dellaert\DCIMBundle\Controller;

use Dellaert\DCIMBundle\Entity\OutgoingInvoiceEntry;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\OutgoingInvoice;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;

class OutgoingInvoiceController extends Controller
{
	public function listAction()
	{
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"));
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:list.html.twig');
	}
	
	public function listDataAction()
	{
		$request = $this->getRequest();
		
		$page = 1;
		if( $request->request->get('page') != null && $request->request->get('page') != '' ) {
			$page = $request->request->get('page');
		}
		
		$sortname = 'invoiceNumber';
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
		
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
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
		$qstring = 'SELECT COUNT(c.id) FROM DellaertDCIMBundle:OutgoingInvoice c';
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
				'cell' => array($entity->getInvoiceNumber(),$entity->getOriginCompany()->getCompanyName(),$entity->getTitle(),$entity->getCategory()->getTitle(),$entity->getProject()->getCompany()->getCompanyName().' - '.$entity->getProject()->getTitle())
			);
		}

		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		
		return $response;
	}
	
	public function viewAction($slug)
	{
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
		$entity = $repository->findOneBySlug($slug);
		
		$this->get("white_october_breadcrumbs")
			->addItem("Home", $this->get("router")->generate("homepage"))
				->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getOriginCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getOriginCompany()->getSlug())))
				->addItem($entity->getInvoiceNumber().' - '.$entity->getTitle(), $this->get("router")->generate("OutgoingInvoiceViewSlug",array('slug'=>$slug)));
		} else {
			$this->get("white_october_breadcrumbs")->addItem("Unkown outgoing invoice", '');
		}
		
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:view.html.twig',array('entity'=>$entity));
	}
	
	public function addAction($id)
	{
		$user = $this->get('security.context')->getToken()->getUser();
		$entity = new OutgoingInvoice();
		$entity->setOriginCompany($user->getCompany());
		$today = new \DateTime();
		$entity->setDate($today);
		$entity->setEndDate($today);
		$due = new \DateTime();
		$due->add(new \DateInterval('P30D'));
		$entity->setDueDate($due);
		$start = new \DateTime();
		$start->sub(new \DateInterval('P1M'));
		$entity->setStartDate($start);
		$request = $this->getRequest();
		$entity->setInvoiceNumber($this->getNextInvoiceNumber($entity));
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		
		if( $id > 0 ) {
			$project = $this->getDoctrine()
				->getRepository('DellaertDCIMBundle:Project')
				->find($id);
			if( $project ) {
				$entity->setProject($project);
				$entity->setVat($project->getVat());
				$this->get('session')->set('return_url',$this->get('router')->generate('ProjectViewSlug',array('slug'=>$project->getSlug())));
				$this->get("white_october_breadcrumbs")
					->addItem($project->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$project->getCompany()->getSlug())))
					->addItem($project->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$project->getSlug())));
			} else {
				$this->get("white_october_breadcrumbs")
					->addItem("Projects", $this->get("router")->generate("ProjectList"))
				->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"));
			}
		} elseif( $request->getMethod() != 'POST' ) {
			$this->get('session')->remove('return_url');
		}
		if( $this->get('session')->get('return_url') == null || $this->get('session')->get('return_url') == '' ) {
			$this->get('session')->set('return_url',$this->get('router')->generate('OutgoingInvoiceList'));
		}
		
		$form = $this->createAddEditForm($entity);
		if( $request->getMethod() == 'POST' ) {
			$form->handleRequest($request);	
			if( $form->isValid() ) {
				$entity->setCreatedBy($user);
				$entity->setEnabled(true);
				$entity->preInsert();
				$entity->setInvoiceNumber($this->getNextInvoiceNumber($entity));
				
				$em = $this->getDoctrine()->getManager();
				$em->persist($entity);
				
				$entries = $this->generateInvoiceEntries($entity);
				foreach( $entries as $entry ) {
					$entry->setOutgoingInvoice($entity);
					$entry->setCreatedBy($user);
					$entry->setEnabled(true);
					$entry->preInsert();
					$em->persist($entry);
				}
				$em->flush();
				
				$this->get("white_october_breadcrumbs")
					->addItem($entity->getInvoiceNumber().' - '.$entity->getTitle(), $this->get("router")->generate("OutgoingInvoiceViewSlug",array('slug'=>$entity->getSlug())))
					->addItem("Save",'');
				return $this->render('DellaertDCIMBundle:OutgoingInvoice:add.html.twig',array('entity'=>$entity));
			}
		}
		$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"))
			->addItem("Add outgoing invoice", '');
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:add.html.twig',array('form'=>$form->createView(),'id'=>$id));
	}
	
	public function editAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice')->find($id);
		$user = $this->get('security.context')->getToken()->getUser();
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
				->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
				->addItem($entity->getInvoiceNumber().' - '.$entity->getTitle(), $this->get("router")->generate("OutgoingInvoiceViewSlug",array('slug'=>$entity->getSlug())));
			$form = $this->createAddEditForm($entity);
			$request = $this->getRequest();
			if( $request->getMethod() == 'POST' ) {
				$form->handleRequest($request);	
				if( $form->isValid() ) {
					
					$em = $this->getDoctrine()->getManager();
					foreach( $entity->getEntries() as $entry ) {
						if( count($entry->getSubentries()) >0  ) {
							foreach( $entry->getSubentries() as $subentry ) {
								$em->remove($subentry);
							}
						}
						$em->remove($entry);
					}	    			
					$entity->preUpdate();
					$em->persist($entity);
					
					$entries = $this->generateInvoiceEntries($entity);
					foreach( $entries as $entry ) {
						$entry->setOutgoingInvoice($entity);
						$entry->setCreatedBy($user);
						$entry->setEnabled(true);
						$entry->preInsert();
						$em->persist($entry);
					}
					$em->flush();
					
					$this->get("white_october_breadcrumbs")->addItem("Save",'');
					return $this->render('DellaertDCIMBundle:OutgoingInvoice:edit.html.twig',array('entity'=>$entity));
				}
			}
			$this->get("white_october_breadcrumbs")->addItem("Edit",'');
			return $this->render('DellaertDCIMBundle:OutgoingInvoice:edit.html.twig',array('form'=>$form->createView(),'entity'=>$entity));
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"))
			->addItem("Unkown outgoing invoice", '');
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:edit.html.twig');
	}
	
	public function deleteAction($id)
	{
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice')->find($id);
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
				->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
				->addItem($entity->getInvoiceNumber().' - '.$entity->getTitle(), $this->get("router")->generate("OutgoingInvoiceViewSlug",array('slug'=>$entity->getSlug())))
				->addItem("Delete",'');
			$this->get('session')->set('return_url',$this->get('router')->generate('ProjectViewSlug', array('slug'=>$entity->getProject()->getSlug())));
			$em = $this->getDoctrine()->getManager();
			foreach( $entity->getEntries() as $entry ) {
				if( count($entry->getSubentries()) >0  ) {
					foreach( $entry->getSubentries() as $subentry ) {
						$em->remove($subentry);
					}
				}
				$em->remove($entry);
			}
			$em->remove($entity);
			$em->flush();
			return $this->render('DellaertDCIMBundle:OutgoingInvoice:delete.html.twig',array('entity'=>$entity));
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"))
			->addItem("Unkown outgoing invoice", '');
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:delete.html.twig');
	}
	
	public function payedAction($id,$status) {
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice')->find($id);
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
				->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
				->addItem($entity->getInvoiceNumber().' - '.$entity->getTitle(), $this->get("router")->generate("OutgoingInvoiceViewSlug",array('slug'=>$entity->getSlug())))
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
			return $this->render('DellaertDCIMBundle:OutgoingInvoice:payed.html.twig',array('entity'=>$entity));
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"))
			->add("Unkown outgoing invoice", '');
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:payed.html.twig');
	}
	
	public function generateAction($id) {
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice')->find($id);
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
				->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
				->addItem($entity->getInvoiceNumber().' - '.$entity->getTitle(), $this->get("router")->generate("OutgoingInvoiceViewSlug",array('slug'=>$entity->getSlug())))
				->addItem("Generate",'');
			
			$base_folder = $this->getRequest()->server->get('DOCUMENT_ROOT').'/../data/outgoinginvoices/base';
			$data_folder = $this->getRequest()->server->get('DOCUMENT_ROOT').'/../data/outgoinginvoices/'.$entity->getId().'-'.$entity->getInvoiceNumber();
			mkdir($data_folder);
			$basecontent = dir($base_folder);
			while( FALSE !== ( $f = $basecontent->read()) ) {
				if( !is_dir($f) ) {
					copy($base_folder.'/'.$f,$data_folder.'/'.$f);
				}
			}
			
			$latexContent = $this->container->get('templating')->render('DellaertDCIMBundle:Tex:invoice.tex.twig',array('entity'=>$entity));
			$latexFile = $data_folder.'/'.$entity->getInvoiceNumber().'_'.$entity->getProject()->getCompany()->getSlug().'.tex';
			file_put_contents($latexFile,$latexContent);
			exec('cd '.$data_folder.'; pdflatex '.$latexFile.'; pdflatex '.$latexFile);
			unlink($data_folder.'/'.$entity->getInvoiceNumber().'_'.$entity->getProject()->getCompany()->getSlug().'.aux');
			unlink($data_folder.'/'.$entity->getInvoiceNumber().'_'.$entity->getProject()->getCompany()->getSlug().'.log');
			
			$entity->setFilelocation($data_folder);
			$entity->setGenerated(true);
			$em = $this->getDoctrine()->getManager();
			$entity->preUpdate();
			$em->persist($entity);
			$em->flush();
			return $this->render('DellaertDCIMBundle:OutgoingInvoice:generate.html.twig',array('entity'=>$entity));
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"))
			->addItem("Unkown outgoing invoice", '');
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:generate.html.twig');
	}
	
	public function downloadAction($id,$type) {
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice')->find($id);
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$data_folder = $entity->getFilelocation();
			$response = new Response(file_get_contents($data_folder.'/'.$entity->getInvoiceNumber().'_'.$entity->getProject()->getCompany()->getSlug().'.'.$type));
			if( $type == 'pdf' ) {
				$response->headers->set('Content-Type', 'application/pdf');
			} elseif( $type == 'tex' ) {
				$response->headers->set('Content-Type', 'application/tex');
			}
			$response->headers->set('Content-Disposition','attachement; filename="'.$entity->getInvoiceNumber().'_'.$entity->getProject()->getCompany()->getSlug().'.'.$type.'"');
			return $response;
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"))
			->addItem("Unkown outgoing invoice", '');
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:download.html.twig');
	}
	
	public function groupEntriesAction($id) {
		$entity = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice')->find($id);
		$user = $this->get('security.context')->getToken()->getUser();
		$this->get("white_october_breadcrumbs")
		->addItem("Home", $this->get("router")->generate("homepage"))
			->addItem("Companies", $this->get("router")->generate("CompanyList"));
		if( $entity ) {
			$this->get("white_october_breadcrumbs")
				->addItem($entity->getProject()->getCompany()->getCompanyName(), $this->get("router")->generate("CompanyViewSlug",array('slug'=>$entity->getProject()->getCompany()->getSlug())))
				->addItem($entity->getProject()->getTitle(), $this->get("router")->generate("ProjectViewSlug",array('slug'=>$entity->getProject()->getSlug())))
				->addItem($entity->getInvoiceNumber().' - '.$entity->getTitle(), $this->get("router")->generate("OutgoingInvoiceViewSlug",array('slug'=>$entity->getSlug())))
				->addItem("Group entries",'');
			
			$entryIds = $this->get('request')->request->get('entryIds');
			$groupName = $this->get('request')->request->get('groupName');
			$em = $this->getDoctrine()->getManager();
			if( count($entryIds) > 0 && !empty($groupName) ) {
				$groupEntry = new OutgoingInvoiceEntry();
				$groupEntry->setOutgoingInvoice($entity);
				$groupEntry->setCreatedBy($user);
				$groupEntry->setEnabled(true);
				$groupEntry->setRate($entity->getProject()->getRate());
				$groupEntry->setTitle($groupName);
				$groupEntry->preInsert();
				$em->persist($groupEntry);
				
				$amount = 0;
				foreach( $entryIds as $entryId ) {
					$entry = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoiceEntry')->find($entryId);
					$entry->setParentEntry($groupEntry);
					$entry->unsetOutgoingInvoice();
					$em->persist($entry);
					$amount += $entry->getAmount();
				}
				
				$groupEntry->setAmount($amount);
				$em->persist($groupEntry);
				$em->flush();
			}
			
			return $this->render('DellaertDCIMBundle:OutgoingInvoice:group.html.twig',array('entity'=>$entity));
		}
	$this->get("white_october_breadcrumbs")
		->addItem("Projects", $this->get("router")->generate("ProjectList"))
		->addItem("Outgoing invoices", $this->get("router")->generate("OutgoingInvoiceList"))
			->addItem("Unkown outgoing invoice", '');
		return $this->render('DellaertDCIMBundle:OutgoingInvoice:group.html.twig');
	}
	
	public function createAddEditForm($entity)
	{
		$fb = $this->createFormBuilder($entity);
		$fb->add('title','text',array('max_length'=>255,'required'=>true,'label'=>'Title'));
		$fb->add('invoiceNumber','text',array('max_length'=>255,'required'=>true,'label'=>'Invoice Number','read_only'=>true));
		$fb->add('originCompany','entity',array(
			'class' => 'Dellaert\\DCIMBundle\\Entity\\Company',
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('c')->orderBy('c.companyName','ASC');
			},
			'property' => 'companyName',
			'required' => true,
			'label' => 'Company'
		));
		$fb->add('project','entity',array(
			'class' => 'Dellaert\\DCIMBundle\\Entity\\Project',
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('c')->orderBy('c.title','ASC');
			},
			'property' => 'title',
			'required' => true,
			'label' => 'Project'
		));
		$fb->add('category','entity',array(
			'class' => 'Dellaert\\DCIMBundle\\Entity\\Category',
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('c')->orderBy('c.title','ASC');
			},
			'property' => 'title',
			'required' => true,
			'label' => 'Category'
		));
		$fb->add('date','date',array('required'=>true,'label'=>'Invoice date'));
		$fb->add('startDate','date',array('required'=>true,'label'=>'Start date'));
		$fb->add('endDate','date',array('required'=>true,'label'=>'End Date'));
		$fb->add('dueDate','date',array('required'=>true,'label'=>'Due Date'));
		$fb->add('vat','percent',array('precision'=>2,'required'=>true,'label'=>'VAT'));
		$fb->add('generated','checkbox', array('required'=>false,'label'=>'Generated'));
		$fb->add('payed','checkbox', array('required'=>false,'label'=>'Payed'));
		return $fb->getForm();
	}
	
	public function getNextInvoiceNumber(OutgoingInvoice $entity) {
		$invoiceDate = $entity->getDate();
		if( empty($invoiceDate) ) {
			$invoiceDate = new \DateTime();
		}
		$invoiceDateTxt = $invoiceDate->format('Ymd');
		$repository = $this->getDoctrine()->getRepository('DellaertDCIMBundle:OutgoingInvoice');
		$qb = $repository->createQueryBuilder('c');
		$qb->add('where',$qb->expr()->like('c.invoiceNumber', $qb->expr()->literal($invoiceDateTxt.'%')));
		$qb->orderBy('c.invoiceNumber','desc');
		$query = $qb->getQuery();
		$results = $query->getResult();
		if( count($results) > 0 ) {
			return $results[0]->getInvoiceNumber()+1;
		} else {
			return $invoiceDateTxt.'01';
		}
	}
	
	public function generateInvoiceEntries(OutgoingInvoice $entity) {
		$entries = array();
		foreach( $entity->getProject()->getIssues() as $issue ) {
			$amount = 0;
			foreach( $issue->getWorkEntries() as $workentry ) {
				if( $workentry->getDate() >= $entity->getStartDate() && $workentry->getDate() <= $entity->getEndDate() ) {
					$amount += $workentry->getAmount();
				}
			}
			if( $amount > 0 ) {
				$entry = new OutgoingInvoiceEntry();
				$entry->setAmount($amount);
				$entry->setRate($issue->getRate());
				$entry->setTitle($issue->getTitle());
				$entries[] = $entry;
			}
		}
		return $entries;
	}
}
