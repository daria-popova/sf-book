<?php

namespace BookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use BookBundle\Entity\Book;
use BookBundle\Form\BookType;
use BookBundle\Service\FileUploader;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="list")
     */
    public function listAction()
    {
        $books = $this
            ->getDoctrine()
            ->getRepository(Book::class)
            ->findAllOrderedByDateDesc();

        return $this->render(
            'BookBundle:Default:list.html.twig',
            [
                'books' => $books,
            ]
        );
    }

    /**
     * @Route("/book/create", name="create")
     */
    public function createAction(Request $request, FileUploader $fileUploader)
    {
        return $this->createOrEditAction(null, $request, $fileUploader);
    }

    /**
     * @Route("/book/edit/{id}", name="edit", requirements={"id": "\d+"})
     */
    public function editAction(int $id, Request $request, FileUploader $fileUploader)
    {
        return $this->createOrEditAction($id, $request, $fileUploader);
    }

    private function createOrEditAction(?int $id, Request $request, FileUploader $fileUploader)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == null) {
            $book = new Book();
        } else {
            $book = $em->find(Book::class, $id);
            if (!$book) {
                throw $this->createNotFoundException('The book does not exist');
            }
        }

        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);

        //TODO how to check constraints of files?
        //https://symfony.com/doc/2.0/book/forms.html#adding-validation
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();

            if ($form->get('coverFile')->getData() instanceof UploadedFile) {
                $book->setCover(
                    $fileUploader->upload($form->get('coverFile')->getData())
                );
            } elseif ($form->has('deleteCover') && $form->get('deleteCover')->getData()) {
                $book->setCover(null);
            }

            if ($form->get('sourceFile')->getData() instanceof UploadedFile) {
                $book->setSource(
                    $fileUploader->upload($form->get('sourceFile')->getData())
                );
            } elseif ($form->has('deleteSource') && $form->get('deleteSource')->getData()) {
                $book->setSource(null);
            }

            if ($id === null) {
                $em->persist($book);
            }
            $em->flush();
            return $this->redirectToRoute('list');
        }

        return $this->render(
            'BookBundle:Default:edit.html.twig',
            [
                'form' => $form->createView(),
                'new' => !($book->getId())
            ]
        );
    }

    /**
     * @Route("/book/delete/{id}", name="delete", requirements={"id": "\d+"})
     * @Method("POST")
     *
     * TODO make correct json response in all cases, refactor js
     */
    public function deleteAction(int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $book = $em->find(Book::class, $id);
        if (!$book) {
            throw $this->createNotFoundException('The book does not exist');
        }
        $em->remove($book);
        $em->flush();
        return new JsonResponse(["status" => "ok"]);
    }
}
