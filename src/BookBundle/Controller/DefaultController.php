<?php

namespace BookBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\ORM\EntityManagerInterface;
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
        $repository = $this->getDoctrine()->getRepository(Book::class);

        $query = $repository->createQueryBuilder('b')
            ->orderBy('b.readDate', 'DESC')
            ->getQuery();

        $books = $query->getResult();
        return $this->render('BookBundle:Default:list.html.twig', ['books' => $books]);
    }

    /**
     * @Route("/create/", name="create")
     */
    public function createAction(Request $request, FileUploader $fileUploader)
    {
        return $this->createOrEditAction(null, $request, $fileUploader);
    }

    /**
     * @Route("/edit/{id}", name="edit", requirements={"id": "\d+"})
     */
    public function editAction($id, Request $request, FileUploader $fileUploader)
    {
        return $this->createOrEditAction($id, $request, $fileUploader);
    }

    public function createOrEditAction($id, Request $request, FileUploader $fileUploader)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == null) {
            $book = new Book();
        } else {
            $book = $em->find(Book::class, $id);
        }

        $oldFilePath = [
            'cover' => $book->getCover(),
            'source' => $book->getSource()
        ];

        if ($book->getCover()) {
            $book->setCover(new File($fileUploader->getUploadDir() . '/' . $book->getCover()));
        }

        if ($book->getSource()) {
            $book->setSource(new File($fileUploader->getUploadDir() . '/' . $book->getSource()));
        }

        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();

            $deleteCover = $form->has('deleteCover') && $form->get('deleteCover')->getData();
            $deleteSource = $form->has('deleteSource') && $form->get('deleteSource')->getData();

            if ($book->getCover() instanceof UploadedFile) {
                $book->setCover($fileUploader->upload($book->getCover()));
            } elseif ($deleteCover) {
                $book->setCover(null);
            } else {
                $book->setCover($oldFilePath['cover']);
            }

            if ($book->getSource() instanceof UploadedFile) {
                $book->setSource($fileUploader->upload($book->getSource()));
            } elseif ($deleteSource) {
                $book->setCover(null);
            } else {
                $book->setSource($oldFilePath['source']);
            }

            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('list');
        }

        return $this->render('BookBundle:Default:edit.html.twig',
            ['form' => $form->createView(), 'oldFilePath' => $oldFilePath]);
    }

    /**
     * @Route("/delete/{id}", name="delete", requirements={"id": "\d+"})
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $book = $em->find(Book::class, $id);
        $em->remove($book);
        $em->flush();
        return $this->redirectToRoute('list');
    }
}
