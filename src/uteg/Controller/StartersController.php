<?php

namespace uteg\Controller;

use uteg\Entity\Club;
use uteg\Form\Type\StarterType;
use uteg\Form\Type\S2cType;
use uteg\Entity\Starter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;


class StartersController extends DefaultController
{
    /**
     * @Route("/{compid}/starters/{gender}", name="starters", defaults={"gender": "male"}, requirements={"gender": "male|female"})
     * @Method("GET")
     */
    public function startersGetAction($gender, Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        $requestUri = explode("/", $request->getRequestUri());

        if (end($requestUri) !== 'male' && end($requestUri) !== 'female') {
            return $this->redirect($request->getRequestUri() . "/male", 301);
        } else {
            $gendershort = (end($requestUri) == 'female') ? 'f' : 'm';
            $gendertrans = ($gender == 'female') ? 'starters.female' : 'starters.male';

            return $this->render('starters.html.twig', array(
                "gender" => $gendershort,
                "gendertrans" => $gendertrans,
                "comp" => $comp
            ));
        }
    }

    /**
     * @Route("/{compid}/starters/{gender}/{cat}", name="starterspost", defaults={"gender": "male", "cat": "0"}, requirements={"gender": "male|female", "cat": "\d+"})
     * @Method("POST")
     */
    public function startersPostAction($gender, $cat, Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_VIEW');

        if ($gender !== 'male' && $gender !== 'female') {
            return $this->redirect($request->getRequestUri() . "/male", 301);
        } else {
            $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $request->getSession()->get('comp'));
            $s2cs = ($cat == 0) ? $comp->getS2csByGender($gender) : $comp->getS2csByGenderCat($gender, $cat);
            $starters["data"] = array();

            foreach ($s2cs as $s2c) {
                $starters["data"][] = array("id" => $s2c->getId(),
                    "firstname" => $s2c->getStarter()->getFirstname(),
                    "lastname" => $s2c->getStarter()->getLastname(),
                    "birthyear" => $s2c->getStarter()->getBirthyear(),
                    "club" => $s2c->getClub()->getName(),
                    "category" => ($s2c->getCategory()->getNumber() == 8) ? ($s2c->getStarter()->getGender() == 'female') ? $s2c->getCategory()->getName() . "D" : $s2c->getCategory()->getName() . "H" : $s2c->getCategory()->getName(),
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
     * @Route("/{compid}/starter/add", name="starterAdd")
     */
    public function starterAddAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        $form = $this->createForm(new S2cType(false));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $formdata = $form->getData();

            $starter = $em->getRepository('uteg:Starter')->findOneBy(array("firstname" => $formdata['firstname'], "lastname" => $formdata['lastname'], "birthyear" => $formdata['birthyear'], "gender" => $formdata['gender']));
            if (!$starter) {
                $starter = $em->getRepository('uteg:Starter')->findOneBy(array("lastname" => $formdata['firstname'], "firstname" => $formdata['lastname'], "birthyear" => $formdata['birthyear'], "gender" => $formdata['gender']));
                if (!$starter) {
                    $starter = new Starter();
                    $starter->setFirstname($formdata['firstname']);
                    $starter->setLastname($formdata['lastname']);
                    $starter->setBirthyear($formdata['birthyear']);
                    $starter->setGender($formdata['gender']);
                    $s2c = false;
                } else {
                    $s2c = $module->findS2c(array("starter" => $starter, "competition" => $comp));
                }
            } else {
                $s2c = $module->findS2c(array("starter" => $starter, "competition" => $comp));
            }

            if (!$s2c) {
                $s2c = $module->getS2c();
                $s2c->setStarter($starter);
                $s2c->setCompetition($comp);

                $starter->addS2c($s2c);
                $comp->addS2c($s2c);
            }

            $s2c->setClub($em->getRepository('uteg:Club')->find($formdata['club']));
            $s2c->setCategory($em->getRepository('uteg:Category')->find($formdata['category']));
            $s2c->setPresent($formdata['present']);
            $s2c->setMedicalcert($formdata['medicalcert']);

            $validator = $this->get('validator');
            $errors = $validator->validate($starter);
            if (count($errors) <= 0) {
                $em->persist($starter);
                $em->persist($s2c);
                $em->persist($comp);
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
     * @Route("/{compid}/starter/add/massive", name="starterAddMassive")
     * @Method("POST")
     */
    public function starterAddMassiveAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');
        $em = $this->getDoctrine()->getManager();

        $return = $this->addMassiveAction($em->getRepository('uteg:Competition')->find($request->getSession()->get('comp')), $request->request->get('data'));

        if (count($return['fails']) > 0) {
            $clubs = $em->getRepository('uteg:Club')->findBy(array(), array('name' => 'asc'));
            $categories = $em->getRepository('uteg:Category')->findBy(array(), array('number' => 'asc'));

            return $this->render('form/StarterImport.html.twig',
                array('clubs' => $clubs,
                    'categories' => $categories,
                    'starters' => $return['fails'],
                    'errors' => $return['errorMessages']
                )
            );
        } else {
            $this->get('session')->getFlashBag()->add('success', 'starters.import.success');
            return $this->redirectToRoute('starters', array("compid" => $compid));
        }
    }

    /**
     * @Route("/{compid}/starter/import", name="starterImport")
     * Increase max_input_vars in php.ini from 1000 to 6000 to import max. 1000 starters at once
     */
    public function starterImportAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        $files = $request->getSession()->get('import');
        $request->getSession()->set('import', null);
        $errors = array();

        if ($files) {
            $em = $this->getDoctrine()->getEntityManager();
            $fs = new Filesystem();
            $index = -1;

            foreach ($files as $file) {
                try {
                    $inputFileType = \PHPExcel_IOFactory::identify($file);
                    $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($file);
                } catch (Exception $e) {
                    throw new HttpException(500, 'Error loading file "' . pathinfo($file, PATHINFO_BASENAME) . '": ' . $e->getMessage());
                }

                //  Get worksheet dimensions
                $sheetData = $objPHPExcel->setActiveSheetIndex(0);
                $highestRow = $sheetData->getHighestDataRow();
                $highestColumn = $sheetData->getHighestDataColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                ($fs->exists($file)) ? $fs->remove($file) : '';

                if ($highestColumnIndex == 7) {

                    for ($row = 1; $row <= $highestRow; ++$row) {
                        ++$index;


                        ($sheetData->getCellByColumnAndRow(0, $row)->getValue() == 'STV-Nummer' ||
                            $sheetData->getCellByColumnAndRow(1, $row)->getValue() == 'Vorname' ||
                            $sheetData->getCellByColumnAndRow(2, $row)->getValue() == 'Lastname' ||
                            $sheetData->getCellByColumnAndRow(3, $row)->getValue() == 'Brith year' ||
                            $sheetData->getCellByColumnAndRow(4, $row)->getValue() == 'Gender' ||
                            $sheetData->getCellByColumnAndRow(5, $row)->getValue() == 'Category' ||
                            $sheetData->getCellByColumnAndRow(7, $row)->getValue() == 'Club') ? $row++ : $row;

                        ($sheetData->getCellByColumnAndRow(1, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(2, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(3, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(4, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(5, $row)->getValue() == '' &&
                            $sheetData->getCellByColumnAndRow(6, $row)->getValue() == '') ? $row++ : $row;

                        $stvid = $sheetData->getCellByColumnAndRow(0, $row)->getValue();
                        if (strlen($stvid) <= 0) {
                            $errors[$index]['stvid'] = 'starter.error.stvid';
                        }

                        $firstname = $sheetData->getCellByColumnAndRow(1, $row)->getValue();
                        if (strlen($firstname) <= 0) {
                            $errors[$index]['firstname'] = 'starter.error.firstname';
                        }

                        $lastname = $sheetData->getCellByColumnAndRow(2, $row)->getValue();
                        if (strlen($lastname) <= 0) {
                            $errors[$index]['lastname'] = 'starter.error.lastname';
                        }

                        $birthyear = $sheetData->getCellByColumnAndRow(3, $row)->getValue();
                        if (strlen($birthyear) < 4) {
                            $errors[$index]['birthyear'] = 'starter.error.birthyearMin';
                        }

                        if (strlen($birthyear) > 4) {
                            $errors[$index]['birthyear'] = 'starter.error.birthyearMax';
                        }

                        $gender = strtolower($sheetData->getCellByColumnAndRow(4, $row)->getValue());
                        if ($gender === 'w') {
                            $gender = 'female';
                        }

                        if ($gender === 'm') {
                            $gender = 'male';
                        }

                        if ($gender !== 'male' && $gender !== 'female') {
                            $errors[$index]['gender'] = 'starter.error.gender';
                        }

                        $clubname = $sheetData->getCellByColumnAndRow(6, $row)->getValue();
                        if ($clubname) {
                            $club = $em->getRepository('uteg:Club')->findOneBy(array("name" => $clubname));
                            if (!$club) {
                                $club = new Club();
                                $club->setName($sheetData->getCellByColumnAndRow(6, $row)->getValue());
                                $em->persist($club);
                                $em->flush();
                            }
                        } else {
                            $club['id'] = 0;
                            $errors[$index]['club'] = 'starter.error.club';
                        }

                        $category = $sheetData->getCellByColumnAndRow(5, $row)->getValue();
                        if (strpos($category, "K") === false) {
                            $category = "K" . $category;
                        }
                        $category = $em->getRepository('uteg:Category')->findOneBy(array("name" => $category));
                        if (!$category) {
                            $category['id'] = 0;
                            $errors[$index]['category'] = 'starter.error.category';
                        }

                        $starters[] = array("stvid" => $stvid,
                            "firstname" => $firstname,
                            'lastname' => $lastname,
                            'birthyear' => $birthyear,
                            'gender' => $gender,
                            'category' => $category,
                            'club' => $club
                        );

                        unset($stvid);
                        unset($firstname);
                        unset($lastname);
                        unset($birthyear);
                        unset($gender);
                        unset($club);
                        unset($clubname);
                        unset($category);
                    }
                } else {
                    return $this->render('form/StarterImportUpload.html.twig',
                        array('error' => 'starters.import.error.columnCount'));
                }
            }

            if (isset($starters)) {
                $clubs = $em->getRepository('uteg:Club')->findBy(array(), array('name' => 'asc'));
                $categories = $em->getRepository('uteg:Category')->findBy(array(), array('number' => 'asc'));

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
     * @Route("/{compid}/starter/import/process", name="starterProcess")
     * @Method("POST")
     */
    public function starterUploadAction(Request $request, $compid)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $files = $request->files;

        // configuration values
        $directory = $this->get('kernel')->getRootDir() . '/../web/uploads/' . $request->getSession()->getId();

        // $file will be an instance of Symfony\Component\HttpFoundation\File\UploadedFile
        foreach ($files as $uploadedFiles) {
            foreach ($uploadedFiles as $uploadedFile) {
                // name the resulting file
                $name = $uploadedFile->getClientOriginalName();
                $file = $uploadedFile->move($directory, $name);
                $sessionFiles[] = $directory . "/" . $name;
            }
        }

        $request->getSession()->set('import', $sessionFiles);

        // return data to the frontend
        return new Response($this->get('router')->generate('starterImport', array("compid" => $compid)));
    }

    /**
     * @Route("/{compid}/starter/edit/{id}", name="starterEdit", defaults={"id": ""}, requirements={"id": "\d+"})
     */
    public function starterEditAction(Request $request, $compid, $id)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        $s2c = $module->findS2c(array("id" => $id));

        $form = $this->createForm(new S2cType(), $s2c);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $s2c = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();

            $em->persist($s2c);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'starters.edit.success');

            return new Response('true');
        }

        return $this->render('form/StarterEdit.html.twig',
            array('form' => $form->createView(),
                'target' => 'starterEdit'
            )
        );
    }

    /**
     * @Route("/{compid}/starter/remove/{id}", name="starterRemove", defaults={"id": ""}, requirements={"id": "\d+"})
     */
    public function starterRemoveAction(Request $request, $compid, $id)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');

        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        $em = $this->getDoctrine()->getEntityManager();
        $s2c = $module->findS2c(array("id" => $id));

        $comp->removeS2c($s2c);
        $em->persist($comp);
        $em->flush();
        return new Response('true');
    }

    /**
     * @Route("/{compid}/starters/{present}", name="togglePresent", requirements={"id": "\d+", "present": "0|1"})
     * @Method("POST")
     */
    public function togglePresentAction(Request $request, $compid, $present)
    {
        $this->get('acl_competition')->isGrantedUrl('STARTERS_EDIT');
        $em = $this->getDoctrine()->getEntityManager();
        $comp = $this->getDoctrine()->getEntityManager()->find('uteg:Competition', $compid);
        $module = $this->get($comp->getModule()->getServiceName());
        $module->init();

        $starters = $request->request->get('starters');

        foreach ($starters as $starter) {
            $s2c = $module->findS2c(array("id" => $starter));

            if ($present == 1) {
                $s2c->setPresent(1);
            } else {
                $s2c->setPresent(0);
            }

            $em->persist($s2c);
        }

        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'starters.edit.success');

        return new Response('true');
    }
}
