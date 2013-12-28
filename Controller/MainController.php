<?php
namespace Dellaert\DCIMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Dellaert\DCIMBundle\Entity\Company as Company;

class MainController extends Controller
{
    public function indexAction()
    {
    	$this->get("white_october_breadcrumbs")
    		->addItem("Dashboard", $this->get("router")->generate("homepage"));
        return $this->render('DellaertDCIMBundle:Main:dashboard.html.twig');
    }
}
