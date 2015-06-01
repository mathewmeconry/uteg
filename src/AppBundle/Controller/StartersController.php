<?php 

namespace AppBundle\Controller;

use AppBundle\Form\Type\StarterType;
use AppBundle\Form\Type\S2cType;
use AppBundle\Entity\Starters2Competitions;
use AppBundle\Entity\Starter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;


class StartersController extends Controller 
{
	/**
	 * @Route("/starters/{sex}", name="starters", defaults={"sex": "male"}, requirements={"sex": "male|female"})
	 * @Method("GET")
	 */
	public function startersGetAction($sex, Request $request)
	{
        $this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');

		$requestUri = explode("/", $request->getRequestUri());
		 
		if(end($requestUri) !== 'male' && end($requestUri) !== 'female') {
			return $this->redirect($request->getRequestUri()."/male", 301);
		} else {
            $sexshort = (end($requestUri) == 'female') ? 'f' : 'm';
            $sextrans = ($sex == 'female') ? 'starters.female' : 'starters.male';

			return $this->render('starters.html.twig', array(
                "sex" => $sexshort,
                "sextrans" => $sextrans,
                "comp" => $this->getDoctrine()->getEntityManager()->find('AppBundle:Competition', $request->getSession()->get('comp'))
			));
		}
	}
	
	/**
	 * @Route("/starters/{sex}/{cat}", name="starterspost", defaults={"sex": "male", "cat": "0"}, requirements={"sex": "male|female", "cat": "\d+"})
	 * @Method("POST")
	 */
	public function startersPostAction($sex, $cat, Request $request) {
		$this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');
		setlocale(LC_TIME, $request->getLocale());
		$dateFormatter = $this->get('bcc_extra_tools.date_formatter');
		
		
		if($sex !== 'male' && $sex !== 'female') {
			return $this->redirect($request->getRequestUri()."/male", 301);
		} else {
			$comp = $this->getDoctrine()->getEntityManager()->find('AppBundle:Competition', $request->getSession()->get('comp'));
			$s2cs = ($cat == 0) ? $comp->getS2csBySex(substr($sex,0,1)) : $comp->getS2csBySexCat(substr($sex,0,1), $cat);
			$starters = array();

			foreach($s2cs as $s2c) {
				$starters["data"][] = array("id" => $s2c->getId(),
					"firstname" => $s2c->getStarter()->getFirstname(),
					"lastname" => $s2c->getStarter()->getLastname(),
					"birthyear" => $s2c->getStarter()->getBirthyear(),
					"club" => $s2c->getClub()->getName(),
					"category" => ($s2c->getCategory()->getNumber() == 8) ? ($s2c->getStarter()->getSex() == 'f') ? $s2c->getCategory()->getName()."D" : $s2c->getCategory()->getName()."H" : $s2c->getCategory()->getName(),
					"present" => $s2c->getPresent(),
					"medicalcert" => $s2c->getMedicalcert()
				);
			}
		
			$response = new Response(json_encode($starters));
			$response->headers->set('Content-Type', 'application/json');
			
			return $response;
		}
	}
	
	/**
	 * @Route("/starter/{id}/{name}", name="starter", defaults={"name": ""}, requirements={"id": "\d+"})
	 */
	public function starterAction($id, $name, Request $request)
	{
		if($name === "") {
			return $this->redirect($request->getRequestUri()."/".$this->getName($id), 301);
		} else {
			return $this->render('starter.html.twig', array(
                    "title" => $name,
					"path" => array($request->getSession()->get('comp')->getName(), 'starter.path', $name)
			));
		}
	}
	
	/**
	 * @Route("/starter/add", name="starterAdd")
	 */
	public function starterAddAction(Request $request) {
		$this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');
		
		$form = $this->createForm(new S2cType(false));
		
		$form->handleRequest($request);
		if ($form->isValid()) {
			$em = $this->getDoctrine()->getEntityManager();
			$competition = $em->getRepository('AppBundle:Competition')->find($request->getSession()->get('comp'));
			$formdata = $form->getData();
			
			$starter = $em->getRepository('AppBundle:Starter')->findOneBy(array("firstname" => $formdata['firstname'], "lastname" => $formdata['lastname'], "birthyear" => $formdata['birthyear'], "sex" => $formdata['sex']));
			if(!$starter) {
				$starter = $em->getRepository('AppBundle:Starter')->findOneBy(array("lastname" => $formdata['firstname'], "firstname" => $formdata['lastname'], "birthyear" => $formdata['birthyear'], "sex" => $formdata['sex']));
				if(!$starter) {
					$starter = new Starter();
					$starter->setFirstname($formdata['firstname']);
					$starter->setLastname($formdata['lastname']);
					$starter->setBirthyear($formdata['birthyear']);
					$starter->setSex($formdata['sex']);
					$s2c = false;
				} else {
					$s2c = $em->getRepository('AppBundle:Starters2Competitions')->findOneBy(array("starter" => $starter, "competition" => $competition));
				}
			} else {
				$s2c = $em->getRepository('AppBundle:Starters2Competitions')->findOneBy(array("starter" => $starter, "competition" => $competition));
			}
			
			if(!$s2c) {
				$s2c = new Starters2Competitions();
				$s2c->setStarter($starter);
				$s2c->setCompetition($competition);
				
				$starter->addS2c($s2c);
				$competition->addS2c($s2c);
			}
			
			$s2c->setClub($em->getRepository('AppBundle:Club')->find($formdata['club']));
			$s2c->setCategory($em->getRepository('AppBundle:Category')->find($formdata['category']));
			$s2c->setPresent($formdata['present']);
			$s2c->setMedicalcert($formdata['medicalcert']);
			
			$em->persist($starter);
			$em->persist($s2c);
			$em->persist($competition);
			$em->flush();
			
			$this->get('session')->getFlashBag()->add('success', 'competitionlist.addcomp.success');
		
			return new Response('true');
		}
		
		return $this->render('form/StarterEdit.html.twig',
				array('form' => $form->createView(),
						'target' => 'starterAdd'
				)
		);
	}
	
	/**
	 * @Route("/starter/import", name="starterImport")
     * Increase max_input_var in php.ini from 1000 to 6000 to import max. 1000 starters at once
	 */
	public function starterImportAction() {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $em = $this->getDoctrine()->getEntityManager();

		try {
		    $inputFileType = \PHPExcel_IOFactory::identify($this->get('kernel')->getRootDir() . '/../web/test_data.xlsx');
		    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
		    $objPHPExcel = $objReader->load($this->get('kernel')->getRootDir() . '/../web/test_data.xlsx');
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($this->get('kernel')->getRootDir() . '/../web/test_data.xlsx',PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		
		//  Get worksheet dimensions
		$sheetData = $objPHPExcel->getActiveSheet();
		$highestRow = $sheetData->getHighestRow(); 
		$highestColumn = $sheetData->getHighestColumn(); 
		$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn); 
		
		if($highestColumnIndex == 6) {
			for ($row = 1; $row <= $highestRow; ++$row) {
				($sheetData->getCellByColumnAndRow(0, $row)->getValue() == 'Firstname' ||
                    $sheetData->getCellByColumnAndRow(1, $row)->getValue() == 'Lastname' ||
                    $sheetData->getCellByColumnAndRow(2, $row)->getValue() == 'Brith year' ||
                    $sheetData->getCellByColumnAndRow(3, $row)->getValue() == 'Sex' ||
                    $sheetData->getCellByColumnAndRow(4, $row)->getValue() == 'Category' ||
                    $sheetData->getCellByColumnAndRow(5, $row)->getValue() == 'Club') ? $row++ : $row;

                ($sheetData->getCellByColumnAndRow(0, $row)->getValue() == '' ||
                    $sheetData->getCellByColumnAndRow(1, $row)->getValue() == '' ||
                    $sheetData->getCellByColumnAndRow(2, $row)->getValue() == '' ||
                    $sheetData->getCellByColumnAndRow(3, $row)->getValue() == '' ||
                    $sheetData->getCellByColumnAndRow(4, $row)->getValue() == '' ||
                    $sheetData->getCellByColumnAndRow(5, $row)->getValue() == '') ? $row++ : $row;

				if($sheetData->getCellByColumnAndRow(0, $row)->getValue() !== NULL) {
                    $sex = ($sheetData->getCellByColumnAndRow(3, $row)->getValue() == 'Female') ? 'f' : 'm';

                    $category = $em->getRepository('AppBundle:Category')->findOneBy(array("name" => $sheetData->getCellByColumnAndRow(4, $row)->getValue()));

                    $club = $em->getRepository('AppBundle:Club')->findOneBy(array("name" => $sheetData->getCellByColumnAndRow(5, $row)->getValue()));
                    if(!$club) {
                        $club = new Club();
                        $club->setName($sheetData->getCellByColumnAndRow(5, $row)->getValue());
                        $em->persist($club);
                        $em->flush();
                    }

			    	$starters[] = array("firstname" => $sheetData->getCellByColumnAndRow(0, $row)->getValue(),
				    	'lastname' => $sheetData->getCellByColumnAndRow(1, $row)->getValue(),
				    	'birthyear' => $sheetData->getCellByColumnAndRow(2, $row)->getValue(),
				    	'sex' => $sex,
				    	'category' => $category,
				    	'club' => $club
			    	);
				}
			}
		} else {
			throw new HttpException(406, "Invalid column count. Counted: ".$highestColumnIndex);
		}
		
		if(isset($starters)) {
            $clubs = $em->getRepository('AppBundle:Club')->findBy(array(), array('name'=>'asc'));
            $categories = $em->getRepository('AppBundle:Category')->findBy(array(), array('number'=>'asc'));

			return $this->render('form/StarterImport.html.twig',
                array('clubs' => $clubs,
                    'categories' => $categories,
                    'starters' => $starters
                )
            );
		} else {
			throw new HttpException(406, "No Data passed in Excel");
		}
		
		return new Response('true');
	}

    /**
     * @Route("/starter/edit/{id}", name="starterEdit", defaults={"id": ""}, requirements={"id": "\d+"})
     */
    public function starterEditAction($id, Request $request) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $s2c = $this->getDoctrine()->getEntityManager()->find('AppBundle:Starters2Competitions',$id);

        $form = $this->createForm(new S2cType(), $s2c);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $s2c = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();

            $em->persist($s2c);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'competitionlist.addcomp.success');

            return new Response('true');
        }

        return $this->render('form/StarterEdit.html.twig',
            array('form' => $form->createView(),
						'target' => 'starterEdit'
            )
        );
    }

    /**
     * @Route("/starter/remove", name="starterRemove")
     */
    public function starterRemoveAction(Request $request) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $em = $this->getDoctrine()->getEntityManager();
        $s2c = $em->find('AppBundle:Starters2Competitions', $_POST['id']);
        $competition = $em->find('AppBundle:Competition', $request->getSession()->get('comp'));

        $competition->removeS2c($s2c);
        $em->persist($competition);
        $em->flush();
        return new Response('true');
    }
	
	private function getName($id) {
		return "Mathias Scherer";
	}
}