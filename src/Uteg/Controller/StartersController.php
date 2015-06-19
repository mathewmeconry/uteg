<?php 

namespace uteg\Controller;

use uteg\Form\Type\StarterType;
use uteg\Form\Type\S2cType;
use uteg\Entity\Starters2Competitions;
use uteg\Entity\Starter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


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
                "comp" => $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $request->getSession()->get('comp'))
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
			$comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $request->getSession()->get('comp'));
			$s2cs = ($cat == 0) ? $comp->getS2csBySex($sex) : $comp->getS2csBySexCat($sex, $cat);
			$starters = array();

			foreach($s2cs as $s2c) {
				$starters["data"][] = array("id" => $s2c->getId(),
					"firstname" => $s2c->getStarter()->getFirstname(),
					"lastname" => $s2c->getStarter()->getLastname(),
					"birthyear" => $s2c->getStarter()->getBirthyear(),
					"club" => $s2c->getClub()->getName(),
					"category" => ($s2c->getCategory()->getNumber() == 8) ? ($s2c->getStarter()->getSex() == 'female') ? $s2c->getCategory()->getName()."D" : $s2c->getCategory()->getName()."H" : $s2c->getCategory()->getName(),
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
			$competition = $em->getRepository('uteg:Competition')->find($request->getSession()->get('comp'));
			$formdata = $form->getData();
			
			$starter = $em->getRepository('uteg:Starter')->findOneBy(array("firstname" => $formdata['firstname'], "lastname" => $formdata['lastname'], "birthyear" => $formdata['birthyear'], "sex" => $formdata['sex']));
			if(!$starter) {
				$starter = $em->getRepository('uteg:Starter')->findOneBy(array("lastname" => $formdata['firstname'], "firstname" => $formdata['lastname'], "birthyear" => $formdata['birthyear'], "sex" => $formdata['sex']));
				if(!$starter) {
					$starter = new Starter();
					$starter->setFirstname($formdata['firstname']);
					$starter->setLastname($formdata['lastname']);
					$starter->setBirthyear($formdata['birthyear']);
					$starter->setSex($formdata['sex']);
					$s2c = false;
				} else {
					$s2c = $em->getRepository('uteg:Starters2Competitions')->findOneBy(array("starter" => $starter, "competition" => $competition));
				}
			} else {
				$s2c = $em->getRepository('uteg:Starters2Competitions')->findOneBy(array("starter" => $starter, "competition" => $competition));
			}
			
			if(!$s2c) {
				$s2c = new Starters2Competitions();
				$s2c->setStarter($starter);
				$s2c->setCompetition($competition);
				
				$starter->addS2c($s2c);
				$competition->addS2c($s2c);
			}
			
			$s2c->setClub($em->getRepository('uteg:Club')->find($formdata['club']));
			$s2c->setCategory($em->getRepository('uteg:Category')->find($formdata['category']));
			$s2c->setPresent($formdata['present']);
			$s2c->setMedicalcert($formdata['medicalcert']);
			
			$validator = $this->get('validator');
			$errors = $validator->validate($starter);
			if(count($errors) <= 0) {
				$em->persist($starter);
				$em->persist($s2c);
				$em->persist($competition);
				$em->flush();
				
				$this->get('session')->getFlashBag()->add('success', 'competitionlist.addcomp.success');
			
				return new Response('true');
			} else {
				foreach ($errors as $error) {
					$errorMessages[$error->getPropertyPath()] = $error->getMessage();
				}
			}
		}
		
		return $this->render('form/StarterEdit.html.twig',
				array('form' => $form->createView(),
						'error' => (isset($errorMessages)) ? $errorMessages : '',
						'target' => 'starterAdd'
				)
		);
	}

    /**
     * @Route("/starter/add/massive", name="starterAddMassive")
     * @Method("POST")
     */
    public function starterAddMassiveAction(Request $request) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $em = $this->getDoctrine()->getEntityManager();
        $starters = $this->get('request')->request->get('data');
        $competition = $em->getRepository('uteg:Competition')->find($request->getSession()->get('comp'));

        foreach($starters as $starterPost) {
            $starter = $em->getRepository('uteg:Starter')->findOneBy(array("firstname" => $starterPost['firstname'], "lastname" => $starterPost['lastname'], "birthyear" => $starterPost['birthyear'], "sex" => $starterPost['sex']));
            if(!$starter) {
                $starter = $em->getRepository('uteg:Starter')->findOneBy(array("lastname" => $starterPost['firstname'], "firstname" => $starterPost['lastname'], "birthyear" => $starterPost['birthyear'], "sex" => $starterPost['sex']));
                if(!$starter) {
                    $starter = new Starter();
                    $starter->setFirstname($starterPost['firstname']);
                    $starter->setLastname($starterPost['lastname']);
                    $starter->setBirthyear($starterPost['birthyear']);
                    $starter->setSex($starterPost['sex']);
                    $s2c = false;
                } else {
                    $s2c = $em->getRepository('uteg:Starters2Competitions')->findOneBy(array("starter" => $starter, "competition" => $competition));
                }
            } else {
                $s2c = $em->getRepository('uteg:Starters2Competitions')->findOneBy(array("starter" => $starter, "competition" => $competition));
            }

            if(!$s2c) {
                $s2c = new Starters2Competitions();
                $s2c->setStarter($starter);
                $s2c->setCompetition($competition);

                $starter->addS2c($s2c);
                $competition->addS2c($s2c);
            }

            $club = $em->getRepository('uteg:Club')->find((array_key_exists('club', $starterPost)) ? $starterPost['club'] : 0);
            if($club !== 0 && $club) {
                $s2c->setClub($club);
            } else {
                $errorMessagesRaw['club'] = 'starter.error.club';
            }

            $category = $em->getRepository('uteg:Category')->find((array_key_exists('category', $starterPost)) ? $starterPost['category'] : 0);
            if($category !== 0 && $category) {
                $s2c->setCategory($category);
            } else {
                $errorMessagesRaw['category'] = 'starter.error.category';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($starter);
            if(count($errors) <= 0 && $club && $category && $club !== 0 && $category !== 0) {
                $em->persist($starter);
                $em->persist($s2c);
                $em->flush($starter);
                $em->flush($s2c);
            } else {
                $array = $starter->__toArray();
                $array['club']['id'] = (array_key_exists('club', $starterPost)) ? $starterPost['club'] : 0;
                $array['category']['id'] = (array_key_exists('category', $starterPost)) ? $starterPost['category'] : 0;
                foreach ($errors as $error) {
                    $errorMessagesRaw[$error->getPropertyPath()] = $error->getMessage();
                }
                $fails[] = $array;
                $errorMessages[] = $errorMessagesRaw;
            }

            unset($starter);
            unset($s2c);
            unset($errorMessagesRaw);
        }

        if(isset($fails)) {
            $clubs = $em->getRepository('uteg:Club')->findBy(array(), array('name'=>'asc'));
            $categories = $em->getRepository('uteg:Category')->findBy(array(), array('number'=>'asc'));

            return $this->render('form/StarterImport.html.twig',
                array('clubs' => $clubs,
                    'categories' => $categories,
                    'starters' => $fails,
                    'errors' => $errorMessages
                )
            );
        } else {
            $this->get('session')->getFlashBag()->add('success', 'competitionlist.addcomp.success');
            return $this->redirectToRoute('starters');
        }
    }

	/**
	 * @Route("/starter/import", name="starterImport")
     * Increase max_input_vars in php.ini from 1000 to 6000 to import max. 1000 starters at once
	 */
	public function starterImportAction(Request $request) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');
		
        $files = $request->getSession()->get('import');
        $request->getSession()->set('import', null);
        $errors = array();

        if($files) {
			$em = $this->getDoctrine()->getEntityManager();
            $fs = new Filesystem();
            $index = -1;

			foreach($files as $file) {
				try {
				    $inputFileType = \PHPExcel_IOFactory::identify($file);
				    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
				    $objPHPExcel = $objReader->load($file);
				} catch(Exception $e) {
				    throw new HttpException(500, 'Error loading file "'.pathinfo($file,PATHINFO_BASENAME).'": '.$e->getMessage());
				}
				
				//  Get worksheet dimensions
				$sheetData = $objPHPExcel->getActiveSheet();
				$highestRow = $sheetData->getHighestRow(); 
				$highestColumn = $sheetData->getHighestColumn(); 
				$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn); 
                ($fs->exists($file)) ? $fs->remove($file) : '';

				if($highestColumnIndex == 6) {

					for ($row = 1; $row <= $highestRow; ++$row) {
                        ++$index;

                        ($sheetData->getCellByColumnAndRow(0, $row)->getValue() == 'Firstname' ||
                            $sheetData->getCellByColumnAndRow(1, $row)->getValue() == 'Lastname' ||
                            $sheetData->getCellByColumnAndRow(2, $row)->getValue() == 'Brith year' ||
                            $sheetData->getCellByColumnAndRow(3, $row)->getValue() == 'Sex' ||
                            $sheetData->getCellByColumnAndRow(4, $row)->getValue() == 'Category' ||
                            $sheetData->getCellByColumnAndRow(5, $row)->getValue() == 'Club') ? $row++ : $row;

                        ($sheetData->getCellByColumnAndRow(0, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(1, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(2, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(3, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(4, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(5, $row)->getValue() == '') ? $row++ : $row;

                        $firstname = $sheetData->getCellByColumnAndRow(0, $row)->getValue();
                        if (strlen($firstname) <= 0) {
                            $errors[$index]['firstname'] = 'starter.error.firstname';
                        }

                        $lastname = $sheetData->getCellByColumnAndRow(1, $row)->getValue();
                        if (strlen($lastname) <= 0) {
                            $errors[$index]['lastname'] = 'starter.error.lastname';
                        }

                        $birthyear = $sheetData->getCellByColumnAndRow(2, $row)->getValue();
                        if (strlen($birthyear) < 4) {
                            $errors[$index]['birthyear'] = 'starter.error.birthyearMin';
                        }

                        if (strlen($birthyear) > 4) {
                            $errors[$index]['birthyear'] = 'starter.error.birthyearMax';
                        }

                        $sex = strtolower($sheetData->getCellByColumnAndRow(3, $row)->getValue());
                        if ($sex !== 'male' && $sex !== 'female') {
                            $errors[$index]['sex'] = 'starter.error.sex';
                        }

                        $clubname = $sheetData->getCellByColumnAndRow(5, $row)->getValue();
                        if ($clubname) {
                            $club = $em->getRepository('uteg:Club')->findOneBy(array("name" => $clubname));
                            if (!$club) {
                                $club = new Club();
                                $club->setName($sheetData->getCellByColumnAndRow(5, $row)->getValue());
                                $em->persist($club);
                                $em->flush();
                            }
                        } else {
                            $club['id'] = 0;
                            $errors[$index]['club'] = 'starter.error.club';
                        }

                        $category = $em->getRepository('uteg:Category')->findOneBy(array("name" => $sheetData->getCellByColumnAndRow(4, $row)->getValue()));
                        if (!$category) {
                            $category['id'] = 0;
                            $errors[$index]['category'] = 'starter.error.category';
                        }

                        $starters[] = array("firstname" => $firstname,
                            'lastname' => $lastname,
                            'birthyear' => $birthyear,
                            'sex' => $sex,
                            'category' => $category,
                            'club' => $club
                        );

                        unset($firstname);
                        unset($lastname);
                        unset($birthyear);
                        unset($sex);
                        unset($club);
                        unset($clubname);
                        unset($category);
					}
				} else {
					return $this->render('form/StarterImportUpload.html.twig',
							array('error' => 'starters.import.error.columnCount'));
				}
        	}
			
			if(isset($starters)) {
	            $clubs = $em->getRepository('uteg:Club')->findBy(array(), array('name'=>'asc'));
	            $categories = $em->getRepository('uteg:Category')->findBy(array(), array('number'=>'asc'));
	
				return $this->render('form/StarterImport.html.twig',
	                array('clubs' => $clubs,
	                    'categories' => $categories,
	                    'starters' => $starters,
                        'errors' => $errors
	                )
	            );
			} else {
				return $this->render('form/StarterImportUpload.html.twig',
						array('error' => 'starters.import.error.empty'));
			}
			
			return new Response('true');
		} else {
        	return $this->render('form/StarterImportUpload.html.twig');
        }
	}
	
	/**
	 * @Route("/starter/import/process", name="starterProcess")
	 * @Method("POST")
	 */
	public function starterUploadAction(Request $request) {
		$this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');
		
		$files = $request->files;
		
		// configuration values
		$directory = $this->get('kernel')->getRootDir() . '/../web/uploads/'.$request->getSession()->getId();
		
		// $file will be an instance of Symfony\Component\HttpFoundation\File\UploadedFile
		foreach ($files as $uploadedFiles) {
			foreach($uploadedFiles as $uploadedFile) {
				// name the resulting file
				$name = $uploadedFile->getClientOriginalName();
				$file = $uploadedFile->move($directory, $name);
				$sessionFiles[] = $directory."/".$name;
			}
		}

		$request->getSession()->set('import', $sessionFiles);
		
		// return data to the frontend
		return new Response($this->get('router')->generate('starterImport'));
	}
	
    /**
     * @Route("/starter/edit/{id}", name="starterEdit", defaults={"id": ""}, requirements={"id": "\d+"})
     */
    public function starterEditAction($id, Request $request) {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $s2c = $this->getDoctrine()->getEntityManager()->find('uteg:Starters2Competitions',$id);

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
        $s2c = $em->find('uteg:Starters2Competitions', $_POST['id']);
        $competition = $em->find('uteg:Competition', $request->getSession()->get('comp'));

        $competition->removeS2c($s2c);
        $em->persist($competition);
        $em->flush();
        return new Response('true');
    }
	
	private function getName($id) {
		return "Mathias Scherer";
	}
}