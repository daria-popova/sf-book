<?php

namespace BookBundle\Controller;

use BookBundle\BookBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use BookBundle\Entity\Book;
use BookBundle\Form\BookType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use BookBundle\Service\FileUploader;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="list")
     */
    public function listAction()
    {
        //TODO Move to dependencies
        $repository = $this->getDoctrine()->getRepository(Book::class);

        $query = $repository->createQueryBuilder('b')
            ->orderBy('b.readDate', 'DESC')
            ->getQuery();

        $books = $query->getResult();
        return $this->render('BookBundle:Default:list.html.twig', ['books' => $books]);
    }

    /**
     * @Route("/edit/{id}", name="edit", requirements={"id": "\d+"})
     */
    public function editController($id)
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);
        $book = $repository->findOneById($id);
        if (!$book) {
            return new Response('404 error', 404);
        }

        $form = $this->createForm(BookType::class, $book);

        return $this->render('BookBundle:Default:edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/create/", name="create")
     */
    public function createController(Request $request, FileUploader $fileUploader)
    {
        $form = $this->createForm(BookType::class, new Book());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $em = $this->getDoctrine()->getManager();

            if ($book->getCover() instanceof UploadedFile) {
                $book->setCover($fileUploader->upload($book->getCover()));
            }

            if ($book->getSource() instanceof UploadedFile) {
                $book->setSource($fileUploader->upload($book->getSource()));
            }

            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('list');
        }

        return $this->render('BookBundle:Default:edit.html.twig', ['form' => $form->createView()]);
    }
}
