<?php

namespace App\Controller;

use App\Entity\Students;
use App\Form\StudentsType;
use App\Repository\StudentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_ADMIN")
 */
class StudentsController extends AbstractController
{
    /**
     * @Route("/students", name="app_students_index")
     */
    public function index(StudentsRepository $studentsRepository, Request $request): Response
    {
        $student = new Students();
        $form = $this->createForm(StudentsType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('photo')->getData();

            $new_name = md5(uniqid()).'.'.$photo->guessExtension();

            $photo->move(
                $this->getParameter('image_students'),
                $new_name
            );

            $student->setPhoto($new_name);

            $studentsRepository->add($student, true);

            return $this->redirectToRoute('app_students_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/students/index.html.twig', [
            'students' => $studentsRepository->findAll(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/student/new", name="app_student_new", methods={"GET", "POST"})
     */
    public function new(Request $request, StudentsRepository $studentsRepository): Response
    {
        $student = new Students();
        $form = $this->createForm(StudentsType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photo = $form->get('photo')->getData();

            $new_name = md5(uniqid()).'.'.$photo->guessExtension();

            $photo->move(
                $this->getParameter('image_students'),
                $new_name
            );

            $student->setPhoto($new_name);

            $studentsRepository->add($student, true);

            return $this->redirectToRoute('app_students_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/students/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);

    }

        /**
         * @Route("/student/{id}", name="app_student_show", methods={"GET"})
         */
        public function show(Students $student): Response
        {
            return $this->render('admin/students/show.html.twig', [
                'student' => $student,
            ]);
        }

        /**
     * @Route("/student/{id}/edit", name="app_student_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Students $student, StudentsRepository $studentsRepository): Response
    {
        $old_photo = $student->getPhoto();

        $form = $this->createForm(StudentsType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($student->getPhoto()) {
                if($old_photo != null){
                    // Supprime l'ancienne photo
                    $filesystem= new Filesystem();

                    $filesystem->remove('students/' . $old_photo);
                }
                $photo = $form->get('photo')->getData();

                $new_name = md5(uniqid()) .'.'. $photo->guessExtension();

                $photo->move(
                    $this->getParameter('image_students'),
                    $new_name
                );
                $student->setPhoto($new_name);
            }else{
                $student->setPhoto($old_photo);
            }

            $studentsRepository->add($student, true);

            return $this->redirectToRoute('app_students_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/students/edit.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/student/delete/{id}", name="app_student_delete")
     */
    public function delete(Request $request, Students $student, StudentsRepository $studentsRepository): Response
    {
        $old_photo = $student->getPhoto();

        if($student->getPhoto()){
            $filesystem1 = new Filesystem();
            $filesystem1->remove('students/' . $old_photo);
        }

        if($student){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($student);
            $entityManager->flush();

            return new JsonResponse(200);
        }
        return new JsonResponse(300);
    }


}
