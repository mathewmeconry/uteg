<?php 

namespace uteg\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ClubsController extends Controller
{
	/**
	 * @Route("/clubs", name="clubs")
	 */
	public function clubsAction()
	{
		return $this->render('clubs.html.twig');
	}
}